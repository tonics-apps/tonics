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

class RecentPostMenuWidget implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        $event->addMenuWidgetBox('Recent Posts', 'Site Most Recent Posts', function ($data){
            return $this->widgetForm($data);
        }, handleViewProcessing: function () {
            return '';
        });
    }

    public function widgetForm($data = null): string
    {
        $widgetName = 'Recent Posts';
        $widgetTake = 5;
        if (isset($data->take)) {
            $widgetTake = $data->take;
        }

        if (isset($data->widgetName)) {
            $widgetName = $data->widgetName;
        }
        return <<<FORM
<div class="form-group">
    <label class="menu-settings-handle-name" for="widget-name">Overwrite Name
        <input id="widget-name" name="widgetName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray" 
        value="$widgetName" placeholder="Overwrite the widget name">
    </label>
</div>
<div class="form-group">
    <label class="menu-settings-handle-name" for="recent-post-name">Items To Show
        <input name="take" id="recent-post-name" type="number" class="menu-url-slug color:black border-width:default border:black placeholder-color:gray"
         value="$widgetTake">
    </label>
</div>
FORM;
    }
}