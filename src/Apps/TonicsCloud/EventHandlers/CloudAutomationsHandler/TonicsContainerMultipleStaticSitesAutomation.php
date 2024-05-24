<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Apps\TonicsCloud\EventHandlers\CloudAutomationsHandler;

use App\Apps\TonicsCloud\Controllers\InstanceController;
use App\Apps\TonicsCloud\Interfaces\CloudAutomationInterfaceAbstract;
use App\Apps\TonicsCloud\Jobs\Container\Automations\CloudJobQueueAutomationMultipleStaticSite;
use App\Apps\TonicsCloud\Jobs\Container\Automations\CloudJobQueueAutomationMultipleStaticSiteProxyConfiguration;
use App\Apps\TonicsCloud\Services\ContainerService;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInputMethodsInterface;

class TonicsContainerMultipleStaticSitesAutomation extends CloudAutomationInterfaceAbstract
{

    public function name (): string
    {
        return 'app-tonicscloud-automation-multiple-static-site';
    }

    public function displayName (): string
    {
        return 'I Want Multiple Static Sites - TonicsCloud';
    }

    public function automate ($data = []): void
    {
        /** @var TonicsRouterRequestInputMethodsInterface $input */
        $input = $data['input'];
        /** @var ContainerService $containerService */
        $containerService = $data['containerService'];
        $validation = $containerService->validateContainerCreate($input);
        if ($validation->fails()) {
            $containerService->handleValidationFailureForContainerCreate($validation);
            return;
        }

        $sites = $this->prepareSites($input);
        $emails = $input->retrieve('tonicsCloud_multi_static_site_emailAddress');

        $containersToProxyTo = $this->createContainersForSites($sites, $data);
        $cloudInstance = $input->retrieve('cloud_instance');
        $serviceInstance = InstanceController::GetServiceInstances([
            'instance_id' => $cloudInstance,
            'user_id'     => \session()::getUserID(),
        ]);

        $data['proxyEmail'] = $emails[0];
        $data['proxyJobCallback'] = function ($cloudInstance, $proxyTo) {
            /** @var CloudJobQueueAutomationMultipleStaticSiteProxyConfiguration $cloudJobQueueAutomationProxyConfig */
            $cloudJobQueueAutomationProxyConfig = container()->get(CloudJobQueueAutomationMultipleStaticSiteProxyConfiguration::class);
            $cloudJobQueueAutomationProxyConfig->setData([
                'cloudInstance'    => $cloudInstance,
                'containerProxyTo' => $proxyTo,
            ]);

            return $cloudJobQueueAutomationProxyConfig;
        };

        $this->createProxyContainerIfNecessary($serviceInstance, $containersToProxyTo, $data);
    }

    /**
     * @param TonicsRouterRequestInputMethodsInterface $input
     *
     * @return array
     */
    protected function prepareSites (TonicsRouterRequestInputMethodsInterface $input): array
    {
        return $this->mapInputToArray($input, [
            'tonicsCloud_multi_static_site_domainName'   => 'domain',
            'tonicsCloud_multi_static_site_emailAddress' => 'email',
            'tonicsCloud_multi_static_site_archiveFile'  => 'file',
        ]);
    }

    /**
     * @param array $sites
     * @param array $data
     *
     * @return array
     * @throws \ReflectionException
     * @throws \Throwable
     */
    protected function createContainersForSites (array $sites, array $data): array
    {
        $containersToProxyTo = [];
        $input = $data['input'];

        foreach ($sites as $site) {
            $siteInput = $input->all();
            $siteInput['container_name'] = helper()->strLimit($siteInput['container_name'] . '[' . $site['domain'] . ']', 220);
            $siteInput['container_image'] = $this->getImageID(self::IMAGE_NGINX);
            $siteInput['variables'] = "ACME_DOMAIN={$site['domain']}\nARCHIVE_FILE={$site['file']}";

            $data['input'] = input()->fromPost($siteInput);
            $data['jobs'] = $this->defaultContainerCreateQueuePaths($data['input'], ['job' => container()->get(CloudJobQueueAutomationMultipleStaticSite::class)]);
            $this->createContainer($data);
            $containersToProxyTo[] = $this->containerSlugID;
        }

        return $containersToProxyTo;
    }
}