<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Events;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class TonicsTemplateEngines implements EventInterface
{

    private array $templateEngines = [];
    private array $templateEngineNames = [];

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    /**
     * @param string $name
     * @param TonicsView $tonicsView
     * @return $this
     */
    public function addTemplateEngine(string $name, TonicsView $tonicsView): static
    {
        $this->templateEngines[$name] = $tonicsView;
        $this->templateEngineNames[$name] = $name;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function getTemplateEngine(string $name): TonicsView
    {
        if (isset($this->templateEngineNames[$name])){
            return $this->templateEngines[$name];
        }

        throw new \Exception("$name is an unknown engine name");
    }

    public function exist(string $name): bool
    {
        return isset($this->templateEngineNames[$name]);
    }

    /**
     * @return array
     */
    public function getTemplateEngines(): array
    {
        return $this->templateEngines;
    }

    /**
     * @param array $templateEngines
     */
    public function setTemplateEngines(array $templateEngines): void
    {
        $this->templateEngines = $templateEngines;
    }

    /**
     * @return array
     */
    public function getTemplateEngineNames(): array
    {
        return $this->templateEngineNames;
    }
}