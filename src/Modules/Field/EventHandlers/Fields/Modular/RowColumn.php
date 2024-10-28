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

class RowColumn implements HandlerInterface
{
    const FieldSlug = 'modular_rowcolumn';

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent (object $event): void
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
            },
            userForm: function ($data) use ($event) {
                return $this->userForm($event, $data);
            },
        );
    }

    /**
     * @throws \Exception|\Throwable
     */
    public function settingsForm (OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'RowColumn';
        $row = 1;
        $column = 1;
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $styles = (isset($data->styles)) ? helper()->htmlSpecChar($data->styles) : '';

        if (isset($data->row)) {
            $row = $data->row;
        }

        if (isset($data->column)) {
            $column = $data->column;
        }

        $gridTemplateCol = $data->grid_template_col ?? '';

        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';

        $useTab = $event->booleanOptionSelect($data->useTab ?? '0');
        $group = $event->booleanOptionSelect($data->group ?? '0');
        $toggleable = $event->booleanOptionSelectWithNull($data->toggleable ?? '');

        $more = <<<HTML
<div class="form-group d:flex flex-gap align-items:flex-end">

     <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="useTab-$changeID">Use Tabs
         <select name="useTab" class="default-selector mg-b-plus-1" id="useTab-$changeID">
               $useTab
          </select>
    </label>
    
     <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="group-$changeID">Group
        <select name="group" class="default-selector mg-b-plus-1" id="group-$changeID">
           $group
        </select>
    </label>
    
</div>

<div class="form-group d:flex flex-gap align-items:flex-end">

  <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="styles-$changeID">Styles
     <input id="styles-$changeID" name="styles" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
        value="$styles" placeholder="width:100px;height:100px;...">
    </label>
    
    <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="toggleable-$changeID">Toggable
        <select name="toggleable" class="default-selector mg-b-plus-1" id="toggleable-$changeID">
           $toggleable
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
     </label>
       <label class="menu-settings-handle-name" for="widget-column-$changeID">Grid Template Col
        <input id="widget-column-$changeID" name="grid_template_col" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray" data-name="grid_template_col" 
        value="$gridTemplateCol">
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
     * @throws \Throwable
     */
    public function userForm (OnFieldMetaBox $event, $data): string
    {
        $useTabs = isset($data->useTab) && $data->useTab === '1';
        $isGroup = isset($data->group) && $data->group === '1';
        $styles = (isset($data->styles)) ? $data->styles : '';
        $isToggleable = null;
        if (isset($data->toggleable) && $data->toggleable !== '') {
            $isToggleable = $data->toggleable === '1';
        }

        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'RowColumn';
        $row = 1;
        $column = 1;
        if (isset($data->row)) {
            $row = $data->row;
        }

        if (isset($data->column)) {
            $column = $data->column;
        }

        $tabKey = $data->_field->field_data['tabbed_key'] ?? '';

        $repeaters = [];
        $lastRepeater = null;

        if ($isGroup) {
            $frag = $event->_topHTMLWrapper($fieldName, $data, true, function ($isEditorWidgetSettings, $toggle) use ($tabKey, $data, $event) {
                $slug = $data->field_slug ?? '';
                $hash = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
                $inputName = (isset($data->inputName)) ? $data->inputName : '';
                $field_table_slug = (isset($data->_field->main_field_slug)) ? "<input type='hidden' name='main_field_slug' value='{$data->_field->main_field_slug}'>" : '';

                return <<<HTML
<li tabIndex="0" class="width:100% field-builder-items overflow:auto">
            <div $isEditorWidgetSettings role="form" data-widget-form="true" class="widgetSettings flex-d:column menu-widget-information cursor:pointer width:100% {$toggle['div']}">
<input type="hidden" name="field_slug" value="$slug">
$field_table_slug
<input type="hidden" name="field_slug_unique_hash" value="$hash">
<input type="hidden" name="field_input_name" value="$inputName">
<input type="hidden" name="tabbed_key" value="$tabKey">
HTML;
            });
        } else {
            $frag = $event->_topHTMLWrapper($fieldName, $data, true, toggleUserSettings: $isToggleable);
        }

        $cell = $row * $column;
        $fieldNameTabUnique = $fieldName . '_' . helper()->randString(10);
        // Having grid-template-columns: repeat(autofit, var(--column-width)); cancels out any row or col number
        // remove the comment to make that effect: This might improve the responsiveness

        # The Tabs Version:
        if ($useTabs) {
            $tabID = helper()->slug($fieldName, '_');
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
    background: unset;
    margin-left: unset;
    margin-right: unset;
}
.menu-arranger-li ul {
    list-style: none;
    margin-left: unset;
}
</style>
HTML;

            if (isset($data->_children) && !isset($data->_field->_children)) {
                if (!isset($data->_field)) {
                    $data->_field = new \stdClass();
                }
                $data->_field->_children = $data->_children;
            }

            $first = false;
            for ($i = 1; $i <= $cell; $i++) {
                if (!isset($data->_field->_children)) {
                    continue;
                }

                if (isset($data->_field->_children)) {

                    foreach ($data->_field->_children as $child) {

                        $fieldSlugUniqueHash = $child->field_options->field_slug_unique_hash;
                        $childCellNumber = (isset($child->field_options->{$child->field_name . "_cell"}))
                            ? (int)$child->field_options->{$child->field_name . "_cell"}
                            : $i;

                        if ($childCellNumber === $i) {

                            if (isset($child->field_options)) {
                                $child->field_options->{"_field"} = $child;
                            }

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
                            $fieldUniqueHash = $fieldOptionName . '_' . $child->field_options->field_slug_unique_hash;
                            $fieldOptionNameID = helper()->slug($fieldOptionName, '_') . '_' . $fieldNameTabUnique . helper()->randString(10);


                            $buildTab = fn() => <<<TAB
<input tabindex="0" data-row-tabber data-field_input_name="$fieldInputName"  data-field_slug_unique_hash="{$child->field_options->field_slug_unique_hash}" data-unique="$fieldUniqueHash" type="radio" id="{$fieldOptionNameID}_field" name="$fieldNameTabUnique" $checked>
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

                        }
                    }

                    # End Consumption of Repeater
                    RowColumn::CloseRepeaterFrag($frag, $repeaters);
                }
            }
            $frag .= <<<HTML
</ul>
HTML;

        } else {

            $gridTemplateCol = '';
            if (isset($data->grid_template_col)) {
                $gridTemplateCol = " grid-template-columns: {$data->grid_template_col};";
            }

            if ($isGroup) {
                $frag .= <<<HTML
<div class="row-col-parent" data-depth="0" style="$styles">
    <ul style="margin-left: unset;--row:$row; --column:$column; $gridTemplateCol" class="cursor:pointer form-group d:grid flex-gap:small overflow-x:auto overflow-y:auto rowColumnItemContainer grid-template-rows grid-template-columns">
HTML;
            } else {
                $frag .= <<<HTML
<div class="row-col-parent" data-depth="0"  style="$styles">
    <div style="--row:$row; --column:$column; $gridTemplateCol" class="cursor:pointer form-group d:grid flex-gap:small overflow-x:auto overflow-y:auto rowColumnItemContainer grid-template-rows grid-template-columns">
HTML;
            }

            for ($i = 1; $i <= $cell; $i++) {
                if (!isset($data->_field->_children)) {
                    continue;
                }

                if (!$isGroup) {
                    $frag .= <<<HTML
<ul style="margin-left: 0; transform: unset; box-shadow: unset;" class="row-col-item-user owl">
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
                if (!$isGroup) {
                    $frag .= <<<HTML
</ul>
HTML;
                }
            }

            if ($isGroup) {
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

        if ($isGroup) {
            $frag .= $event->_bottomHTMLWrapper(function () {
                return "</div></li>";
            });
        } else {
            $frag .= $event->_bottomHTMLWrapper();
        }
        return $frag;
    }

    /**
     * @param $frag
     * @param $repeaters
     * @param bool $force
     *
     * @return void
     */
    public static function CloseRepeaterFrag (&$frag, &$repeaters, bool $force = false): void
    {
        $close = '</ul></li>';
        if ($force) {
            $frag .= $close;
            return;
        }

        if (count($repeaters) > 0) {
            $repeaters = [];
            $frag .= $close;
        }
    }
}