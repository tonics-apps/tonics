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

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class BeforePageView implements EventInterface
{
    private array $fieldSettings = [];
    private string $pagePath = '';
    private bool $cacheData = true;
    private string $viewName = ''; // TODO: Add a default view name

    /**
     * @throws \Exception
     */
    public function __construct($fieldSettings = [], $pagePath = '')
    {
        $this->fieldSettings = $fieldSettings;
        $this->pagePath = $pagePath;
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
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
    public function getPagePath(): string
    {
        return $this->pagePath;
    }

    /**
     * @param string $pagePath
     */
    public function setPagePath(string $pagePath): void
    {
        $this->pagePath = $pagePath;
    }

    /**
     * @return bool
     */
    public function isCacheData(): bool
    {
        return $this->cacheData;
    }

    /**
     * @param bool $cacheData
     */
    public function setCacheData(bool $cacheData): void
    {
        $this->cacheData = $cacheData;
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
}