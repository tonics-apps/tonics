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
    const FieldSlug = 'modular_rowcolumnrepeater';

    private array $repeaterButton = [];
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
            },
            userForm: function ($data) use ($event) {
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

        $disallowRepeat = (isset($data->disallowRepeat)) ? $data->disallowRepeat : '0';

        if ($disallowRepeat === '1') {
            $disallowRepeatFrag = <<<HTML
<option value="0">False</option>
<option value="1" selected>True</option>
HTML;
        } else {
            $disallowRepeatFrag = <<<HTML
<option value="0" selected>False</option>
<option value="1">True</option>
HTML;
        }

        $more = <<<HTML
<div class="form-group">
     <label class="menu-settings-handle-name" for="disallowRepeat-$changeID">Disallow Repeat
     <select name="disallowRepeat" class="default-selector mg-b-plus-1" id="disallowRepeat-$changeID">
        $disallowRepeatFrag
     </select>
    </label>
</div>

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
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        // $this->repeatersButton($event, $data);
        $this->isRoot = false;
        return $this->handleUserFormFrag($event, $data);
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

        $row = 1;
        $column = 1;
        if (isset($data->row)) {
            $row = $data->row;
        }

        if (isset($data->column)) {
            $column = $data->column;
        }

        if (!isset($data->depth) || (!isset($data->_field->depth))){
            $depth = 0;
        } else {
            $depth = $data->_field->depth ?? $data->depth;
        }

        $root = 'false';
        if ($this->isRoot === false){
            $root = 'true';
            $this->isRoot = true;
        }

        if ($depth == '0'){
            $root = 'true';
        }

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
            if (isset($data->_children)){
                $children = $data->_children;
            } elseif (isset($data->_field->_children)){
                $children = $data->_field->_children;
            } else {
                continue;
            }
            $frag .= <<<HTML
<ul style="margin-left: 0; transform: unset; box-shadow: unset;" data-cell_position="$i" class="row-col-item-user margin-top:0 owl">
HTML;

            foreach ($children as $child) {
                $fieldSlug = '';
                if (isset($child->field_name)){
                    $fieldSlug = $child->field_name;
                } elseif (isset($child->field_options->field_slug)){
                    $fieldSlug = $child->field_options->field_slug;
                } elseif (isset($child->field_slug)){
                    $fieldSlug = $child->field_slug;
                }
                $childCellNumber = (isset($child->field_data['_cell_position'])) ? (int)$child->field_data['_cell_position'] : null;

                if (isset($child->_cell_position)){
                    $childCellNumber = (int)$child->_cell_position;
                }

                if ($childCellNumber === null){
                    $childCellNumber = (isset($child->field_options->{$fieldSlug . "_cell"}))
                        ? (int)$child->field_options->{$fieldSlug . "_cell"}
                        : $i;
                }

                if ($childCellNumber === $i) {
                    if (isset($child->field_options)) {
                        $child->field_options->{"_field"} = $child;
                    }
                    $interceptChildFrag = '';
                    if ($interceptChild) {
                        $interceptChildFrag = $interceptChild($child->field_options, $data);
                    }

                    if (isset($child->field_options)){
                        // meaning we are gonna skip checking user supplied data in global post data
                        // if we do not do this, then it means, the input would be same in repeatable fields.
                        // using double underscore to prefix this as this is a core thing
                        $child->field_options->__skip_global_post_data = true;
                    }

                    $frag .= (empty($interceptChildFrag)) ? $event->getUsersForm($fieldSlug, $child->field_options ?? null) : $interceptChildFrag;
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

        $disallowRepeat = (isset($data->disallowRepeat)) ? $data->disallowRepeat : '0';
        $repeaterButtonFrag = '';

        if ($disallowRepeat === '0'){
            $canHaveRepeaterButton = true;
            if ((isset($data->_field->field_data['_moreOptions']))){
                $moreOption = $data->_field->field_data['_moreOptions'];
                $canHaveRepeaterButton = $moreOption->_can_have_repeater_button;
            }

            if ($canHaveRepeaterButton){
                $repeaterButtonFrag = <<<HTML
<button type="button" class="margin-top:1em row-col-repeater-button width:200px text-align:center bg:transparent border:none 
color:black bg:white-one border-width:default border:black padding:default cursor:pointer">
  $repeat_button_text
  <template class="repeater-frag">
    $frag
  </template>
</button>
HTML;
            }

        }

        $frag .= $repeaterButtonFrag;
        if ($interceptBottom) {
            return $interceptBottom($data, $repeaterButtonFrag);
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