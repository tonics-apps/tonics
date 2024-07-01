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

namespace App\Modules\Customer\EventHandlers;

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
class CustomerMenus implements HandlerInterface
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

            $tree->add(AdminMenuHelper::CUSTOMER, ['mt_name' => 'Customers', 'mt_url_slug' => route('admin.customers.index'), 'mt_icon' => helper()->getIcon('users', 'icon:admin')]);

        }, ['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_CORE])]);
    }
}
