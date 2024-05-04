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