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
 * TO Create Pepper, RUN: php bin/console --set:environmental --pepper
 *
 * Class SetEnvironmentalPepper
 * @package App\Commands\Environmental
 */
class SetEnvironmentalPepper extends EnvironmentalAbstract implements ConsoleCommand
{

    public function required(): array
    {
        return [
            "--set:environmental",
            "--pepper"
        ];
    }

    /**
     * @param array $commandOptions
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        if ($this->setEnvironmentValue('PEPPER', helper()->randString())){
            $this->successMessage("PEPPER Added");
        } else {
            $this->errorMessage("Failed To Add Environmental PEPPER");
            exit();
        }
    }
}