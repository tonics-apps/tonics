<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Core\Commands\Environmental;


use App\Modules\Core\Library\ConsoleColor;

abstract class EnvironmentalAbstract
{
    use ConsoleColor;

    /**
     * @param $envKey
     * @param $envValue
     * @return bool
     * @throws \Exception
     */
    protected function setEnvironmentValue($envKey, $envValue): bool
    {
        $envFile = APP_ROOT . DIRECTORY_SEPARATOR . '.env';
        return helper()->updateEnvValue($envFile, $envKey, $envValue);
    }

}