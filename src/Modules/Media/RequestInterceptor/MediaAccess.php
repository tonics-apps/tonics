<?php

namespace App\Modules\Media\RequestInterceptor;

use App\Library\Authentication\Roles;
use App\Library\SimpleState;
use App\Modules\Core\Data\UserData;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInterceptorInterface;

class MediaAccess implements TonicsRouterRequestInterceptorInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handle(OnRequestProcess $request): void
    {
        if (UserData::canAccess(Roles::CAN_ACCESS_MEDIA) === false){
            SimpleState::displayUnauthorizedErrorMessage();
        }
    }
}