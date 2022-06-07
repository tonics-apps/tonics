<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Configs;


class ModuleConfig
{
    /**
     * Module Default Structure
     * @return string[]
     */
    public static function getDefaultStructure(): array
    {
        return [
            'controllers' => 'Controllers',
            'models' => 'Models',
            'views' => 'Views',
            'translations' => 'resources/lang',
            'Routes' => 'Routes',
            'migrations' => 'database/migrations',
        ];
    }

    /**
     * Module Specific Configuration
     */
    public static function getInstances($instances = [])
    {
        return $instances;
    }

}