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

namespace App\Modules\Core\Commands;

use App\Modules\Core\Commands\App\AppBoilerPlate;
use App\Modules\Core\Commands\App\AppMakeMigration;
use App\Modules\Core\Commands\App\AppMigrate;
use App\Modules\Core\Commands\Environmental\SetEnvironmentalPepper;
use App\Modules\Core\Commands\Job\JobManager;
use App\Modules\Core\Commands\Module\MigrateAll;
use App\Modules\Core\Commands\Module\MigrateAllFresh;
use App\Modules\Core\Commands\Module\ModuleMakeConsole;
use App\Modules\Core\Commands\Module\ModuleMakeMigration;
use App\Modules\Core\Commands\Module\ModuleMigrate;
use App\Modules\Core\Commands\Module\ModuleMigrateDown;
use App\Modules\Core\Commands\PreInstall\PreInstallerManager;
use App\Modules\Core\Commands\Scheduler\ScheduleManager;
use App\Modules\Core\Commands\Sync\SyncDirectory;
use App\Modules\Core\Commands\UpdateMechanism\AutoUpdate;
use App\Modules\Core\Commands\UpdateMechanism\Updates;
use App\Modules\Core\Events\OnAddConsoleCommand;
use Devsrealm\TonicsConsole\CommandRegistrar;
use Devsrealm\TonicsConsole\Console;
use Devsrealm\TonicsConsole\ProcessCommandLineArgs;
use Devsrealm\TonicsContainer\Container;
use ReflectionException;

class InitConsole
{
    public function __construct(Container $container, ProcessCommandLineArgs $args)
    {
        #
        # REGISTER COMMANDS
        #
        try {
            /** @var OnAddConsoleCommand $otherConsoleCommands */
            # Third-Party Console Commands
            $otherConsoleCommands = event()->dispatch(new OnAddConsoleCommand())->event();
            $coreConsoleCommands = [
                SetupTonics::class,
                OnStartUpCLI::class,
                PreInstallerManager::class,
                ScheduleManager::class,
                JobManager::class,
                ModuleMakeConsole::class,
                ModuleMakeMigration::class,
                ModuleMigrate::class,
                MigrateAllFresh::class,
                MigrateAll::class,
                ModuleMigrateDown::class,
                // SetEnvironmentalKey::class, <- No Longer Needed
                SetEnvironmentalPepper::class,
                ClearCache::class,
                SyncDirectory::class,
                Updates::class,
                AutoUpdate::class,
                // For Apps
                AppBoilerPlate::class,
                AppMakeMigration::class,
                AppMigrate::class
            ];

            foreach ($otherConsoleCommands->getConsoleCommands() as $consoleCommand){
                $coreConsoleCommands[] = $consoleCommand;
            }

            $commandRegistrar = new CommandRegistrar(
                $container->resolveMany($coreConsoleCommands)
            );
        } catch (ReflectionException|\Exception $e) {
            exit(1);
        }

        $console = new Console($commandRegistrar, $args->getProcessArgs(), $container);
        $console->bootConsole();
    }
}