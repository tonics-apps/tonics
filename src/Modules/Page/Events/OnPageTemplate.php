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

namespace App\Modules\Page\Events;

use App\Modules\Field\Data\FieldData;
use App\Modules\Page\Events\AbstractClasses\PageTemplateInterface;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnPageTemplate implements EventInterface
{
    private array $fieldSettings = [];
    private array $templateNames = [];
    private string $viewName = '';

    public function event(): static
    {
        return $this;
    }

    /**
     * @param PageTemplateInterface $pageTemplate
     * @return $this
     */
    public function addTemplate(PageTemplateInterface $pageTemplate): static
    {
        $this->templateNames[$pageTemplate->name()] = $pageTemplate::class;
        return $this;
    }

    public function exist(string $name): bool
    {
        return isset($this->templateNames[$name]);
    }

    /**
     * @throws \Exception
     */
    public function getTemplate(string $name): PageTemplateInterface
    {
        if ($this->exist($name)){
            return container()->get($this->templateNames[$name]);
        }

        throw new \Exception("$name is an unknown template name");
    }

    /**
     * @return array
     */
    public function getTemplateNames(): array
    {
        return $this->templateNames;
    }

    /**
     * @param array $templateNames
     */
    public function setTemplateNames(array $templateNames): void
    {
        $this->templateNames = $templateNames;
    }

    /**
     * @return array
     */
    public function getFieldSettings(): array
    {
        return $this->fieldSettings;
    }

    /**
     * @param array $fieldSettings
     */
    public function setFieldSettings(array $fieldSettings): void
    {
        $this->fieldSettings = $fieldSettings;
    }

    /**
     * @return string
     */
    public function getViewName(): string
    {
        return $this->viewName;
    }

    /**
     * @param string $viewName
     */
    public function setViewName(string $viewName): void
    {
        $this->viewName = $viewName;
    }

    public function getFieldData()
    {
        return new FieldData();
    }
}