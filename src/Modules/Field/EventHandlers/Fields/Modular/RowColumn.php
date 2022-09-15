<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\EventHandlers\Fields\Modular;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class RowColumn implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        $script = AppConfig::getModuleAsset('Core', '/js/views/field/native/script.js');
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox(
            'RowColumn',
            'Add an Unlimited Number of Row or Column',
            'Modular',
            $script,
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'RowColumn';
        $row = 1;
        $column = 1;
        $inputName = (isset($data->inputName)) ? $data->inputName : '';

        if (isset($data->row)) {
            $row = $data->row;
        }

        if (isset($data->column)) {
            $column = $data->column;
        }

        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';

        $useTab = (isset($data->useTab)) ? $data->useTab : '0';
        $useTab = $event->booleanOptionSelect($useTab);

        $group = (isset($data->group)) ? $data->group : '0';
        $group = $event->booleanOptionSelect($group);

        $more = <<<HTML
<div class="form-group">
     <label class="menu-settings-handle-name" for="useTab-$changeID">Use Tabs
     <select name="useTab" class="default-selector mg-b-plus-1" id="useTab-$changeID">
           $useTab
      </select>
    </label>
</div>
<div class="form-group">
     <label class="menu-settings-handle-name" for="group-$changeID">Group
     <select name="group" class="default-selector mg-b-plus-1" id="group-$changeID">
           $group
      </select>
    </label>
</div>
HTML;

        $frag .= <<<HTML
<div class="row-col-parent owl" data-depth="0">
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="widget-name-$changeID">Field Name
            <input id="widget-name-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
 <label class="menu-settings-handle-name" for="inputName-$changeID">Input Name
        <input id="inputName-$changeID" name="inputName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
        value="$inputName" placeholder="(Optional) Input Name">
</label>
</div>
<div class="form-group d:flex flex-d:row flex-gap:small rowColumn">
    <label class="menu-settings-handle-name" for="widget-row-$changeID">Row
        <input id="widget-row-$changeID" name="row" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray" data-name="row" 
        value="$row" placeholder="Overwrite the widget name">
    </label>
       <label class="menu-settings-handle-name" for="widget-column-$changeID">Column
        <input id="widget-column-$changeID" name="column" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray" data-name="column" 
        value="$column">
    </label>
</div>
{$event->generateMoreSettingsFrag($data, $more)}
    <div style="--row:$row; --column:$column;" class="cursor:pointer form-group d:grid flex-gap:small overflow-x:auto overflow-y:auto rowColumnItemContainer grid-template-rows grid-template-columns">
HTML;

        $cell = $row * $column;
        if (isset($data->_field)) {
            for ($i = 1; $i <= $cell; $i++) {
                $changeID = helper()->randString(10);

                $frag .= <<<HTML
<ul style="margin-left: 0; transform: unset; box-shadow: unset;" class="row-col-item">
     <div class="form-group">
      <label class="menu-settings-handle-name" for="cell-select-$changeID">Select & Choose Field
        <input id="cell-select-$changeID" type="checkbox" name="cell">
      </label>
     </div>
HTML;
                if (isset($data->_field->_children)) {
                    foreach ($data->_field->_children as $child) {
                        $childCellNumber = (isset($child->field_options->{$child->field_name . "_cell"}))
                            ? (int)$child->field_options->{$child->field_name . "_cell"}
                            : $i;

                        if ($childCellNumber === $i) {
                            if (isset($child->field_options)) {
                                $child->field_options->{"_field"} = $child;
                            }
                            $frag .= $event->getSettingsForm($child->field_name, $child->field_options ?? null);
                        }
                    }
                }

                $frag .= <<<HTML
</ul>
HTML;
            }
        } else {
            $frag .= <<<HTML
<ul style="margin-left: 0; transform: unset; box-shadow: unset;" class="row-col-item">
     <div class="form-group d:flex flex-d:column flex-gap:small">
      <label class="menu-settings-handle-name" for="cell-select-$changeID">Select & Choose Field
        <input id="cell-select-$changeID" type="checkbox" name="cell">
      </label>
     </div>
HTML;
        }
        $frag .= <<<HTML
    </div>
</div>
HTML;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;

    }

    /**
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $useTabs = isset($data->useTab) && $data->useTab === '1';
        $isGroup = isset($data->group) && $data->group === '1';
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'RowColumn';
        $row = 1;
        $column = 1;
        if (isset($data->row)) {
            $row = $data->row;
        }

        if (isset($data->column)) {
            $column = $data->column;
        }

        if ($isGroup){
            $frag = $event->_topHTMLWrapper($fieldName, $data, true, function ($isEditorWidgetSettings, $toggle){
                return <<<HTML
<li tabIndex="0" class="width:100% draggable menu-arranger-li cursor:move field-builder-items overflow:auto">
            <div $isEditorWidgetSettings role="form" data-widget-form="true" class="widgetSettings owl flex-d:column menu-widget-information cursor:pointer width:100% {$toggle['div']}">
HTML;
            });
        } else {
            $frag = $event->_topHTMLWrapper($fieldName, $data, true);
        }

        $cell = $row * $column;
        $fieldNameTabUnique = $fieldName . '_' . helper()->randString(10);
        // Having grid-template-columns: repeat(autofit, var(--column-width)); cancels out any row or col number
        // This is intended to make things responsive for user

        # The Tabs Version:
        if ($useTabs){
            $frag .= <<<HTML
<ul class="tabs tonicsFieldTabsContainer color:black bg:white-one border-width:default border:black">
<style>
.tonicsFieldTabsContainer {
     font-size: unset; 
     max-width: unset; 
     min-width: unset; 
     width: unset; 
}

.tabs.tonicsFieldTabsContainer {
    background: unset;
    margin-left: unset;
    margin-right: unset;
}
</style>
HTML; $first = false;
            for ($i = 1; $i <= $cell; $i++) {
                if (!isset($data->_field->_children)) {
                    continue;
                }

                if (isset($data->_field->_children)) {
                    foreach ($data->_field->_children as  $child) {
                        $childCellNumber = (isset($child->field_options->{$child->field_name . "_cell"}))
                            ? (int)$child->field_options->{$child->field_name . "_cell"}
                            : $i;

                        if ($childCellNumber === $i) {
                            if (isset($child->field_options)) {
                                $child->field_options->{"_field"} = $child;
                            }
                            if (!$first){ $first = true; $checked = 'checked'; } else $checked = '';
                            $fieldOptionName = $child->field_options->fieldName;
                            $frag .= <<<HTML
<input tabindex="0" type="radio" id="{$fieldOptionName}_field" name="$fieldNameTabUnique" $checked>
<label tabindex="0" for="{$fieldOptionName}_field">$fieldOptionName</label>
HTML;
                            $frag .= $event->getUsersForm($child->field_name, $child->field_options ?? null);
                        }
                    }
                }
            }
            $frag .= <<<HTML
</ul>
HTML;
        } else {

            if ($isGroup){
                $frag .= <<<HTML
<div class="row-col-parent" data-depth="0">
    <ul style="margin-left: unset;" class="cursor:pointer form-group d:grid flex-gap:small overflow-x:auto overflow-y:auto rowColumnItemContainer">
HTML;
            } else {
                $frag .= <<<HTML
<div class="row-col-parent" data-depth="0">
    <div style="--row:$row; --column:$column; grid-template-columns: repeat(autofit, var(--column-width));" class="cursor:pointer form-group d:grid flex-gap:small overflow-x:auto overflow-y:auto rowColumnItemContainer grid-template-rows grid-template-columns">
HTML;
            }

            for ($i = 1; $i <= $cell; $i++) {
                if (!isset($data->_field->_children)) {
                    continue;
                }

                if (!$isGroup){
                    $frag .= <<<HTML
<ul style="margin-left: 0; transform: unset; box-shadow: unset;" class="row-col-item owl">
HTML;
                }

                if (isset($data->_field->_children)) {
                    foreach ($data->_field->_children as $child) {
                        $childCellNumber = (isset($child->field_options->{$child->field_name . "_cell"}))
                            ? (int)$child->field_options->{$child->field_name . "_cell"}
                            : $i;

                        if ($childCellNumber === $i) {
                            if (isset($child->field_options)) {
                                $child->field_options->{"_field"} = $child;
                            }
                            $frag .= $event->getUsersForm($child->field_name, $child->field_options ?? null);
                        }
                    }
                }
                if (!$isGroup){
                    $frag .= <<<HTML
</ul>
HTML;
                }
            }

            if ($isGroup){
                $frag .= <<<HTML
    </ul>
</div>
HTML;
            } else {
                $frag .= <<<HTML
    </div>
</div>
HTML;
            }
        }

        if ($isGroup){
            $frag .= $event->_bottomHTMLWrapper(function (){
                return "</div></li>";
            });
        } else {
            $frag .= $event->_bottomHTMLWrapper();
        }
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function viewFrag(OnFieldMetaBox $event, $data): string
    {
        $frag = '';
        if (isset($data->_field->_children)) {
            foreach ($data->_field->_children as $child) {
                if (isset($child->field_options)) {
                    $child->field_options->{"_field"} = $child;
                }
                $event->getViewProcessingFrag($child->field_name, $child->field_options ?? null);
            }
        }

        return $frag;
    }
}