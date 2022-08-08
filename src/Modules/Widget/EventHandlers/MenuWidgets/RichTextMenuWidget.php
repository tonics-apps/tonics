<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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