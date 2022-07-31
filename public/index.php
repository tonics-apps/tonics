<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

use Ahc\Env\Loader;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Tables;
use App\Themes\NinetySeven\Library\PostLoop;

define('APP_ROOT', dirname(__DIR__));

        #-----------------------------
    # REQUIRE COMPOSER AUTOLOADER
#---------------------------------
require dirname(__FILE__, 2) . '/src/Modules/Core/Library/Composer/autoload.php';

        #-----------------------------
    # LOAD ENV FILE
#---------------------------------
(new Loader)->load(AppConfig::getEnvFilePath());

if (AppConfig::isProduction() === false){
    error_reporting(E_ALL);
    ini_set("display_errors", "On");
}


        #-----------------------------------
    # EACH DAY IS A NEW BEGINNING
#-------------------------------------------
AppConfig::initLoaderMinimal(false)->init();
AppConfig::initLoaderOthers(false)->BootDaBoot();