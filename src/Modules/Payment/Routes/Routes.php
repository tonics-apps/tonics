<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\Routes;

use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{

    public function routeWeb(Route $route): Route
    {
        return $route;
    }

    public function routeApi(Route $route): Route
    {
        return $route;
    }
}