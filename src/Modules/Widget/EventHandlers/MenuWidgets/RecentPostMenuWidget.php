<?php

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