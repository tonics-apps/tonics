<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

use App\Modules\Core\Boot\TonicsCoreEntry;

define('APP_ROOT', dirname(__DIR__));

        #-----------------------------
    # REQUIRE COMPOSER AUTOLOADER
#---------------------------------
require dirname(__FILE__, 2) . '/src/Modules/Core/Library/Composer/autoload.php';

        #-----------------------------------
    # EACH DAY IS A NEW BEGINNING
#-------------------------------------------
TonicsCoreEntry::entry();