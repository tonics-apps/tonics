<?php

namespace App\Modules\Track\RequestInterceptor;

use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\SimpleState;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInterceptorInterface;

class TrackAccess implements TonicsRouterRequestInterceptorInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handle(OnRequestProcess $request): void
    {
        if (UserData::canAccess(Roles::CAN_ACCESS_TRACK) === false){
            SimpleState::displayUnauthorizedErrorMessage();
        }
    }
}