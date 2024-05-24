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


use App\Apps\TonicsCloud\Apps\TonicsCloudACME;
use App\Apps\TonicsCloud\Apps\TonicsCloudNginx;
use App\Apps\TonicsCloud\Apps\TonicsCloudUnZip;
use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueUpdateApp;
use App\Apps\TonicsCloud\Jobs\Container\Traits\TonicsJobQueueAutomationTrait;
use App\Apps\TonicsCloud\Jobs\Container\Traits\TonicsJobQueueContainerTrait;
use App\Apps\TonicsCloud\Services\AppService;
use App\Apps\TonicsCloud\Services\ContainerService;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;

class CloudJobQueueAutomationStandaloneStaticSite extends AbstractJobInterface implements JobHandlerInterface
{

    use TonicsJobQueueContainerTrait, TonicsJobQueueAutomationTrait;

    private AppService $appService;

    public function __construct (AppService $appService)
    {
        $this->appService = $appService;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle (): void
    {
        $containerID = $this->getContainerID();
        $apps = $this->mapAppsByName(ContainerService::getAppsInContainer($containerID));

        $zipApp = [
            'container_id'  => $containerID,
            'app_id'        => $apps['UnZip']->app_id,
            '_fieldDetails' => TonicsCloudUnZip::createFieldDetails([
                'unzip_extractTo'   => '/var/www/[[ACME_DOMAIN]]',
                'unzip_archiveFile' => '[[ARCHIVE_FILE]]',
                'unzip_format'      => '',
                'unzip_overwrite'   => '1',
            ]),
        ];

        $nginxHTTPMode = $this->NginxMode($containerID, $apps, TonicsCloudNginx::NginxSimple([
            'serverName' => '[[ACME_DOMAIN]]',
            'root'       => '/var/www/[[ACME_DOMAIN]]',
            'ssl'        => false,
        ]));

        $nginxHTTPSMode = $this->NginxMode($containerID, $apps, TonicsCloudNginx::NginxSimple([
            'serverName' => '[[ACME_DOMAIN]]',
            'root'       => '/var/www/[[ACME_DOMAIN]]',
            'ssl'        => true,
        ]));

        $acmeApp = [
            'container_id'  => $containerID,
            'app_id'        => $apps['ACME']->app_id,
            '_fieldDetails' => TonicsCloudACME::createFieldDetails([
                'acme_email'  => '[[ACME_EMAIL]]',
                'acme_mode'   => 'nginx',
                'acme_issuer' => 'Letsencrypt',
                'acme_sites'  => ['[[ACME_DOMAIN]]'],
            ]),
        ];

        /** @var CloudJobQueueUpdateApp $cloudJobQueueUpdateApp */
        $cloudJobQueueUpdateApp = container()->get(CloudJobQueueUpdateApp::class);

        $appsToUpdate = [
            $nginxHTTPMode,
            $acmeApp,
            $nginxHTTPSMode,
            $zipApp,
        ];

        $cloudJobQueueUpdateApp->setData(['appsToUpdate' => $appsToUpdate]);
        TonicsCloudActivator::getJobQueue()->enqueue($cloudJobQueueUpdateApp);
    }
}