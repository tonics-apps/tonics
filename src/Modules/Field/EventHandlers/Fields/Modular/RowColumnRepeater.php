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
    private array $repeaters = [];
    private array $nonRepeaters = [];

    private array $oldPostData = [];
    private array $repeaterButton = [];
    private array $childStacks = [];

    private array $fieldsSorted = [];

    private bool $isRoot = false;

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
     * @param $data
     * @return void
     */
    private function unnestRepeater($data): void
    {
        if ($data->field_slug === 'modular_rowcolumnrepeater') {
            $this->repeaters[$data->field_slug_unique_hash] = $data;
            if (isset($data->_field->_children)) {
                foreach ($data->_field->_children as $child) {
                    $this->unnestRepeater($child->field_options);
                }
            }
        } else {
            $this->nonRepeaters[$data->field_slug_unique_hash] = $data;
        }
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @return string
     * @throws \Exception
     */
    private function repeatersButton(OnFieldMetaBox $event, $data): string
    {
        return $this->handleUserFormFrag($event, $data,
            function ($field) use ($event) {
                $frag = '';
                if ($field->field_slug === 'modular_rowcolumnrepeater') {
                    $frag = $this->repeatersButton($event, $field);
                }
                return $frag;
            },
            function ($field, $repeatButtonFrag) {
                $this->repeaterButton[$field->field_slug_unique_hash] = $repeatButtonFrag;
                return $repeatButtonFrag;
            });
    }

    /**
     * @param $item
     * @return void
     * @throws \Exception
     */
    private function walkTreeAndDoTheDo($item): void
    {
        $this->fieldsSorted[] = $item;
        if (isset($item->_children)) {
            $item->_children = $this->sortWalkerTreeChildren($item);
            $children = $item->_children;
            // unset($item->_children);
            foreach ($children as $child) {
                $slug = $child->field_slug ?? $child->field_options->field_slug;
                if ($slug === 'modular_rowcolumnrepeater') {
                    $this->walkTreeAndDoTheDo($child);
                } else {
                    $this->fieldsSorted[] = $child;
                }
            }
        }

        $fieldSlugHash = $item->field_slug_unique_hash ?? $item->field_options->field_slug_unique_hash;
        $this->childStacks[$fieldSlugHash] = $item;
    }

    /**
     * @param $item
     * @return array
     */
    private function sortWalkerTreeChildren($item): array
    {
        $sorted = [];
        $fieldSlugHash = $item->field_slug_unique_hash ?? $item->field_options->field_slug_unique_hash;
        if (isset($this->repeaters[$fieldSlugHash])) {
            $originalFields = $this->repeaters[$fieldSlugHash]->_field->_children;
            $treeFields = $item->_children;
            foreach ($originalFields as $originalField) {
                $originalFieldSlugHash = $originalField->field_options->field_slug_unique_hash;
                $match = false;
                foreach ($treeFields as $treeKey => $treeField) {
                    $treeFieldSlugHash = $treeField->field_slug_unique_hash ?? $treeField->field_options->field_slug_unique_hash;

                    if ($originalFieldSlugHash === $treeFieldSlugHash) {
                        $sorted[] = $treeField;
                        unset($treeFields->{$treeKey});
                        $match = true;
                    }
                }

                // TODO
                // if you have exhaust looping, and you couldn't match anything, then it means
                // the originalFields has a new field push it in the sorted
                // for now, we won't do anything... (SOLVED)
                if (!$match) {
                    $slug = $originalField->field_options->field_slug;
                    $cellName = $originalField->field_options->field_slug . '_cell';
                    $cellPosition = $originalField->field_options->{$cellName};
                    $originalField->field_options->_cell_position = $cellPosition;
                    if ($slug === 'modular_rowcolumnrepeater'){
                        $originalField->field_options->_can_have_repeater_button = true;
                        $originalField->field_options->_children = $originalField->_children;
                    }
                    $sorted[] = $originalField->field_options;
                }
            }

        }

        return $sorted;
    }

    /**
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        // dd($data);
        $inputData = (isset(getPostData()[$data->inputName])) ? getPostData()[$data->inputName] : '';
        $inputData = json_decode($inputData);

        $this->oldPostData = getPostData();
        addToGlobalVariable('Data', []);

        if (isset($inputData->tree)) {
            $this->unnestRepeater($data);
            $this->repeatersButton($event, $data);
            foreach ($inputData->tree->_data as $tree_data) {
                $this->walkTreeAndDoTheDo($tree_data);
            }
            $frag = '';
            foreach ($inputData->tree->_data as $modularRepeaterData) {
                $frag .= $this->handleRepeaterUserFormFrag($event, $modularRepeaterData);
            }
        } else {
            $frag = $this->handleUserFormFrag($event, $data);
        }
        // restore old post
        addToGlobalVariable('Data', $this->oldPostData);
        return $frag;
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @return string
     * @throws \Exception
     */
    private function getTopWrapper(OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->field_name)) ? $data->field_name : '';
        if (empty($fieldName)) {
            $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'DataTable_Repeater';
        }

        $root = 'false';
        if (!$this->isRoot){
            $root = 'true';
            $this->isRoot = true;
        }

        $row = 1;
        $column = 1;
        if (isset($data->row)) {
            $row = $data->row;
        }

        if (isset($data->column)) {
            $column = $data->column;
        }

        $depth = $data->_field->depth ?? $data->depth;

        $frag = $event->_topHTMLWrapper($fieldName, $data, true);

        $gridTemplateCol = '';
        # from js tree input
        if (isset($data->field_name)) {
            $gridTemplateCol = $data->grid_template_col ?? '';
        } else {
            if (isset($data->grid_template_col)) {
                $gridTemplateCol = " grid-template-columns: {$data->grid_template_col};";
            }
        }

        $repeat_button_text = $data->repeat_button_text ?? 'Repeat Section';

        $inputName = $data->inputName ?? '';
        $frag .= <<<HTML
