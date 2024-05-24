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
        return 'I Want a Static Site - TonicsCloud';
    }

    public function automate ($data = []): void
    {
        /** @var TonicsRouterRequestInputMethodsInterface $input */
        $input = $data['input'];
        $inputs = $data['input']->all();

        $inputs['container_profiles'] = $this->getProfiles();
        $inputs['container_image'] = $this->getImageID(self::IMAGE_NGINX);
        $inputs['variables'] = <<<VARIABLES
ACME_EMAIL={$input->retrieve('tonicsCloud_standalone_static_site_emailAddress')}
ACME_DOMAIN={$input->retrieve('tonicsCloud_standalone_static_site_domainName')}
ARCHIVE_FILE={$input->retrieve('tonicsCloud_standalone_static_site_archiveFile')}
VARIABLES;

        $data['input'] = input()->fromPost($inputs);
        $data['jobs'] = $this->defaultContainerCreateQueuePaths($data['input'], ['job' => container()->get(CloudJobQueueAutomationStandaloneStaticSite::class)]);
        $this->createContainer($data);
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