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

use App\Apps\TonicsCloud\Interfaces\CloudAutomationInterfaceAbstract;
use App\Apps\TonicsCloud\Jobs\Container\Automations\CloudJobQueueAutomationStandaloneStaticSite;
use App\Apps\TonicsCloud\Services\ContainerService;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInputMethodsInterface;

class TonicsContainerStandaloneStaticSiteAutomation extends CloudAutomationInterfaceAbstract
{

    public function name (): string
    {
        return 'app-tonicscloud-automation-standalone-static-site';
    }

    public function displayName (): string
    {
        return 'TonicsCloud - Standalone Static Site';
    }

    public function automate ($data = []): void
    {
        /** @var TonicsRouterRequestInputMethodsInterface $input */
        $input = $data['input'];
        $inputs = $data['input']->all();
        /** @var ContainerService $containerService */
        $containerService = $data['containerService'];

        $email = $input->retrieve('tonicsCloud_standalone_static_site_emailAddress');
        $domain = $input->retrieve('tonicsCloud_standalone_static_site_domainName');
        $file = $input->retrieve('tonicsCloud_standalone_static_site_archiveFile');

        $validation = $containerService->validateGeneral([
            'email'  => $email,
            'domain' => $domain,
            'file'   => $file,
        ], $this->getSiteValidationRule());

        if ($validation->fails()) {
            $containerService->handleValidationFailureForContainerCreate($validation);
            return;
        }

        $inputs['container_profiles'] = $this->getProfiles();
        $inputs['container_image'] = $this->getImageID(self::IMAGE_NGINX);
        $inputs['image_version'] = $this->getImageVersion(self::IMAGE_NGINX);
        $inputs['variables'] = $containerService->createContainerVariables([
            'ROOT'         => "/var/www/$domain",
            'ACME_EMAIL'   => $email,
            'ACME_DOMAIN'  => $domain,
            'ARCHIVE_FILE' => $file,
        ]);

        $data['input'] = input()->fromPost($inputs);
        $data['jobs'] = $this->defaultContainerCreateQueuePaths($data['input'], ['job' => container()->get(CloudJobQueueAutomationStandaloneStaticSite::class)]);
        $this->createContainer($data);
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
     * @throws \Exception
     */
    private function getProfiles ()
    {
        $profiles = ContainerService::getProfilesByName(['Port 80 - HTTP', 'Port 443 - HTTPS']);
        return array_map(fn($profile) => $profile->container_profile_id, $profiles);
    }
}