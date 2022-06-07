<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Media;

use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\Tables;
use App\Modules\Media\Routes\Routes;
use Devsrealm\TonicsRouterSystem\Route;

class MediaActivator implements ModuleConfig
{
    use Routes;

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function events(): array
    {
        return [];
    }

    /**
     * @throws \ReflectionException
     */
    public function route(Route $routes): Route
    {
        $this->routeWeb($routes);
        return $this->routeApi($routes);
    }

    /**
     * @return array
     */
    public function tables(): array
    {
        return
            [
                Tables::getTable(Tables::DRIVE_BLOB_COLLATOR) => Tables::getTable(Tables::DRIVE_BLOB_COLLATOR),
                Tables::getTable(Tables::DRIVE_SYSTEM) => Tables::getTable(Tables::DRIVE_SYSTEM),
            ];
    }
}