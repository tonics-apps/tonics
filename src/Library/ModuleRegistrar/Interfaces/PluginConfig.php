<?php

namespace App\Library\ModuleRegistrar\Interfaces;

interface PluginConfig
{
    /**
     * Would be called anytime a plugin,theme,module is installed or updated,
     * so, you can do your check here, e.g. update schema, or whatever
     * @return void
     */
    public function onInstall(): void;

    public function onUninstall(): void;

    public function info(): array;
}