<style>
.remove-row-col-repeater-button:hover + .rowColumnItemContainer {
    background: #c2dbffa3;
}
</style>
<div class="row-col-parent repeater-field position:relative" 
data-row="$row" 
data-col="$column" 
data-is_repeater_root="$root" 
data-grid_template_col="$gridTemplateCol" 
data-repeater_repeat_button_text="$repeat_button_text" 
data-repeater_field_name="$fieldName" 
data-repeater_depth="$depth" 
data-repeater_input_name="$inputName">
    <button type="button" class="position:absolute height:2em d:flex align-items:center right:0 remove-row-col-repeater-button text-align:center bg:transparent border:none 
        color:black bg:white-one border-width:default border:black padding:small cursor:pointer"><span>Delete</span></button>
    <div style="border: 2px dashed #000; padding: 1em;--row:$row; --column:$column; $gridTemplateCol" class="cursor:pointer form-group d:grid cursor:move owl flex-gap:small overflow-x:auto overflow-y:auto rowColumnItemContainer grid-template-rows grid-template-columns">
HTML;

        return $frag;
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @param callable|null $interceptChild
     * @param callable|null $interceptBottom
     * @return string
     * @throws \Exception
     */
    private function handleUserFormFrag(OnFieldMetaBox $event, $data, callable $interceptChild = null, callable $interceptBottom = null): string
    {
        $row = 1;
        $column = 1;
        if (isset($data->row)) {
            $row = $data->row;
        }

        if (isset($data->column)) {
            $column = $data->column;
        }

        $cell = $row * $column;

        $repeat_button_text = $data->repeat_button_text ?? 'Repeat Section';

        $frag = $this->getTopWrapper($event, $data);

        for ($i = 1; $i <= $cell; $i++) {
            if (!isset($data->_field->_children)) {
                continue;
            }

            $frag .= <<<HTML
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
                        if ($interceptChild) {
                            $interceptChildFrag = $interceptChild($child->field_options, $data);
                        }
                        $frag .= (empty($interceptChildFrag)) ? $event->getUsersForm($child->field_name, $child->field_options ?? null) : $interceptChildFrag;
                    }
                }
            }

            $frag .= <<<HTML
        </ul>
HTML;
        }


        $frag .= <<<HTML
    </div>
</div>
HTML;
        $frag .= $event->_bottomHTMLWrapper();

        $repeaterButtonFrag = <<<HTML
<button type="button" class="margin-top:1em row-col-repeater-button width:200px text-align:center bg:transparent border:none 
color:black bg:white-one border-width:default border:black padding:default cursor:pointer">
  $repeat_button_text
  <template class="repeater-frag">
    $frag
  </template>
</button>
HTML;

        $frag .= $repeaterButtonFrag;

        if ($interceptBottom) {
            return $interceptBottom($data, $repeaterButtonFrag);
        }

        return $frag;
    }


    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @return string
     * @throws \Exception
     */
    private function handleRepeaterUserFormFrag(OnFieldMetaBox $event, $data): string
    {
        $row = 1;
        $column = 1;
        if (isset($data->row)) {
            $row = $data->row;
        }

        if (isset($data->column)) {
            $column = $data->column;
        }

        $cell = $row * $column;

        $frag = $this->getTopWrapper($event, $data);
        for ($i = 1; $i <= $cell; $i++) {
            if (isset($data->_children)) {
                $frag .= <<<HTML
<ul style="margin-left: 0; transform: unset; box-shadow: unset;" data-cell_position="$i" class="row-col-item-user owl">
HTML;
                foreach ($data->_children as $child) {
                    if (!isset($child->_cell_position)){
                        $slugCell = $child->field_options->field_slug . '_cell';
                        $childCellNumber = (int)$child->field_options->{$slugCell};
                    } else {
                        $childCellNumber = (int)$child->_cell_position;
                    }

                    $fieldSlugHash = $child->field_slug_unique_hash ?? $child->field_options->field_slug_unique_hash;
                    $childField = null;
                    if (key_exists($fieldSlugHash, $this->repeaters) || key_exists($fieldSlugHash, $this->nonRepeaters)) {
                        $childField = (isset($this->repeaters[$fieldSlugHash])) ? $this->repeaters[$fieldSlugHash] : $this->nonRepeaters[$fieldSlugHash];
                    }

                    if ($childCellNumber === $i) {
                        if ($childField->field_slug === 'modular_rowcolumnrepeater'){
                            $frag .= $this->handleRepeaterUserFormFrag($event, $child);
                        } else {
                            addToGlobalVariable('Data', (array)$child);
                            $frag .= $event->getUsersForm($childField->field_slug, $childField ?? null);
                        }
                    }
                }
                $frag .= <<<HTML
        </ul>
HTML;
            }
        }

        $frag .= <<<HTML
    </div>
</div>
HTML;
        $frag .= $event->_bottomHTMLWrapper();

        $slugHash = $data->field_slug_unique_hash ?? $data->field_options->field_slug_unique_hash;
        if (isset($data->_can_have_repeater_button) && $data->_can_have_repeater_button){
            if (isset($this->repeaterButton[$slugHash])){
                $frag .= $this->repeaterButton[$slugHash];
            }
        } else {
            $frag .= $this->repeaterButton[$slugHash];
        }

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