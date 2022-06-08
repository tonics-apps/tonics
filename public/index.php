<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

use Ahc\Env\Loader;
use App\Configs\AppConfig;
use App\Configs\DriveConfig;

## Remove this in production
error_reporting(E_ALL);
ini_set("display_errors", "On");

define('APP_ROOT', dirname(__DIR__));

        #-----------------------------
    # REQUIRE COMPOSER AUTOLOADER
#---------------------------------
require dirname(__FILE__, 2) . '/vendor/autoload.php';

        #-----------------------------
    # LOAD ENV FILE
#---------------------------------
(new Loader)->load(AppConfig::getEnvFilePath());

        #-----------------------------------
    # EACH DAY IS A NEW BEGINNING
#-------------------------------------------

AppConfig::initLoader(false)->BootDaBoot();