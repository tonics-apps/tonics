<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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
            // We should prob use an event here, so, user could hook into this and create their own comman
            $commandRegistrar = new CommandRegistrar(
                $container->resolveMany([
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
                ])
            );
        } catch (ReflectionException $e) {
            exit(1);
        }

        $console = new Console($commandRegistrar, $args->getProcessArgs(), $container);
        $console->bootConsole();
    }
}