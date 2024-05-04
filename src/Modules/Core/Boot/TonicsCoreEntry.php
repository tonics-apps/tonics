<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
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