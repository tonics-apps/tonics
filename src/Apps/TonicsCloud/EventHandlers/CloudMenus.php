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

namespace App\Apps\TonicsCloud\EventHandlers;

use App\Apps\TonicsCloud\Controllers\TonicsCloudSettingsController;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\AdminMenuHelper;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTreeSystem\Tree;

class CloudMenus implements HandlerInterface
{
    const CLOUD_ADMIN = AdminMenuHelper::DASHBOARD . '/TONICS_ADMIN_CLOUD';

    const IMAGES              = self::CLOUD_ADMIN . '/IMAGES';
    const IMAGE_NEW           = self::IMAGES . '/NEW_IMAGE';
    const IMAGE_EDIT          = self::IMAGES . '/EDIT_IMAGE';
    const APP_UPDATE_SETTINGS = self::CLOUD_ADMIN . '/APP_UPDATE_SETTINGS';

    const CLOUD          = AdminMenuHelper::DASHBOARD . '/TONICS_CLOUD';
    const INSTANCES      = self::CLOUD . '/INSTANCES';
    const INSTANCES_NEW  = self::INSTANCES . '/NEW_INSTANCE';
    const INSTANCES_EDIT = self::INSTANCES . '/EDIT_INSTANCE';

    const CONTAINER      = self::CLOUD . '/CONTAINER';
    const CONTAINER_NEW  = self::CONTAINER . '/NEW_CONTAINER';
    const CONTAINER_EDIT = self::CONTAINER . '/EDIT_CONTAINER';

    const CONTAINER_APPS      = self::CONTAINER . '/APPS_CONTAINER';
    const CONTAINER_APPS_EDIT = self::CONTAINER_APPS . '/EDIT_APP_CONTAINER';

    const DOMAIN      = self::CLOUD . '/DOMAIN';
    const DOMAIN_NEW  = self::DOMAIN . '/NEW_DOMAIN';
    const DOMAIN_EDIT = self::DOMAIN . '/EDIT_DOMAIN';

    const BILLING = self::CLOUD . '/BILLING';

    /**
     * @throws \Exception|\Throwable
     */
    public function handleEvent (object $event): void
    {
        tree()->group('', function (Tree $tree) {

            $tree->add(self::CLOUD_ADMIN, [
                'mt_name'     => 'Cloud',
                'mt_url_slug' => route('tonicsCloud.settings'),
                'mt_icon'     => helper()->getIcon('cloud'),
            ]);

            $tree->add(self::IMAGES, [
                'mt_name'     => 'Images',
                'mt_url_slug' => route('tonicsCloud.admin.images.index'),
                'mt_icon'     => helper()->getIcon('archive', 'icon:admin'),
            ]);

            $tree->add(self::IMAGE_NEW, [
                'mt_name'     => 'New Image',
                'mt_url_slug' => route('tonicsCloud.admin.images.create'),
                'mt_icon'     => helper()->getIcon('plus', 'icon:admin'),
            ]);

            $tree->add(self::IMAGE_EDIT, [
                'mt_name'     => 'Edit Image',
                'mt_url_slug' => '/admin/tonics_cloud/images/:image/edit',
                'ignore'      => true,
            ]);

            $tree->add(self::APP_UPDATE_SETTINGS, [
                'mt_name'     => 'RefreshApp',
                'mt_url_slug' => route('tonicsCloud.admin.images.updateApps'),
                'mt_icon'     => helper()->getIcon('refresh', 'icon:admin'),
            ]);

        }, ['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_CORE])]);

        tree()->group('', function (Tree $tree) {

            $tree->add(self::CLOUD, [
                'mt_name'     => 'Cloud',
                'mt_url_slug' => route('tonicsCloud.instances.index'),
                'mt_icon'     => helper()->getIcon('cloud'),
            ]);

            $tree->add(self::INSTANCES, [
                'mt_name'     => 'Instances',
                'mt_url_slug' => route('tonicsCloud.instances.index'),
                'mt_icon'     => helper()->getIcon('server', 'icon:admin'),
            ]);

            $tree->add(self::INSTANCES_NEW, [
                'mt_name'     => 'New Instance',
                'mt_url_slug' => route('tonicsCloud.instances.create'),
                'mt_icon'     => helper()->getIcon('plus', 'icon:admin'),
            ]);

            $tree->add(self::INSTANCES_EDIT, [
                'mt_name'     => 'Edit Instance',
                'mt_url_slug' => '/customer/tonics_cloud/instances/:instance/edit',
                'ignore'      => true,
            ]);

            $tree->add(self::CONTAINER, [
                'mt_name'     => 'Containers',
                'mt_url_slug' => route('tonicsCloud.containers.index'),
                'mt_icon'     => helper()->getIcon('container', 'icon:admin'),
            ]);

            $tree->add(self::CONTAINER_APPS, [
                'mt_name'     => 'Container Apps',
                'mt_url_slug' => '/customer/tonics_cloud/containers/:container/apps',
                'route'       => 'tonicsCloud.containers.apps.index',
                'ignore'      => true,
            ]);

            $tree->add(self::CONTAINER_APPS_EDIT, [
                'mt_name'     => 'Container App Edit',
                'mt_url_slug' => '/customer/tonics_cloud/containers/:container/apps/:app/edit',
                'ignore'      => true,
            ]);

            $tree->add(self::CONTAINER_NEW, [
                'mt_name'     => 'New Container',
                'mt_url_slug' => route('tonicsCloud.containers.create'),
                'mt_icon'     => helper()->getIcon('plus', 'icon:admin'),
            ]);

            $tree->add(self::CONTAINER_EDIT, [
                'mt_name'     => 'Edit Instance',
                'mt_url_slug' => '/customer/tonics_cloud/containers/:container/edit',
                'ignore'      => true,
            ]);

            $tree->add(self::DOMAIN, [
                'mt_name'     => 'Domains',
                'mt_url_slug' => route('tonicsCloud.domains.index'),
                'mt_icon'     => helper()->getIcon('website', 'icon:admin'),
            ]);

            $tree->add(self::DOMAIN_NEW, [
                'mt_name'     => 'New Domain',
                'mt_url_slug' => route('tonicsCloud.domains.create'),
                'mt_icon'     => helper()->getIcon('plus', 'icon:admin'),
            ]);

            $tree->add(self::DOMAIN_EDIT, [
                'mt_name'     => 'Edit Domain',
                'mt_url_slug' => '/customer/tonics_cloud/domains/:domain/edit',
                'ignore'      => true,
            ]);

            if (TonicsCloudSettingsController::billingEnabled()) {
                $tree->add(self::BILLING, [
                    'mt_name'     => 'Billing',
                    'mt_url_slug' => route('tonicsCloud.billings.setting'),
                    'mt_icon'     => helper()->getIcon('shopping-cart', 'icon:admin'),
                ]);
            }

        }, ['permission' => Roles::GET_PERMISSIONS_ID([TonicsCloudActivator::CAN_ACCESS_TONICS_CLOUD])]);
    }
}