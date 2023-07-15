<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Boot;

use Ahc\Env\Loader;
use App\Modules\Core\Configs\AppConfig;

class TonicsCoreEntry
{
    public static function entry()
    {
                #-----------------------------
            # LOAD ENV FILE
        #---------------------------------
        (new Loader)->load(AppConfig::getEnvFilePath());

                #-----------------------------------
            # START BOOTING BOOTY ;)
        #-------------------------------------------
        try {
            if (AppConfig::isProduction() === false) {
                error_reporting(E_ALL);
                ini_set("display_errors", "On");
            }

            AppConfig::initLoaderMinimal()->init();
            AppConfig::initLoaderOthers()->BootDaBoot();
        } catch (\Exception $e) {
            echo $e->getMessage();
            // Log..
        } catch (\Throwable $e) {
            echo $e->getMessage();
            // Log..
        }
    }
}