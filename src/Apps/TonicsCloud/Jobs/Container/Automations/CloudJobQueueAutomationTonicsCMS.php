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


use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueUpdateApp;
use App\Apps\TonicsCloud\Jobs\Container\Traits\TonicsJobQueueAutomationTrait;
use App\Apps\TonicsCloud\Jobs\Container\Traits\TonicsJobQueueContainerTrait;
use App\Apps\TonicsCloud\Services\AppService;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;

class CloudJobQueueAutomationTonicsCMS extends AbstractJobInterface implements JobHandlerInterface
{

    use TonicsJobQueueContainerTrait, TonicsJobQueueAutomationTrait;

    private AppService $appService;

    /**
     * @throws \Exception
     */
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
        $this->constructorSetup();

        /** @var CloudJobQueueUpdateApp $cloudJobQueueUpdateApp */
        $cloudJobQueueUpdateApp = container()->get(CloudJobQueueUpdateApp::class);

        $appsToUpdate =
            [
                self::APP_SETTING_TONICS_NGINX_HTTP_MODE,
                self::APP_SETTING_MARIADB,
                self::APP_SETTING_TONICS_ENV,
                self::APP_SETTING_PHP,
            ];

        if (isset($this->getDataAsObject()->container_variables->ARCHIVE_FILE)) {
            $appsToUpdate = [
                self::APP_SETTING_TONICS_NGINX_HTTP_MODE,
                self::APP_SETTING_MARIADB,
                self::APP_SETTING_UNZIP,
                self::APP_SETTING_TONICS_EXISTING_ENV,
                self::APP_SETTING_TONICS_SCRIPT,
                self::APP_SETTING_PHP,
            ];
        }

        $cloudJobQueueUpdateApp->setData([
            'appsToUpdate' => $this->pickAppSettings($appsToUpdate, $this->getCurrentContainerID()),
        ]);

        $jobs = [
            [
                'job'      => $cloudJobQueueUpdateApp,
                'children' => [],
            ],
        ];

        TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs);
    }
}