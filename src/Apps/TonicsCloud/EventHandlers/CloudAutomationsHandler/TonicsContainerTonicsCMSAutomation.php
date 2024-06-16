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
use App\Apps\TonicsCloud\Jobs\Container\Automations\CloudJobQueueAutomationTonicsCMS;
use App\Apps\TonicsCloud\Services\ContainerService;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInputMethodsInterface;

class TonicsContainerTonicsCMSAutomation extends CloudAutomationInterfaceAbstract
{
    use ProxyAutomation;

    const TONICS_VERSION_CLOUD_TONICS = 'CloudTonics';
    const TONICS_VERSION_WRI_TONICS   = 'WriTonics';
    const TONICS_VERSION_Audio_TONICS = 'AudioTonics';

    const solutionsPHPVersion = [
        self::TONICS_VERSION_CLOUD_TONICS => '8.2',
        self::TONICS_VERSION_WRI_TONICS   => '8.2',
        self::TONICS_VERSION_Audio_TONICS => '8.2',
    ];

    public function name (): string
    {
        return 'app-tonicscloud-automation-tonics-cms';
    }

    public function displayName (): string
    {
        return 'TonicsCloud - Tonics Site(s)';
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
        return $this->getSiteValidationRuleForCommonCMS();
    }

    /**
     * @param TonicsRouterRequestInputMethodsInterface $input
     *
     * @return array
     */
    protected function prepareSites (TonicsRouterRequestInputMethodsInterface $input): array
    {
        return $this->mapInputToArray($input, [
            'tonicsCloud_tonicsCMS_site_domainName'   => 'domain',
            'tonicsCloud_tonicsCMS_site_emailAddress' => 'email',
            'tonicsCloud_tonicsCMS_site_dbUser'       => 'dbUser',
            'tonicsCloud_tonicsCMS_site_dbPass'       => 'dbPass',
            'tonicsCloud_tonicsCMS_site_solution'     => 'solution',
            'tonicsCloud_tonicsCMS_site_archive'      => 'archive',
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
            $siteInput['container_image'] = $this->getImageID(self::IMAGE_TONICS);
            $solution = (empty($site['solution'])) ? self::TONICS_VERSION_CLOUD_TONICS : $site['solution'];
            $siteInput['image_version'] = $solution;
            $variables = [
                'ACME_DOMAIN' => $site['domain'],
                'DB_DATABASE' => helper()->randomString(10),
                'DB_USER'     => $site['dbUser'],
                'DB_PASS'     => $site['dbPass'],
                'DB_HOST'     => 'localhost',
                'ROOT'        => '/var/www/tonics',
                'PHP_VERSION' => self::solutionsPHPVersion[$solution] ?? self::solutionsPHPVersion[self::TONICS_VERSION_CLOUD_TONICS],
                'INSTALL_KEY' => helper()->randomString(25),
                'SITE_KEY'    => helper()->randomString(25),
            ];

            if (!empty($site['archive'])) {
                $variables['ARCHIVE_FILE'] = $site['archive'];
            }

            $siteInput['variables'] = $containerService->createContainerVariables($variables);
            $data['input'] = input()->fromPost($siteInput);
            $data['jobs'] = $this->defaultContainerCreateQueuePaths($data['input'], ['job' => container()->get(CloudJobQueueAutomationTonicsCMS::class)]);
            $this->createContainer($data);
            $containersToProxyTo[] = $this->containerSlugID;
        }

        return $containersToProxyTo;
    }
}