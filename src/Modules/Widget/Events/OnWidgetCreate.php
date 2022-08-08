<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Widget\Events;

use App\Modules\Widget\Data\WidgetData;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;
use stdClass;

class OnWidgetCreate implements EventInterface
{
    private stdClass $widget;
    private WidgetData $widgetData;

    /**
     * @param stdClass $widget
     * @param WidgetData|null $widgetData
     */
    public function __construct(stdClass $widget, WidgetData $widgetData = null)
    {
        $this->widget = $widget;
        if (property_exists($widget, 'created_at')){
            $this->widget->created_at = $this->getCatCreatedAt();
        }
        if (property_exists($widget, 'updated_at')){
            $this->widget->updated_at = $this->getCatUpdatedAt();
        }

        if ($widgetData){
            $this->widgetData = $widgetData;
        }
    }

    public function getAll(): stdClass
    {
        return $this->widget;
    }

    public function getAllToArray(): array
    {
        return (array)$this->widget;
    }

    public function getWidgetID(): string|int
    {
        return (property_exists($this->widget, 'widget_id')) ? $this->widget->widget_id : '';
    }

    public function getWidgetTitle(): string
    {
        return (property_exists($this->widget, 'widget_name')) ? $this->widget->widget_name : '';
    }

    public function getWidgetSlug(): string
    {
        return (property_exists($this->widget, 'widget_slug')) ? $this->widget->widget_slug : '';
    }

    public function getCatCreatedAt(): string
    {
        return (property_exists($this->widget, 'created_at')) ? str_replace(' ', 'T', $this->widget->created_at) : '';
    }

    public function getCatUpdatedAt(): string
    {
        return (property_exists($this->widget, 'updated_at')) ? str_replace(' ', 'T', $this->widget->updated_at) : '';
    }

    public function event(): static
    {
        return $this;
    }

    /**
     * @return WidgetData
     */
    public function getWidgetData(): WidgetData
    {
        return $this->widgetData;
    }

    /**
     * @param WidgetData $widgetData
     */
    public function setWidgetData(WidgetData $widgetData): void
    {
        $this->widgetData = $widgetData;
    }
}