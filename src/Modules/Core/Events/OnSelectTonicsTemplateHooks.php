<?php

namespace App\Modules\Core\Events;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

/**
 * Note: This event doesn't directly add any hook to the template, it is just a way to tell user the hooks you
 * have in your templates, this way, user can directly use theme in the Field Builder
 */
class OnSelectTonicsTemplateHooks implements EventInterface
{

    private array $templateHooks = [];
    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function addHook(string $name): static
    {
        $this->templateHooks[$name] = $name;
        return $this;
    }

    /**
     * @param array $hooks
     * @return $this
     */
    public function addMultipleHooks(array $hooks = []): static
    {
        foreach ($hooks as $hook){
            $this->templateHooks[$hook] = $hook;
        }

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hookExist(string $name): bool
    {
        return isset($this->templateHooks[$name]);
    }

    /**
     * @return array
     */
    public function getTemplateHooks(): array
    {
        return $this->templateHooks;
    }

    /**
     * @param array $templateHooks
     */
    public function setTemplateHooks(array $templateHooks): void
    {
        $this->templateHooks = $templateHooks;
    }

}