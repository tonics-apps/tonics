<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Commands\Environmental;


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