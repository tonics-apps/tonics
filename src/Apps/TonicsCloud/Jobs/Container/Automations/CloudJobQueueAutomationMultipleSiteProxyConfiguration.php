<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Apps\TonicsCloud\Jobs\Container\Automations;


use App\Apps\TonicsCloud\Apps\TonicsCloudNginx;
use App\Apps\TonicsCloud\Controllers\InstanceController;
use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueUpdateApp;
use App\Apps\TonicsCloud\Jobs\Container\Traits\TonicsJobQueueAutomationTrait;
use App\Apps\TonicsCloud\Jobs\Container\Traits\TonicsJobQueueContainerTrait;
use App\Apps\TonicsCloud\Services\AppService;
use App\Apps\TonicsCloud\Services\ContainerService;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;

class CloudJobQueueAutomationMultipleSiteProxyConfiguration extends AbstractJobInterface implements JobHandlerInterface
{

    use TonicsJobQueueContainerTrait, TonicsJobQueueAutomationTrait;

    private AppService $appService;

    private \stdClass|null $serviceInstanceOthers = null;
    private                $cloudInstance         = null;

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function __construct (AppService $appService)
    {
        $this->appService = $appService;
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function setContainerIDForProxy (): void
    {
        $cloudInstance = $this->getDataAsArray()['cloudInstance'];
        $serviceInstance = InstanceController::GetServiceInstances([
            'instance_id' => $cloudInstance,
        ]);
        $serviceInstanceOthers = json_decode($serviceInstance->others);

        $containerProxyID = $serviceInstanceOthers->containerProxy;
        $this->containerID = $containerProxyID;
        $this->serviceInstanceOthers = $serviceInstanceOthers;
        $this->cloudInstance = $cloudInstance;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle (): void
    {
        $this->setContainerIDForProxy();
        $this->constructorSetup();

        $data = $this->getDataAsArray();

        if ($this->isValidData($data)) {

            $containerProxyTo = $this->getDataAsArray()['containerProxyTo'];
            $apps = $this->getApps();

            $httpNginx = '';
            $httpsNginx = '';
            $acmeSites = [];
            $newProxyTo = [];
            foreach ($containerProxyTo as $proxyTo) {

                $container = ContainerService::getContainer($proxyTo, false, 'slug_id');
                if ($container) {

                    if ($container->container_status !== 'Running') {
                        # We need to requeue and ensure the container is running before any operation
                        $this->setJobStatusAfterJobHandled(Job::JobStatus_Queued);
                        return;
                    }

                    $newProxyTo[] = $proxyTo;
                    $containerOthers = json_decode($container->containerOthers);
                    if (isset($containerOthers->container_variables->ACME_DOMAIN)) {
                        $domain = $containerOthers->container_variables->ACME_DOMAIN;
                        $acmeSites[] = $domain;
                        $httpNginx .= TonicsCloudNginx::NginxConfigReverseProxySimple(
                            [
                                'serverName'         => $domain,
                                'proxyPassContainer' => ContainerService::getIncusContainerName($proxyTo),
                                'ssl'                => false,
                            ],
                        );

                        $httpsNginx .= TonicsCloudNginx::NginxConfigReverseProxySimple(
                            [
                                'serverName'         => $domain,
                                'proxyPassContainer' => ContainerService::getIncusContainerName($proxyTo),
                                'ssl'                => true,
                            ],
                        );

                    }
                }
            }

            $this->serviceInstanceOthers->container_proxy_to = $newProxyTo;
            InstanceController::updateInstanceServiceOthers($this->serviceInstanceOthers, $this->cloudInstance);

            $appsToUpdate = $this->pickAppSettings(
                [
                    fn() => $this->NginxMode($this->getCurrentContainerID(), $apps, $httpNginx),
                    self::APP_SETTING_ACME => ['acme_sites' => $acmeSites,],
                    fn() => $this->NginxMode($this->getCurrentContainerID(), $apps, $httpsNginx),
                ],
                $this->getCurrentContainerID());

            /** @var CloudJobQueueUpdateApp $cloudJobQueueUpdateApp */
            $cloudJobQueueUpdateApp = container()->get(CloudJobQueueUpdateApp::class);
            $cloudJobQueueUpdateApp->setData(['appsToUpdate' => $appsToUpdate]);
            TonicsCloudActivator::getJobQueue()->enqueue($cloudJobQueueUpdateApp);

        }

    }

    /**
     * @param array $data
     *
     * @return bool
     */
    protected function isValidData (array $data): bool
    {
        return isset($data['cloudInstance']) && isset($data['containerProxyTo']);
    }
}