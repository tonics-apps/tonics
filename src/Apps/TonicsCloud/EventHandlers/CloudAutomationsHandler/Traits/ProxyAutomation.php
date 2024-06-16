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

namespace App\Apps\TonicsCloud\EventHandlers\CloudAutomationsHandler\Traits;

use App\Apps\TonicsCloud\Controllers\InstanceController;
use App\Apps\TonicsCloud\Jobs\Container\Automations\CloudJobQueueAutomationMultipleSiteProxyConfiguration;
use App\Apps\TonicsCloud\Services\ContainerService;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInputMethodsInterface;

trait ProxyAutomation
{
    /**
     * @param $data
     *
     * @return void
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function automateContainerAndProxyToContainers ($data): void
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
        foreach ($sites as $site) {
            $validation = $containerService->validateGeneral($site, $this->getSiteValidationRule());
            if ($validation->fails()) {
                $containerService->handleValidationFailureForContainerCreate($validation);
                return;
            }
        }

        $containersToProxyTo = $this->createContainersForSites($sites, $data);
        $cloudInstance = $input->retrieve('cloud_instance');
        $serviceInstance = InstanceController::GetServiceInstances([
            'instance_id' => $cloudInstance,
            'user_id'     => \session()::getUserID(),
        ]);

        $data['proxyEmail'] = $sites[0]['email'];
        $data['proxyJobCallback'] = function ($cloudInstance, $proxyTo) {
            /** @var CloudJobQueueAutomationMultipleSiteProxyConfiguration $cloudJobQueueAutomationProxyConfig */
            $cloudJobQueueAutomationProxyConfig = container()->get(CloudJobQueueAutomationMultipleSiteProxyConfiguration::class);
            $cloudJobQueueAutomationProxyConfig->setData([
                'cloudInstance'    => $cloudInstance,
                'containerProxyTo' => $proxyTo,
            ]);

            return $cloudJobQueueAutomationProxyConfig;
        };

        $this->createProxyContainerIfNecessary($serviceInstance, $containersToProxyTo, $data);
    }

    /**
     * @return array
     */
    public function getSiteValidationRuleForCommonCMS (): array
    {
        return [
            'domain' => ['required', 'string', 'CharLen' => ['min' => 3, 'max' => 1000]],
            'email'  => ['required', 'email'],
            'dbUser' => ['required', 'string', 'CharLen' => ['min' => 3, 'max' => 1000]],
            'dbPass' => ['required', 'string', 'CharLen' => ['min' => 5, 'max' => 10000]],
        ];
    }
}