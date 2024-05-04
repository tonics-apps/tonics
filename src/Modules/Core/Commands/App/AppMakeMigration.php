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

namespace App\Modules\Core\Commands\App;

use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\Database;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * TO CREATE A MIGRATION BOILER-PATE,  RUN: `php bin/console --app=page --make:migration=migration_name`
 *
 * Class ModuleMakeMigration
 * @package App\Commands\Module
 */
class AppMakeMigration implements ConsoleCommand
{
    use ConsoleColor;

    public function required(): array
    {
        return [
            "--app",
            "--make:migration"
        ];
    }

    /**
     * @param array $commandOptions
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        $s = DIRECTORY_SEPARATOR; $appName = $commandOptions['--app'];
        if ($appDirectory = helper()->findAppDirectory($appName)) {
            $migrationTemplate = APP_ROOT . "{$s}src{$s}Modules{$s}Core{$s}Commands{$s}Module{$s}Template{$s}AppMigrationExample.txt";
            if ($copiedFile = helper()->copyMigrationTemplate(
                $migrationTemplate,
                migrationName: $commandOptions['--make:migration'],
                moduleDir: $appDirectory,
                moduleName: $appName)) {
                $this->successMessage("App Migration Successfully Created Under '$appName' Migration Directory With Name $copiedFile");
            } else {
                $this->errorMessage("Couldn't Create Migration");
            }
        }
    }
}