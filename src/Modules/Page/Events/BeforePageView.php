<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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