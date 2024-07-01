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

namespace App\Apps\TonicsCloud\RequestInterceptor;

use App\Apps\TonicsCloud\Services\ContainerService;
use App\Modules\Core\Library\SimpleState;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInterceptorInterface;

class TonicsCloudContainerAccess implements TonicsRouterRequestInterceptorInterface
{

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle (OnRequestProcess $request): void
    {
        $foundURLRequiredParam = $request->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams();
        $containerID = $foundURLRequiredParam[0];

        $container = ContainerService::getContainer($containerID);
        # If isset, then customer has access, we return, otherwise, we display UnauthorizedErrorMessage
        if (isset($container->container_id)) {
            return;
        }

        SimpleState::displayUnauthorizedErrorMessage();
    }
}