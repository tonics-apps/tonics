<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Commands\Module;

use App\Modules\Core\Library\ConsoleColor;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * TO CREATE A MIGRATION BOILER-PATE,  RUN: `php bin/console --module=page --make:migration=migration_name`
 *
 * Class ModuleMakeMigration
 * @package App\Commands\Module
 */
class ModuleMakeMigration implements ConsoleCommand
{
    use ConsoleColor;

    public function required(): array
    {
        return [
            "--module",
            "--make:migration"
        ];
    }

    /**
     * @param array $commandOptions
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {


/*        for ($i = 1; $i <=10; ++$i){
            $pid = pcntl_fork();
            if($pid == 0){
                # Avoids Closing Connection When Child Exist, this way we prevent a connection, e.g. db from closing abruptly
                # If we did not do this, then it means when one forked process closes the db, it closes the rest, why? because the forked
                # processes would share the same resources, so what the below does is tell the child process to kill itself before exit
                register_shutdown_function(function(){
                    posix_kill(getmypid(), SIGKILL);
                });
                exit();
            }

            print "in child " . $i . ' with db ' . db()->row("SELECT post_id FROM tonics_posts order by post_id asc limit 1 FOR UPDATE SKIP LOCKED;")->post_id . "\n";

        }
        exit();*/


        $s = DIRECTORY_SEPARATOR; $module = ucfirst(strtolower($commandOptions['--module']));
        if ($moduleDir = helper()->findModuleDirectory($module)) {
            $migrationTemplate = APP_ROOT . "{$s}src{$s}Modules{$s}Core{$s}Commands{$s}Module{$s}Template{$s}MigrationExample.txt";
            if ($copiedFile = helper()->copyMigrationTemplate(
                $migrationTemplate,
                migrationName: $commandOptions['--make:migration'],
                moduleDir: $moduleDir,
                moduleName: $module)) {
                $this->successMessage("Migration Successfully Created Under '$module' Migration Directory With Name $copiedFile");
            } else {
                $this->errorMessage("Couldn't Create Migration");
            }
        }
    }
}