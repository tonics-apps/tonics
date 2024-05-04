<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
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