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

namespace App\Modules\Core\Commands\PreInstall;

use App\Modules\Core\Commands\Environmental\SetEnvironmentalKey;
use App\Modules\Core\Commands\Module\MigrateAll;
use App\Modules\Core\Commands\UpdateLocalDriveFilesInDb;
use App\Modules\Core\Library\ConsoleColor;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * The PreInstallerManager is nothing more than a class that encapsulate a specific set of commands that should be run while installing the system,
 * more like pre-installation of a set of suites that takes care of the necessary functionality before using the system or app.
 *
 * <br>
 * For example, you can have a utility that collate info in the .env file (site name, mail info, db info, etc),
 * we can also have one that scan an upload directory, and get the files ready for use in the drive manager.
 *
 * <br>
 * On a new system, run: `php bin/console --run --preinstall`, you can also run preinstall individually, e.g, for UpdateLocalDriveFilesInDb,
 * you can do: `php bin/console --run --preinstall=update:drivelocal:db`, the value of the pre-installation might differ, so, check their `required()` method
 *
 * Class PreInstallerManager
 * @package App\Commands\PreInstall
 */
class PreInstallerManager implements ConsoleCommand
{
    use ConsoleColor;

    public function required(): array
    {
        return [
            "--run",
            "--preinstall"
        ];
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        $registrars = $this->registerToBePreInstalled();
        foreach ($registrars as $registrar) {
            $className = explode("\\", get_class($registrar));
            $className = $className[array_key_last($className)];
            /**
             * @var $registrar ConsoleCommand
             */
            if ($registrar instanceof ConsoleCommand) {
                // want to run all the registrars schedule
                if (empty($commandOptions['--preinstall'])) {
                    $this->infoMessage("Running {$className} In Pre-Install");
                    $registrar->run($commandOptions);
                    $this->successMessage("Running $className Completed");
                } else {
                    // want to run the $registrar one at a time
                    if ($registrar->required() === (array)$commandOptions['--preinstall']) {
                        $this->infoMessage("Running {$className} In Pre-Install");
                        $registrar->run($commandOptions);
                        $this->successMessage("Running $className Completed");
                        break;
                    }
                }
            }
        }
    }

    /**
     * @return array
     * @throws \ReflectionException
     * @throws \Exception
     */
    private function registerToBePreInstalled(): array
    {
        return \container()->resolveMany([
            SetEnvironmentalKey::class,
            MigrateAll::class,
            UpdateLocalDriveFilesInDb::class
        ]);
    }
}