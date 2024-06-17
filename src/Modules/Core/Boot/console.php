<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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


use App\Modules\Core\Boot\TonicsCoreConsoleEntry;
use App\Modules\Core\Commands\InitConsole;
use Devsrealm\TonicsConsole\ProcessCommandLineArgs;
use Devsrealm\TonicsContainer\Container;

require dirname(__DIR__, 4) . '/src/Modules/Core/Library/Composer/autoload.php';

$args = new ProcessCommandLineArgs($argv);
$container = new Container();

if ($args->passes()) {

    try {
        TonicsCoreConsoleEntry::entry();
    } catch (Exception|Throwable $e) {
        echo $e->getMessage();
        exit(1);
    }

    #
    # INIT Essential COMMANDS
    #
    new InitConsole($container, $args);
}
exit(1);