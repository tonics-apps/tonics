<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Modules\Core\Commands\Module;


use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * TO MIGRATE A SPECIFIC MODULE DOWN RUN: php bin/console --module:migrate=Core --down
 *
 * Class ModuleMigrateDown
 * @package App\Commands\Module
 */
class ModuleMigrateDown extends DatabaseMigrationAbstract implements ConsoleCommand
{

    public function required (): array
    {
        return [
            "--module:migrate",
            "--down",
        ];
    }

    /**
     * @param array $commandOptions
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function run (array $commandOptions): void
    {
        $module = $commandOptions['--module:migrate'];
        $moduleDir = helper()->findModuleDirectory($module);
        $this->migrateDown($module, $moduleDir);
    }
}