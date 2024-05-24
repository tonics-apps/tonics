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

namespace App\Modules\Core\EventHandlers;

use App\Modules\Core\Boot\InitLoader;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Library\AdminMenuHelper;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTreeSystem\Tree;

/**
 * This Listens to the OnAdminMenu and Whenever the event fires, we call this listener
 *
 * The purpose of this listener is to add core menu functionality, such as Dashboard, Settings, and anything related to core
 * Class DefaultTemplate
 * @package Modules\Core\EventHandlers
 */
class CoreMenus implements HandlerInterface
{

    /**
     * @param object $event
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function handleEvent (object $event): void
    {
        \tree()->group('', function (Tree $tree) {

            $tree->add(AdminMenuHelper::DASHBOARD, ['mt_name' => 'Dashboard', 'mt_url_slug' => route('admin.dashboard'), 'ignore' => true, 'home' => true]);

            $tree->add(AdminMenuHelper::TOOL, ['mt_name' => 'Tools', 'mt_url_slug' => '#0', 'mt_icon' => helper()->getIcon('tools', 'icon:admin')]);

            $tree->add(AdminMenuHelper::APPS, ['mt_name' => 'Apps', 'mt_url_slug' => route('apps.index'), 'mt_icon' => helper()->getIcon('plugin', 'icon:admin')]);

            $tree->add(AdminMenuHelper::APP_FORCE_UPDATE_CHECK, ['mt_name' => 'Force Update Check', 'mt_url_slug' => route('apps.discover_updates'), 'mt_icon' => helper()->getIcon('more-horizontal', 'icon:admin')]);

            $tree->add(AdminMenuHelper::APP_UPLOAD_APP, ['mt_name' => 'Upload App', 'mt_url_slug' => route('apps.uploadForm'), 'mt_icon' => helper()->getIcon('upload', 'icon:admin')]);

            # Crumb For App/Modules
            foreach ($this->getAllAppsSettingsRoute() as $key => $route) {

                $tree->add(AdminMenuHelper::APP_SETTINGS . $key, [
                    'mt_name'     => 'Edit App Settings',
                    'mt_url_slug' => $route,
                    'ignore'      => true,
                ]);

            }

            $tree->add(AdminMenuHelper::JOB_MANAGER, ['mt_name' => 'Job Manager', 'mt_url_slug' => route('jobs.jobsIndex'), 'mt_icon' => helper()->getIcon('jobs', 'icon:admin')]);

            $tree->add(AdminMenuHelper::JOBS, ['mt_name' => 'Jobs', 'mt_url_slug' => route('jobs.jobsIndex'), 'mt_icon' => helper()->getIcon('jobs', 'icon:admin')]);

            $tree->add(AdminMenuHelper::JOB_SCHEDULER, ['mt_name' => 'Jobs Scheduler', 'mt_url_slug' => route('jobs.jobsSchedulerIndex'), 'mt_icon' => helper()->getIcon('schedule', 'icon:admin')]);

        }, ['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_CORE])]);

        \tree()->group('', function (Tree $tree) {

            $tree->add(AdminMenuHelper::CUSTOMER_ORDERS, ['mt_name' => 'Orders', 'mt_url_slug' => route('customer.order.index'), 'mt_icon' => helper()->getIcon('cart', 'icon:admin')]);

        }, ['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_CUSTOMER])], AdminMenuHelper::PRIORITY_EXTREME);
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getAllAppsSettingsRoute (): array
    {
        $apps = InitLoader::getAllApps();
        $internal_modules = helper()->getModuleActivators([ExtensionConfig::class]);
        $apps = [...$apps, ...$internal_modules];
        $settings = [];
        /** @var ExtensionConfig $app */
        foreach ($apps as $app) {
            if (isset($app->info()['settings_page']) && !empty($app->info()['settings_page'])) {
                $settings[helper()->randString(10)] = $app->info()['settings_page'];
            }
        }

        return $settings;
    }
}
