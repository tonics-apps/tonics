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
        $frag = '';
        if (isset($data->_topHTMLWrapper)) {
            $topHTMLWrapper = $data->_topHTMLWrapper;
            $slug = $data->_field->field_name ?? null;
            $frag = $topHTMLWrapper($fieldName, $slug);
        }
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
        $handleViewProcessingFrag = $event->handleViewProcessingFrag((isset($data->handleViewProcessing)) ? $data->handleViewProcessing : '');
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
     <label class="menu-settings-handle-name" for="widgetSlug-$changeID">Choose Widget
     <select name="widgetSlug" class="default-selector mg-b-plus-1" id="widgetSlug-$changeID">
        $widgetFrag
     </select>
    </label>
</div>
<div class="form-group d:flex flex-gap align-items:flex-end">
<label class="menu-settings-handle-name" for="elementName-$changeID">Element Name
            <input id="elementName-$changeID" name="elementName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$elementName" placeholder="div, section, etc">
    </label>
    <label class="menu-settings-handle-name" for="element-attributes-$changeID">Element Attributes
            <input id="element-attributes-$changeID" name="attributes" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$attributes" placeholder="e.g class='class-name' id='id-name' or any attributes">
    </label>
</div>
<div class="form-group">
     <label class="menu-settings-handle-name" for="handleViewProcessing-$changeID">Automatically Handle View Processing
     <select name="handleViewProcessing" class="default-selector mg-b-plus-1" id="handleViewProcessing-$changeID">
        $handleViewProcessingFrag
     </select>
    </label>
</div>
FORM;

        if (isset($data->_bottomHTMLWrapper)) {
            $frag .= $data->_bottomHTMLWrapper;
        }

        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Menu';
        $inputName =  (isset($data->_field->postData[$data->inputName])) ? $data->_field->postData[$data->inputName] : '';
        $widgetSlug = (isset($data->widgetSlug) && !empty($inputName)) ? $inputName : $data->widgetSlug;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $topHTMLWrapper = $data->_topHTMLWrapper;
        $slug = $data->field_slug;
        $form = $topHTMLWrapper($fieldName, $slug);

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
        $form .= <<<HTML
<div class="form-group">
     <label class="menu-settings-handle-name" for="widgetSlug-$changeID">Choose Widget
     <select name="widgetSlug" class="default-selector mg-b-plus-1" id="widgetSlug-$changeID">
        $menuFrag
     </select>
    </label>
</div>
HTML;

        if (isset($data->_bottomHTMLWrapper)) {
            $form .= $data->_bottomHTMLWrapper;
        }

        return $form;
    }

    /**
     * @throws \Exception
     */
    public function viewFrag(OnFieldMetaBox $event, $data): string
    {
        $frag = '';
        $elementName = strtolower($data->elementName);
        $attributes = '';
        if (!key_exists($elementName, helper()->htmlTags())) {
            $elementName = "li";
        }
        if (!empty($data->attributes)) {
            $attributes = " " . $event->flatHTMLTagAttributes($data->attributes) . " ";
        }
        if (isset($data->handleViewProcessing) && $data->handleViewProcessing === '1') {
            $inputName =  (isset($data->_field->postData[$data->inputName])) ? $data->_field->postData[$data->inputName] : '';
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
             $widgetData->getWidgetViewListing($widget, function ($widgetViewDataInstance) use ($attributes, $elementName, &$frag){
                $frag .=<<<HTML
<$elementName$attributes>
$widgetViewDataInstance
</$elementName>
HTML;
            });
        }

        return $frag;
    }
}