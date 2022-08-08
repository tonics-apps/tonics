<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Commands\Environmental;

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