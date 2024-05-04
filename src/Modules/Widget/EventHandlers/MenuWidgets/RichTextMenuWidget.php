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

class RichTextMenuWidget implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var OnMenuWidgetMetaBox $event */
        $event->addMenuWidgetBox('Rich Text', 'Rich Text (Can Contain HTML Input)', function ($data){
            return $this->widgetForm($data);
        }, function ($data){
            return $this->widgetView($data);
        });
    }

    public function widgetForm($data = null): string
    {
        $widgetName = 'Rich Text';
        $widgetTextArea = '';
        if (isset($data->text_area)) {
            $widgetTextArea = $data->text_area;
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
  <div class="form-group body-area">
    <label id="post-body" for="body-area" class="screen-reader-text">This is the body, you can start writing here...</label>
    <textarea id="" name="text_area" class="tinyMCEBodyArea widgetSettings" placeholder="You can Start Writing...">$widgetTextArea</textarea>
  </div>
FORM;
    }

    public function widgetView($data = null): string
    {
        $widgetTextArea = '';
        if (isset($data->text_area)) {
            $widgetTextArea = $data->text_area;
        }
        return <<<FORM
  <div class="form-group body-area">
    $widgetTextArea
  </div>
FORM;

    }
}