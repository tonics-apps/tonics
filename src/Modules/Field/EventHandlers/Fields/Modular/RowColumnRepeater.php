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

namespace App\Modules\Field\EventHandlers\Fields\Modular;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class RowColumnRepeater implements HandlerInterface
{
    const FieldSlug = 'modular_rowcolumnrepeater';

    private array $repeaterButton = [];
    private bool  $isRoot         = false;

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent (object $event): void
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
        );
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function settingsForm (OnFieldMetaBox $event, $data = null): string
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

        $useTab = $event->booleanOptionSelect($data->useTab ?? '0');
        $toggleable = $event->booleanOptionSelectWithNull($data->toggleable ?? '');
        $more = <<<HTML
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="disallowRepeat-$changeID">Disallow Repeat
         <select name="disallowRepeat" class="default-selector mg-b-plus-1" id="disallowRepeat-$changeID">$disallowRepeatFrag</select>
    </label>
    
    <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="useTab-$changeID">Use Tabs
        <select name="useTab" class="default-selector mg-b-plus-1" id="useTab-$changeID">
            $useTab
        </select>
    </label>
    
    <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="toggleable-$changeID">Toggable
        <select name="toggleable" class="default-selector mg-b-plus-1" id="toggleable-$changeID">
           $toggleable
        </select>
    </label>
    
    <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="group-$changeID">Repeat Button Text
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
     *
     * @return string
     * @throws \Exception
     * @throws \Throwable
     */
    private function repeatersButton (OnFieldMetaBox $event, $data): string
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
     * @throws \Throwable
     */
    public function userForm (OnFieldMetaBox $event, $data): string
    {
        // $this->repeatersButton($event, $data);
        $this->isRoot = false;
        return $this->handleUserFormFrag($event, $data);
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     *
     * @return string
     * @throws \Exception|\Throwable
     */
    private function getTopWrapper (OnFieldMetaBox $event, $data): string
    {
        $useTabs = isset($data->useTab) && $data->useTab === '1';
        $fieldName = $data->field_name ?? '';

        if (empty($fieldName)) {
            $fieldName = $data->fieldName ?? 'DataTable_Repeater';
        }

        $row = 1;
        $column = 1;
        if (isset($data->row)) {
            $row = $data->row;
        }

        if (isset($data->column)) {
            $column = $data->column;
        }

        if (!isset($data->depth) || (!isset($data->_field->depth))) {
            $depth = 0;
        } else {
            $depth = $data->_field->depth ?? $data->depth;
        }

        $root = 'false';
        if ($this->isRoot === false) {
            $root = 'true';
            $this->isRoot = true;
        }

        if ($depth == '0') {
            $root = 'true';
        }

        $isToggleable = null;
        if (isset($data->toggleable) && $data->toggleable !== '') {
            $isToggleable = $data->toggleable === '1';
        }

        $firstChildDefault = $data->_firstChild->field_options->selectedValue ?? '';
        $fieldNameDefault = $fieldName;
        if (!empty($firstChildDefault)) {
            $fieldName = "$fieldName ($firstChildDefault)";
        }

        $frag = $event->_topHTMLWrapper($fieldName, $data, true, toggleUserSettings: $isToggleable);

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
        $padding = 'padding:1em;';
        if ($useTabs) {
            $padding = '';
        }

        $styles = "style='border: 1px dashed #000; $padding --row:$row; --column:$column; $gridTemplateCol'";
        $simpleRepeaterStyle = "style='--row:$row; --column:$column; $gridTemplateCol'";
        $classRepeater = "class='cursor:pointer form-group d:grid cursor:move flex-gap:small overflow-x:auto overflow-y:auto rowColumnItemContainer grid-template-rows grid-template-columns'";
        $containerRepeaterStyle = "$styles $classRepeater";

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
data-repeater_field_name="$fieldNameDefault" 
data-repeater_depth="$depth"
data-repeater_input_name="$inputName">
    <button type="button" class="position:absolute height:2em d:flex align-items:center right:0 remove-row-col-repeater-button text-align:center bg:transparent border:none 
        color:black bg:white-one border-width:default border:black padding:small cursor:pointer">Delete</button>
    <div data-contain-repeater $containerRepeaterStyle>
        <form $simpleRepeaterStyle $classRepeater>
HTML;

        return $frag;
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @param callable|null $interceptChild
     * @param callable|null $interceptBottom
     *
     * @return string
     * @throws \Exception
     * @throws \Throwable
     */
    private function handleUserFormFrag (OnFieldMetaBox $event, $data, callable $interceptChild = null, callable $interceptBottom = null): string
    {
        $useTabs = isset($data->useTab) && $data->useTab === '1';
        $row = $data->row ?? 1;
        $column = $data->column ?? 1;
        $cell = $row * $column;
        $repeatButtonText = $data->repeat_button_text ?? 'Repeat Section';
        $frag = '';
        $firstChild = null;

        $repeaters = [];
        $lastRepeater = null;

        $fieldNameTabUnique = $data->fieldName . '_' . helper()->randString(10);

        if ($useTabs) {
            $tabID = helper()->slug($data->fieldName, '_');
            $tabKey = $data->_field->field_data['tabbed_key'] ?? '';

            $frag .= <<<HTML
<ul id="$tabID" class="tabs tonicsFieldTabsContainer color:black bg:white-one border-width:tiny border:black rowColumnItemContainer">
<style>
.tonicsFieldTabsContainer {
    font-size: unset; 
    max-width: unset; 
    min-width: unset; 
    width: unset; 
}
.tabs.tonicsFieldTabsContainer {
    margin-left: unset;
    margin-right: unset;
}
.menu-arranger-li ul {
    list-style: none;
    margin-left: unset;
}
</style>
<input type="hidden" name="tabbed_key" value="$tabKey">
HTML;
        }

        $first = false;

        for ($i = 1; $i <= $cell; $i++) {
            $children = $data->_children ?? ($data->_field->_children ?? []);
            if (empty($children)) continue;

            if (!$useTabs) {
                $frag .= <<<HTML
<ul style="margin-left: 0; transform: unset; box-shadow: unset;" data-cell_position="$i" class="row-col-item-user margin-top:0 owl">
HTML;
            }

            foreach ($children as $child) {
                $fieldSlug = $child->field_name ?? $child->field_options->field_slug ?? $child->field_slug ?? '';
                $childCellNumber = isset($child->field_data['_cell_position']) ? (int)$child->field_data['_cell_position'] : null;
                $childCellNumber = $childCellNumber ?? (isset($child->field_options->{$fieldSlug . "_cell"}) ? (int)$child->field_options->{$fieldSlug . "_cell"} : $i);

                if (isset($child->field_data)) {
                    $child->field_options->{"field_data"} = $child->field_data;
                }

                if ($childCellNumber === $i) {
                    $child->field_options->_field = $child;
                    $fieldSlugUniqueHash = $child->field_options->field_slug_unique_hash;
                    $interceptChildFrag = $interceptChild ? $interceptChild($child->field_options, $data) : '';

                    if (isset($child->field_options)) {
                        $child->field_options->__skip_global_post_data = true;
                    }

                    if ($useTabs) {

                        $checked = '';
                        if (isset($data->_field->field_data['tabbed_key']) && !empty($data->_field->field_data['tabbed_key'])) {
                            if ($data->_field->field_data['tabbed_key'] === $fieldSlugUniqueHash) {
                                $checked = 'checked';
                            }
                        } else {
                            if (!$first) {
                                $first = true;
                                $checked = 'checked';
                            }
                        }

                        $fieldInputName = $child->field_options->field_input_name ?? '';
                        $fieldOptionName = $child->field_options->fieldName;
                        $fieldUniqueHash = $fieldOptionName . '_' . $fieldSlugUniqueHash;
                        $fieldOptionNameID = helper()->slug($fieldOptionName, '_') . '_' . $fieldNameTabUnique . helper()->randString(10);

                        $buildTab = fn() => <<<TAB
<input tabindex="0" data-row-tabber data-field_input_name="$fieldInputName" data-field_slug_unique_hash="{$child->field_options->field_slug_unique_hash}" data-unique="$fieldUniqueHash" type="radio" id="{$fieldOptionNameID}_field" name="$fieldNameTabUnique" $checked>
<label tabindex="0" data-unique="$fieldUniqueHash" for="{$fieldOptionNameID}_field">$fieldOptionName</label>
TAB;

                        $childFieldName = $child->field_name ?? $child->_field->field_name ?? '';

                        if ($childFieldName === RowColumnRepeater::FieldSlug) {
                            # OpenStart of Repeater Field
                            if (count($repeaters) === 0) {
                                $frag .= $buildTab();
                                $frag .= "<li><ul>";
                                $lastRepeater = $fieldOptionName;
                            }

                            if (count($repeaters) > 0 && $lastRepeater !== $fieldOptionName) {
                                RowColumn::CloseRepeaterFrag($frag, $repeaters, true);
                                $frag .= $buildTab();
                                $frag .= "<li><ul>";
                                $lastRepeater = $fieldOptionName;
                            }
                            $repeaters[] = $fieldOptionName;

                        } else {
                            RowColumn::CloseRepeaterFrag($frag, $repeaters);
                            $frag .= $buildTab();
                        }
                        $frag .= $event->getUsersForm($childFieldName, $child->field_options ?? null);

                    } else {
                        $frag .= (empty($interceptChildFrag)) ? $event->getUsersForm($fieldSlug, $child->field_options ?? null) : $interceptChildFrag;
                    }

                    if ($firstChild === null) {
                        $firstChild = $child;
                        $data->_firstChild = $firstChild;
                    }
                }
            }

            # End Consumption of Repeater
            RowColumn::CloseRepeaterFrag($frag, $repeaters);

            if (!$useTabs) {
                $frag .= '</ul>';
            }
        }

        if ($useTabs) {
            $frag .= '</ul>';
        }

        $frag .= <<<HTML
        </form>
    </div>
</div>
HTML;
        $topWrapper = $this->getTopWrapper($event, $data);
        $frag .= $event->_bottomHTMLWrapper();
        $disallowRepeat = $data->disallowRepeat ?? '0';
        $repeaterButtonFrag = '';
        $repeaterFrag = $topWrapper . $frag;

        if ($disallowRepeat === '0') {
            $canHaveRepeaterButton = true;

            if (isset($data->_field->field_data['_moreOptions'])) {
                $moreOption = $data->_field->field_data['_moreOptions'];
                $canHaveRepeaterButton = $moreOption->_can_have_repeater_button;
            }

            if ($canHaveRepeaterButton) {

                $slug = null;
                if (isset($data->_field->main_field_slug)) {
                    $slug = $data->_field->main_field_slug;
                }

                $repeaterButtonFrag = <<<HTML
<button type="button" class="margin-top:1em row-col-repeater-button width:200px text-align:center bg:transparent border:none 
color:black bg:white-one border-width:default border:black padding:default cursor:pointer" data-slug="{$slug}">
  $repeatButtonText
  <template class="repeater-frag">
    $repeaterFrag
  </template>
</button>
HTML;
            }
        }

        $frag = $topWrapper . $frag . $repeaterButtonFrag;
        if ($interceptBottom) {
            return $interceptBottom($data, $repeaterButtonFrag);
        }

        return $frag;
    }
}