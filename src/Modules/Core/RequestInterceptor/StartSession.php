<?php

namespace App\Modules\Core\RequestInterceptor;

use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInterceptorInterface;

class StartSession implements TonicsRouterRequestInterceptorInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handle(OnRequestProcess $request): void
    {
        ## Won't touch the db here yet, this is just generating a sessionID in cookie if
        ## it doesn't already exist. It would touch DB as soon as you start writing
        session()->startSession();
    }
}