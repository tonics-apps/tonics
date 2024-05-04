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

namespace App\Modules\Field\EventHandlers\Fields;

use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Widget\Data\WidgetData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class Widget implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
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
                $this->viewData($event, $data);
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

        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $widgetData = new WidgetData();
        $widgetFrag = '';
        $widgets = $widgetData->getWidgets();
        foreach ($widgets as $widget){
            $uniqueSlug = "$widget->widget_slug";
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
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Field Name ((via [[v('Widget_$inputName.Name')]])
            <input id="fieldName-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
    <label class="menu-settings-handle-name" for="inputName-$changeID">Input Name
            <input id="inputName-$changeID" name="inputName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$inputName" placeholder="(Optional) Input Name">
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="widgetSlug-$changeID">Choose Widget (via [[_v('Widget_$inputName.Data')]])
     <select name="widgetSlug" class="default-selector mg-b-plus-1" id="widgetSlug-$changeID">
        $widgetFrag
     </select>
    </label>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @return string
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Menu';
        $widgetSlug = $event->getKeyValueInData($data, $data->inputName);
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $menuFrag = '';
        $widgetData = new WidgetData();
        $widgets = $widgetData->getWidgets();
        foreach ($widgets as $widget){
            $uniqueSlug = "$widget->widget_slug";
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
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$widgetSlug}_$changeID";
        $frag .= <<<HTML
<div class="form-group">
     <label class="menu-settings-handle-name" for="widgetSlug-$changeID">Choose Widget
     <select name="$inputName" class="default-selector mg-b-plus-1" id="widgetSlug-$changeID">
        $menuFrag
     </select>
    </label>
</div>
HTML;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function viewData(OnFieldMetaBox $event, $data)
    {
        $frag = '';
        $fieldData = (isset($data->_field->field_data)) ? $data->_field->field_data : '';
        $postData = !empty(getPostData()) ? getPostData() : $fieldData;
        $widgetSlug =  (isset($postData[$data->inputName])) ? $postData[$data->inputName] : '';
        if (empty($widgetSlug)){
            return $frag;
        }

        $widgetData = new WidgetData();
        $widget = $widgetData->getWidgetItems($widgetSlug);
        $widgetName = (isset($widget[0]->widget_name)) ? $widget[0]->widget_name : $data->_field->main_field_name;
        $widgetDataArray = [];
        $widgetData->getWidgetViewListing($widget, function ($widgetViewDataInstance, $widgetItem) use (&$frag, &$widgetDataArray){
            $widgetDataArray[] = ['htmlFrag' => $widgetViewDataInstance, 'options' => $widgetItem];
        });
        $inputName =  (isset($data->inputName)) ? $data->inputName : '';
        addToGlobalVariable("Widget_$inputName", ['Name' => $widgetName, 'InputName' => $inputName, 'Data' => $widgetDataArray]);
        return '';
    }
}