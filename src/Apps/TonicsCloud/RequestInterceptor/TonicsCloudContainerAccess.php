<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\RequestInterceptor;

use App\Apps\TonicsCloud\Controllers\ContainerController;
use App\Modules\Core\Library\SimpleState;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInterceptorInterface;

class TonicsCloudContainerAccess implements TonicsRouterRequestInterceptorInterface
{

    /**
     * @throws \Exception
     */
    public function handle(OnRequestProcess $request): void
    {
        $foundURLRequiredParam = $request->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams();
        $containerID = $foundURLRequiredParam[0];

        $container = ContainerController::getContainer($containerID);
        # If isset, then customer has access, we return, otherwise, we display UnauthorizedErrorMessage
        if (isset($container->container_id)){
            return;
        }

        SimpleState::displayUnauthorizedErrorMessage();
    }
}