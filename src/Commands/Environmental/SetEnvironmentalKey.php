<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Commands\Environmental;

use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * TO Create Encryption Key, RUN: php bin/console --set:environmental --key
 *
 * Class SetEnvironmentalKey
 * @package App\Commands\Environmental
 */
class SetEnvironmentalKey extends EnvironmentalAbstract implements ConsoleCommand
{

    public function required(): array
    {
        return [
            "--set:environmental",
            "--key"
        ];
    }

    /**
     * @param array $commandOptions
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        # Update key would aid developer to identify premium users if developers are providing premium extensions
        if ($this->setEnvironmentValue('APP_KEY', helper()->randString()) && $this->setEnvironmentValue('UPDATE_KEY', helper()->randString())){
            $this->successMessage("App Key Added");
        } else {
            $this->errorMessage("Failed To Add Environmental Key");
            exit();
        }
    }
}