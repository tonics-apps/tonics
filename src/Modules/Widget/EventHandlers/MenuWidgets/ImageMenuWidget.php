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

namespace App\Modules\Widget\EventHandlers\MenuWidgets;

use App\Modules\Widget\Events\OnMenuWidgetMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class ImageMenuWidget implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var OnMenuWidgetMetaBox $event */
        $event->addMenuWidgetBox('Image', 'Display an Image', function ($data){
            return $this->widgetForm($data);
        }, function ($data) {
            return $this->widgetView($data);
        });
    }

    public function widgetForm($data = null): string
    {
        $widgetName = 'Image';
        $imagSrc = '';
        $link = '';
        if (isset($data->src)) {
            $imagSrc = $data->src;
        }

        if (isset($data->link)) {
            $link = $data->link;
        }

        if (isset($data->widgetName)) {
            $widgetName = $data->widgetName;
        }

        return <<<FORM
<div class="form-group">
    <label class="menu-settings-handle-name" for="widget-name">Overwrite Name
        <input id="widget-name" name="widgetName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray" data-name="name" 
        value="$widgetName" placeholder="Overwrite the widget name">
    </label>
</div>
<div class="form-group">
<label class="menu-settings-handle-name" for="image-link">Image Link
            <input id="image-link" name="link" placeholder="Image Link" type="url" class="menu-name color:black border-width:default border:black placeholder-color:gray" name="image_url" value="$link">
    </label>
</div>
<div class="form-group">
    <input id="featured-image" class="tonics-featured-image color:black border-width:default border:black placeholder-color:gray" name="featured_image" type="file">
</div>
<div class="form-group">
            <input name="src" placeholder="Image Link" type="hidden" data-widget-image-name="true" class="menu-name color:black border-width:default border:black placeholder-color:gray" name="image_url" value="$imagSrc">
    <img src="$imagSrc" class="image:featured-image featured-image widgetSettings" alt="">
</div>
<div class="form-group">
    <button type="button" class="remove-featured-image d:none background:transparent border:none color:black bg:white-one border-width:default border:black padding:default margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">
        Remove Featured Image
    </button>
</div>
FORM;

    }

    public function widgetView($data = null): string
    {
        $widgetName = 'Image';
        $imagSrc = '';
        $link = '';
        if (isset($data->src)) {
            $imagSrc = $data->src;
        }

        if (isset($data->link)) {
            $link = $data->link;
        }

        if (isset($data->widgetName)) {
            $widgetName = $data->widgetName;
        }
        return <<<VIEW
<div class="image-menu-widget">
<a href="$link">
    <img src="$imagSrc" alt="$widgetName">
</a>
</div>
VIEW;
    }
}