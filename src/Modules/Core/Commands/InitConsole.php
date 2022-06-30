<?php

namespace App\Modules\Core\Commands;

use App\Modules\Core\Commands\Environmental\SetEnvironmentalPepper;
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
            $commandRegistrar = new CommandRegistrar(
                $container->resolveMany([
                    PreInstallerManager::class,
                    ScheduleManager::class,
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
                ])
            );
        } catch (ReflectionException $e) {
            exit(1);
            // dd($e);
        }

        $console = new Console($commandRegistrar, $args->getProcessArgs(), $container);
        $console->bootConsole();
    }
}