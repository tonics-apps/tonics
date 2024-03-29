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