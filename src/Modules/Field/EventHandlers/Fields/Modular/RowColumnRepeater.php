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
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class RowColumnRepeater implements HandlerInterface
{
    private array $fieldHashes = [];
    private $fields = null;
    private string $frag = '';
    private array $fieldRepeaterLoopTimes = [];
    private string $mainRepeaterName = '';

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        $script = AppConfig::getModuleAsset('Core', '/js/views/field/native/script.js');
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox(
            'RowColumnRepeater',
            'A DataTable Repeater Field',
            'Modular',
            $script,
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'RowColumnRepeater';
        $row = 1;
        $column = 1;
        $inputName = (isset($data->inputName)) ? $data->inputName : '';

        if (isset($data->row)) {
            $row = $data->row;
        }

        if (isset($data->column)) {
            $column = $data->column;
        }

        $gridTemplateCol = $data->grid_template_col ?? '';
        $repeat_button_text = $data->repeat_button_text ?? 'Repeat Section';

        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';

        $more = <<<HTML
<div class="form-group">
     <label class="menu-settings-handle-name" for="group-$changeID">Repeat Button Text
     <input id="widget-name-$changeID" name="repeat_button_text" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$repeat_button_text" placeholder="Enter Repeat Button Text">
    </label>
</div>
HTML;

        $gridTemplateColFrag = '';
        if (isset($data->grid_template_col)) {
            $gridTemplateColFrag = " grid-template-columns: {$data->grid_template_col};";
        }

        $frag .= <<<HTML
<div class="row-col-parent owl" data-depth="0">
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="widget-name-$changeID">Field Name
            <input id="widget-name-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
 <label class="menu-settings-handle-name" for="inputName-$changeID">Input Name
        <input id="inputName-$changeID" name="inputName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
        value="$inputName" placeholder="Input Name">
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
    </label>
       <label class="menu-settings-handle-name" for="widget-column-$changeID">Grid Template Col
        <input id="widget-column-$changeID" name="grid_template_col" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray" data-name="grid_template_col" 
        value="$gridTemplateCol">
    </label>
</div>
{$event->generateMoreSettingsFrag($data, $more)}
    <div style="--row:$row; --column:$column; $gridTemplateColFrag" class="cursor:pointer form-group d:grid flex-gap:small overflow-x:auto overflow-y:auto rowColumnItemContainer grid-template-rows grid-template-columns">
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

        $parentTest = [];
        $inputData = (isset(getPostData()[$data->inputName])) ? getPostData()[$data->inputName] : '';
        $inputData = json_decode($inputData);
        $frag = '';
        // return $this->handleUserFormFrag($event, $data);
        if (isset($inputData->treeTimes)) {
           // dd($inputData->treeTimes);
            foreach ($inputData->treeTimes as $key => $fields) {
                $frag .= $this->handleUserFormFrag($event, $data, function ($child, $parent) use ($data, $event, $key, $inputData) {
                    return $this->handleChild($child, $parent, $event, $key, $inputData);
                });
            }
        } else {
            $frag = $this->handleUserFormFrag($event, $data);
        }


        return $frag;
    }

    /**
     * @param $child
     * @param $parent
     * @param $event
     * @param $key
     * @param $inputData
     * @return string
     * @throws \Exception
     */
    private function handleChild($child, $parent, $event, $key, $inputData): string
    {
        $frag2 = '';
        if ($child->field_slug === 'modular_rowcolumnrepeater'){
            $childFields = $inputData->treeTimes->{$key}->{$child->fieldName}->data;
            foreach ($childFields as $childField){
                $frag2 .= $this->handleUserFormFrag($event, $child, function ($child, $parent) use ($event, $key, $inputData) {
                    return $this->handleChild($child, $parent, $event, $key, $inputData);
                });
            }
        } else {
            $fieldName = $parent->fieldName;
            $hashes = $inputData->treeTimes->{$key}->{$fieldName}->hash->{$child->field_slug_unique_hash};
            $nextKey = array_key_first($hashes);
            $hashData = $hashes[$nextKey];
            unset($inputData->treeTimes->{$key}->{$fieldName}->hash->{$child->field_slug_unique_hash}[$nextKey]); // remove for next key
            addToGlobalVariable('Data', (array)$hashData);
        }
        return $frag2;
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @param null $fields
     * @return string
     * @throws \Exception
     */
    private function handleUserFormFrag(OnFieldMetaBox $event, $data, callable $interceptChild = null): string
    {

        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'DataTable_Repeater';

        $frag = '';
        $row = 1;
        $column = 1;
        if (isset($data->row)) {
            $row = $data->row;
        }

        if (isset($data->column)) {
            $column = $data->column;
        }

        $depth = $data->_field->depth;
        $frag .= $event->_topHTMLWrapper($fieldName, $data, true);

        $cell = $row * $column;
        $gridTemplateCol = '';
        if (isset($data->grid_template_col)) {
            $gridTemplateCol = " grid-template-columns: {$data->grid_template_col};";
        }

        $repeat_button_text = $data->repeat_button_text ?? 'Repeat Section';


        $inputName = $data->inputName ?? '';
        $mainFrag = <<<HTML
<style>
.remove-row-col-repeater-button:hover + .rowColumnItemContainer {
    background: #c2dbffa3;
}
</style>
<div class="row-col-parent repeater-field position:relative cursor:move owl draggable draggable-repeater" data-repeater_repeat_button_text="$repeat_button_text" data-repeater_field_name="$fieldName" data-repeater_depth="$depth" data-repeater_input_name="$inputName">
    <button type="button" class="position:absolute height:2em d:flex align-items:center right:0 remove-row-col-repeater-button text-align:center bg:transparent border:none 
        color:black bg:white-one border-width:default border:black padding:small cursor:pointer"><span>Delete</span></button>
    <div style="border: 2px dashed #000; padding: 1em;--row:$row; --column:$column; $gridTemplateCol" class="cursor:pointer form-group d:grid cursor:move owl flex-gap:small overflow-x:auto overflow-y:auto rowColumnItemContainer grid-template-rows grid-template-columns">
HTML;

        for ($i = 1; $i <= $cell; $i++) {
            if (!isset($data->_field->_children)) {
                continue;
            }

            $mainFrag .= <<<HTML
<ul style="margin-left: 0; transform: unset; box-shadow: unset;" data-cell_position="$i" class="row-col-item-user owl">
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
                        $interceptChildFrag = '';
                        if ($interceptChild){
                            $interceptChildFrag = $interceptChild($child->field_options, $data);
                        }
                        $mainFrag .= (empty($interceptChildFrag)) ? $event->getUsersForm($child->field_name, $child->field_options ?? null) : $interceptChildFrag;
                    }
                }
            }
            $mainFrag .= <<<HTML
</ul>
HTML;
        }


        $mainFrag .= <<<HTML
    </div>
