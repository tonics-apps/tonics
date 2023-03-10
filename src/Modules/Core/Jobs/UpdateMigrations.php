<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Jobs;

use App\Modules\Core\Commands\Module\MigrateAll;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;

class UpdateMigrations extends AbstractJobInterface implements JobHandlerInterface
{

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        try {
            $migrateAll = new MigrateAll();
            $commandOptions = [
                '--migrate:all' => '',
            ];
            $migrateAll->run($commandOptions);
        } catch (\Throwable $e) {
            // Log...
            $this->errorMessage($e->getMessage());
        }
    }
}