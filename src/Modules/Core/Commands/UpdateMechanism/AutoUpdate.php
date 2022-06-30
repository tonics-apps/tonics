<?php

namespace App\Modules\Core\Commands\UpdateMechanism;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\States\UpdateMechanismState;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * To run auto-update, you do:
 *
 * <br>
 * `php bin/console --auto-update`
 *
 * This would check the env file to see if all items should be auto updated or some items, this works for plugins, themes and modules
 */
class AutoUpdate implements ConsoleCommand
{
    use ConsoleColor;

    public function required(): array
    {
        return [
            "--auto-update",
        ];
    }

    /**
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        $moduleUpdates = AppConfig::getAutoUpdateModules();
        $pluginUpdates = AppConfig::getAutoUpdatePlugins();
        $themeUpdates = AppConfig::getAutoUpdateThemes();

        $updateMechanismState = new UpdateMechanismState();

        if ($moduleUpdates === true || is_array($moduleUpdates)) {
            $this->infoMessage("Modules Update Initializing...");
            $moduleUpdates = ($moduleUpdates === true) ? [] : $moduleUpdates;
            $updateMechanismState->reset()->setUpdates($moduleUpdates)->setTypes(['module'])->setAction('update')
                ->runStates(false);
        }


        if ($pluginUpdates === true || is_array($pluginUpdates)) {
            $this->infoMessage("Plugins Update Initializing...");
            $pluginUpdates = ($pluginUpdates === true) ? [] : $pluginUpdates;
            $updateMechanismState->reset()->setUpdates($pluginUpdates)->setTypes(['plugin'])->setAction('update')
                ->runStates(false);
        }

        if ($themeUpdates === true || is_array($themeUpdates)) {
            $this->infoMessage("Themes Update Initializing...");
            $themeUpdates = ($themeUpdates === true) ? [] : $themeUpdates;
            $updateMechanismState->reset()->setUpdates($themeUpdates)->setTypes(['theme'])->setAction('update')
                ->runStates(false);
        }

    }
}