</div>
<button type="button" class="margin-top:1em row-col-repeater-button width:200px text-align:center bg:transparent border:none 
color:black bg:white-one border-width:default border:black padding:default cursor:pointer">
  $repeat_button_text
  <template class="repeater-frag">
    $mainFrag
  </template>
</button>
HTML;


        $frag .= $mainFrag . $event->_bottomHTMLWrapper();

        return $frag;
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function buildRepeaterFieldLoopTimes($inputData): void
    {
        $inputData = json_decode($inputData)->_data;
        foreach ($inputData as $fields) {
            dd($inputData, $fields);
        }

        foreach (FieldConfig::getFieldUnSortedItemsDataID() as $fields) {
            foreach ($fields as $field) {
                $this->fieldHashes[$field->field_options->field_slug_unique_hash] = $field;
            }
        }
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @param $inputData
     * @return string
     * @throws \Exception
     */
    private function getNestedFields(OnFieldMetaBox $event, $data, $inputData): string
    {
        $frag = '';
        $inputData = json_decode($inputData);
        if (!isset($inputData->_data)) {
            return $frag;
        } else {
            $inputData = $inputData->_data;
        }

        dd($inputData);

        foreach ($inputData as $fields) {
            $frag .= $this->getNestedFieldsFrag($event, $fields);
            return $frag;
        }

        return $frag;

    }

    /**
     * @param OnFieldMetaBox $event
     * @param $fields
     * @return string
     * @throws \Exception
     */
    private function getNestedFieldsFrag(OnFieldMetaBox $event, $fields): string
    {
        $fieldName = (isset($fields->_configuration->_field_name)) ? $fields->_configuration->_field_name : 'DataTable_Repeater';
        $inputName = $fields->_configuration->_name ?? '';
        $depth = $fields->_configuration->_depth ?? '0';

        $row = 1;
        $column = 1;
        if (isset($data->row)) {
            $row = $data->row;
        }

        if (isset($data->column)) {
            $column = $data->column;
        }

        $gridTemplateCol = '';
        if (isset($data->grid_template_col)) {
            $gridTemplateCol = " grid-template-columns: {$data->grid_template_col};";
        }

        $repeat_button_text = $fields->_configuration->_repeat_button_text ?? 'Repeat Section';
        $mainFieldHashData = $this->fieldHashes[$fields->_configuration->_field_slug_unique_hash]->field_options;
        $frag = $event->_topHTMLWrapper($fieldName, $mainFieldHashData, true);

        $mainFrag = <<<HTML
<style>
.remove-row-col-repeater-button:hover + .rowColumnItemContainer {
    background: #c2dbffa3;
}
</style>
<div class="row-col-parent repeater-field position:relative cursor:move owl draggable draggable-repeater" data-repeater_repeat_button_text="$repeat_button_text" data-repeater_field_name="$fieldName" data-repeater_depth="$depth" data-repeater_input_name="$inputName">
    <button type="button" class="position:absolute height:2em d:flex align-items:center right:0 remove-row-col-repeater-button text-align:center bg:transparent border:none 
        color:black bg:white-one border-width:default border:black padding:small cursor:pointer"><span>Delete</span></button>
    <div style="border: 2px dashed #000; padding: 1em;--row:$row; --column:$column; $gridTemplateCol" class="cursor:pointer form-group d:grid cursor:move owl flex-gap:small overflow-x:auto overflow-y:auto rowColumnItemContainer grid-template-rows grid-template-columns">
HTML;

        $cell = $row * $column;

        foreach ($fields as $key => $data) {
            if ((!is_numeric($key) && $key !== '_children') || (isset($data->field_slug) && $data->field_slug === 'modular_rowcolumnrepeater')) {
                continue;
            }

            $mainFrag .= <<<HTML
<ul style="margin-left: 0; transform: unset; box-shadow: unset;" class="row-col-item-user owl">
HTML;

            if ($key === '_children') {
                foreach ($fields->_children as $dataChild) {
                    $mainFrag .= $this->getNestedFieldsFrag($event, $dataChild);
                }
            } else {
                $fieldHash = $data->field_slug_unique_hash;
                $fieldOption = $this->fieldHashes[$fieldHash]->field_options;
                addToGlobalVariable('Data', (array)$data);
                $mainFrag .= $event->getUsersForm($data->field_slug, $fieldOption);
            }

            $mainFrag .= <<<HTML
</ul>
HTML;
        }


        $mainFrag .= <<<HTML
    </div>
</div>
<button type="button" class="margin-top:1em row-col-repeater-button width:200px text-align:center bg:transparent border:none 
color:black bg:white-one border-width:default border:black padding:default cursor:pointer">
  $repeat_button_text
  <template class="repeater-frag">
    $mainFrag
  </template>
</button>
HTML;
        $frag .= $mainFrag . $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function viewData(OnFieldMetaBox $event, $data): string
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