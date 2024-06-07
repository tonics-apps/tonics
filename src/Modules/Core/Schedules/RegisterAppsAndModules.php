<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Core\Schedules;

use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\SchedulerSystem\AbstractSchedulerInterface;
use App\Modules\Core\Library\SchedulerSystem\ScheduleHandlerInterface;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use App\Modules\Core\Services\AppInstallationService;

class RegisterAppsAndModules extends AbstractSchedulerInterface implements ScheduleHandlerInterface
{
    use ConsoleColor;

    public function __construct (private AppInstallationService $appInstallationService)
    {
        $this->setName('Core_RegisterAppAnsModules');
        $this->setPriority(Scheduler::PRIORITY_LOW);
        $this->setEvery(Scheduler::everyHour(6));
    }

    /**
     * @throws \Exception
     */
    public function handle (): void
    {
        $apps = [
            ...helper()->getAppsActivator([ExtensionConfig::class], installed: false),
            ...helper()->getAppsActivator([ExtensionConfig::class], helper()->getAllModulesDirectory(), false),
        ];

        $appSlugs = [];
        /** @var ExtensionConfig $activator */
        foreach ($apps as $app) {
            if (isset($app->info()['slug_id'])) {
                $appSlugs[] = $app->info()['slug_id'];
            }
        }

        # We register apps little by little to reduce the load on the Tonics App Store ;)
        # For stubborn user that might want to do things manually, there is a restriction on the Tonics App Store end as well, it takes what it deems acceptable and discard the rest
        $appChunks = array_chunk($appSlugs, 15);
        $totalAppChunksCount = count($appChunks);
        // loop through each chunk and save it to a separate file
        for ($i = 0; $i < $totalAppChunksCount; $i++) {
            $appSlugsChunk = $appChunks[$i];
            if (!empty($appSlugsChunk)) {
                $this->appInstallationService->setAppSlug($appSlugsChunk);
                $this->appInstallationService->registerApps();
                $registeringBatchNumber = $i + 1;
                if (!$this->appInstallationService->fails()) {
                    # This would register the site to an updated version of the app slugs if there is one, and it supports the PHP version
                    $this->infoMessage("Registering [$registeringBatchNumber]/[$totalAppChunksCount] Batches of App - Sleeping 😴 Before Going Again");
                } else {
                    $errorMessage = $this->appInstallationService->getErrorsAsString();
                    $this->errorMessage("Failed To Register [$registeringBatchNumber]/[$totalAppChunksCount] Batches of App, Error: $errorMessage");
                }

                sleep(5);
            }
        }

        $this->infoMessage("Ended App Registration, Hopefully It Went Well 😉");


    }
}