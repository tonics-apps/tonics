<?php

namespace App\Modules\Page\RequestInterceptor;

use App\Library\Authentication\Roles;
use App\Library\SimpleState;
use App\Modules\Core\Data\UserData;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInterceptorInterface;

class PageAccess implements TonicsRouterRequestInterceptorInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handle(OnRequestProcess $request): void
    {
        if (UserData::canAccess(Roles::CAN_ACCESS_PAGE) === false){
            SimpleState::displayUnauthorizedErrorMessage();
        }
    }
}