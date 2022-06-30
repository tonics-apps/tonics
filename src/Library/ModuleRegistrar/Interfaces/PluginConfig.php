<?php

namespace App\Library\ModuleRegistrar\Interfaces;

interface PluginConfig
{
    /**
     * Would be called anytime an extension is installed
     * so, you can do your check here, e.g. update schema, or whatever
     * @return void
     */
    public function onInstall(): void;

    /**
     * Would be called anytime an extension is uninstalled
     * @return void
     */
    public function onUninstall(): void;

    /**
     * Would be called anytime an extension is updated,
     * so, you can do your check here, e.g. update schema, or whatever
     * @return void
     */
    public function onUpdate(): void;

    public function info(): array;
}