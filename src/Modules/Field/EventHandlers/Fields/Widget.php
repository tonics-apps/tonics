<?php

namespace App\Modules\Field\EventHandlers\Fields;

use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Widget\Data\WidgetData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class Widget implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox(
            'Widgets',
            'Add Widgets',
            'Widget',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            }, userForm: function ($data) use ($event) {
            return $this->userForm($event, $data);
        },
            handleViewProcessing: function ($data) use ($event) {
                return $this->viewFrag($event, $data);
            }
        );
    }

    /**
     * @throws \Exception
     */
    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Widget';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $widgetSlug = (isset($data->widgetSlug)) ? $data->widgetSlug : '';

        $elementName = (isset($data->elementName)) ? $data->elementName : 'li';
        $attributes = (isset($data->attributes)) ? helper()->htmlSpecChar($data->attributes) : '';

        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $widgetData = new WidgetData();
        $widgetFrag = '';
        $widgets = $widgetData->getWidgets();
        foreach ($widgets as $widget){
            $uniqueSlug = "$widget->widget_slug:$widget->widget_id";
            if ($widgetSlug === $uniqueSlug){
                $widgetFrag .= <<<HTML
<option value="$uniqueSlug" selected>$widget->widget_name</option>
HTML;
            } else {
                $widgetFrag .= <<<HTML
<option value="$uniqueSlug">$widget->widget_name</option>
HTML;
            }
        }

        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';
        $frag .= <<<FORM
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Field Name
            <input id="fieldName-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
    <label class="menu-settings-handle-name" for="inputName-$changeID">Input Name
            <input id="inputName-$changeID" name="inputName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$inputName" placeholder="(Optional) Input Name">
    </label>
</div>
<div class="form-group">
     <label class="menu-settings-handle-name" for="widgetSlug-$changeID">Choose Widget (Access Widget Data in Template Using [[_v('Widget_$inputName')]])
     <select name="widgetSlug" class="default-selector mg-b-plus-1" id="widgetSlug-$changeID">
        $widgetFrag
     </select>
    </label>
</div>
{$event->getTemplateEngineFrag($data)}
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Menu';
        $inputName =  (isset(getPostData()[$data->inputName])) ? getPostData()[$data->inputName] : '';
        $widgetSlug = (isset($data->widgetSlug) && !empty($inputName)) ? $inputName : $data->widgetSlug;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $menuFrag = '';
        $widgetData = new WidgetData();
        $widgets = $widgetData->getWidgets();
        foreach ($widgets as $widget){
            $uniqueSlug = "$widget->widget_slug:$widget->widget_id";
            if ($widgetSlug === $uniqueSlug){
                $menuFrag .= <<<HTML
<option value="$uniqueSlug" selected>$widget->widget_name</option>
HTML;
            } else {
                $menuFrag .= <<<HTML
<option value="$uniqueSlug">$widget->widget_name</option>
HTML;
            }
        }
        $frag .= <<<HTML
<div class="form-group">
     <label class="menu-settings-handle-name" for="widgetSlug-$changeID">Choose Widget
     <select name="widgetSlug" class="default-selector mg-b-plus-1" id="widgetSlug-$changeID">
        $menuFrag
     </select>
    </label>
</div>
HTML;

        $frag .= $event->_bottomHTMLWrapper(true);
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function viewFrag(OnFieldMetaBox $event, $data): string
    {
        $frag = '';
        $inputName =  (isset(getPostData()[$data->inputName])) ? getPostData()[$data->inputName] : '';
        $widgetSlug = (isset($data->widgetSlug) && !empty($inputName)) ? $inputName : $data->widgetSlug;
        if (empty($widgetSlug)){
            return $frag;
        }
        $widgetSlug = explode(':', $widgetSlug);
        $widgetID = (isset($widgetSlug[1]) && is_numeric($widgetSlug[1])) ? (int)$widgetSlug[1]: '';
        if (empty($widgetID)){
            return $frag;
        }
        $widgetData = new WidgetData();
        $widget = $widgetData->decodeWidgetOptions($widgetData->getWidgetItems($widgetID));

        $widgetData->getWidgetViewListing($widget, function ($widgetViewDataInstance, $widgetItem) use (&$frag){
            $frag .=<<<HTML
<nav id="site-navigation" class="site-nav d:flex align-items:center" role="navigation">
<span class="widget-title bg:pure-black color:white padding:small">$widgetItem->widgetName</span>
$widgetViewDataInstance
</nav>
HTML;
        });
        $inputName =  (isset($data->inputName)) ? $data->inputName : '';
        addToGlobalVariable("Widget_$inputName", $frag);
        return '';
    }
}