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

class PlainTextMenuWidget implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {

        $event->addMenuWidgetBox('Plain Text', 'Plain Text and Not To Be Confused With Rich Text', function ($data){
            return $this->widgetForm($data);
        }, function ($data) {
            return $this->widgetView($data);
        });
    }

    public function widgetForm($data = null): string
    {
        $widgetName = 'Plain Text';
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
    <textarea id="body-area" name="text_area" class="plain-text" placeholder="You can Start Writing...">$widgetTextArea</textarea>
  </div>
FORM;
    }

    /**
     * @throws \Exception
     */
    public function widgetView($data = null): string
    {
        $widgetTextArea = '';
        if (isset($data->text_area)) {
            $widgetTextArea = $data->text_area;
        }

        $escapeTx = helper()->htmlSpecChar($widgetTextArea);
        return <<<FORM
  <div class="form-group body-area">
    $escapeTx
  </div>
FORM;
    }
}