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

use App\Apps\TonicsCloud\EventHandlers\CloudAutomationsHandler\Traits\ProxyAutomation;
use App\Apps\TonicsCloud\Interfaces\CloudAutomationInterfaceAbstract;
use App\Apps\TonicsCloud\Jobs\Container\Automations\CloudJobQueueAutomationMultipleStaticSite;
use App\Apps\TonicsCloud\Services\ContainerService;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInputMethodsInterface;

class TonicsContainerMultipleStaticSitesAutomation extends CloudAutomationInterfaceAbstract
{
    use ProxyAutomation;

    public function name (): string
    {
        return 'app-tonicscloud-automation-multiple-static-site';
    }

    public function displayName (): string
    {
        return 'TonicsCloud - Multiple Static Site(s)';
    }

    public function automate ($data = []): void
    {
        $this->automateContainerAndProxyToContainers($data);
    }

    /**
     * @throws \Exception
     */
    public function getSiteValidationRule (): array
    {
        return [
            'domain' => ['required', 'string', 'CharLen' => ['min' => 3, 'max' => 1000]],
            'email'  => ['required', 'email'],
            'file'   => ['required', 'string'],
        ];
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
        /** @var ContainerService $containerService */
        $containerService = $data['containerService'];

        foreach ($sites as $site) {
            $siteInput = $input->all();
            $siteInput['container_name'] = helper()->strLimit($siteInput['container_name'] . '[' . $site['domain'] . ']', 220);
            $siteInput['container_image'] = $this->getImageID(self::IMAGE_NGINX);
            $siteInput['image_version'] = $this->getImageVersion(self::IMAGE_NGINX);
            $domain = $site['domain'];
            $siteInput['variables'] = $containerService->createContainerVariables([
                'ROOT'         => "/var/www/$domain",
                'ACME_DOMAIN'  => $domain,
                'ARCHIVE_FILE' => $site['file'],
            ]);

            $data['input'] = input()->fromPost($siteInput);
            $data['jobs'] = $this->defaultContainerCreateQueuePaths($data['input'], ['job' => container()->get(CloudJobQueueAutomationMultipleStaticSite::class)]);
            $this->createContainer($data);
            $containersToProxyTo[] = $this->containerSlugID;
        }

        return $containersToProxyTo;
    }
}