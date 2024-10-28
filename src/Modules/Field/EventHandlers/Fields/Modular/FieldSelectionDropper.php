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

use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\AbstractFieldHandler;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class FieldSelectionDropper extends AbstractFieldHandler
{
    const FieldSlug = 'modular_fieldselectiondropper';
    private array $fieldsCollation             = [];
    private array $fieldSelectionDropEventSlug = [];

    public function fieldBoxName (): string
    {
        return 'FieldSelectionDropper';
    }

    public function fieldBoxDescription (): string
    {
        return 'Add a Field';
    }

    public function fieldBoxCategory (): string
    {
        return static::CATEGORY_MODULAR;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function settingsForm (OnFieldMetaBox $event, $data = null): string
    {
        $field = $this->getField();
        $field->processData($event, [
            'fieldName' => $this->fieldBoxDescription(),
        ]);

        $fieldName = $field->getFieldName();
        $inputName = $field->getInputName();
        $fieldSlug = (isset($data->fieldSlug)) ? $data->fieldSlug : [];
        $expandField = (isset($data->expandField)) ? $data->expandField : '1';
        $expandField = $event->booleanOptionSelect($expandField);

        $frag = $field->getTopHTMLWrapper();
        $fields = null;
        db(onGetDB: function ($db) use (&$fields) {
            $table = Tables::getTable(Tables::FIELD);
            $fields = $db->run("SELECT * FROM $table");
        });

        $fieldFrag = '';
        $fieldSlug = array_combine($fieldSlug, $fieldSlug);
        $selectedFields = [];
        foreach ($fields as $field) {
            $uniqueSlug = "$field->field_slug";
            $fieldSelected = (isset($fieldSlug[$uniqueSlug])) ? 'selected' : '';
            if ($fieldSelected === 'selected') {
                $selectedFields[] = $field;
            }
            $fieldFrag .= <<<HTML
<option value="$uniqueSlug" $fieldSelected>$field->field_name</option>
HTML;
        }

        $selectedFieldFrag = '';
        foreach ($selectedFields as $field) {
            $uniqueSlug = "$field->field_slug";
            $selected = '';
            if ($uniqueSlug === $data?->defaultFieldSlug) {
                $selected = 'selected';
            }
            $selectedFieldFrag .= <<<HTML
<option value="$uniqueSlug" $selected>$field->field_name</option>
HTML;
        }

        $hookName = (isset($data->hookName)) ? $data->hookName : '';
        $group = $event->booleanOptionSelect($data->group ?? '0');
        $toggleable = $event->booleanOptionSelectWithNull($data->toggleable ?? '');
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';

        $moreSettings = $event->generateMoreSettingsFrag($data, <<<HTML
<div class="form-group d:flex flex-gap align-items:flex-end">
    <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="expandField-$changeID">Expand Field
        <select name="expandField" class="default-selector mg-b-plus-1" id="expandField-$changeID">
            $expandField
        </select>
    </label>
    
    <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="group-$changeID">Group
        <select name="group" class="default-selector mg-b-plus-1" id="group-$changeID">
            $group
        </select>
    </label>
</div>

<div class="form-group d:flex flex-gap align-items:flex-end">
    <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="toggleable-$changeID">Toggable
        <select name="toggleable" class="default-selector mg-b-plus-1" id="toggleable-$changeID">
            $toggleable
        </select>
    </label>
    <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="hookName-$changeID">Hook Name
        <input id="hookName-$changeID" name="hookName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
        value="$hookName" placeholder="Name of the fieldSelector hook">
    </label>
</div>
HTML,
        );

        $frag .= <<<FORM
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="field-settings-handle-name" for="fieldName-$changeID">Field Name
            <input id="fieldName-$changeID" name="fieldName" type="text" class="field-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
    <label class="field-settings-handle-name" for="inputName-$changeID">Input Name
            <input id="inputName-$changeID" name="inputName" type="text" class="field-name color:black border-width:default border:black placeholder-color:gray"
            value="$inputName" placeholder="(Optional) Input Name">
    </label>
</div>
<div class="form-group">
    <label class="field-settings-handle-name" for="fieldSlug-$changeID">Choose Field(s)
        <select name="fieldSlug" multiple class="default-selector mg-b-plus-1" id="fieldSlug-$changeID">
        $fieldFrag
        </select>
    </label>
</div>
<div class="form-group">
    <label class="field-settings-handle-name" for="defaultFieldSlug-$changeID">Choose Default Field Slug
           <select name="defaultFieldSlug" class="default-selector mg-b-plus-1" id="defaultFieldSlug-$changeID">
                <option label=" "></option>
                $selectedFieldFrag
            </select>
        </label>
</div>
$moreSettings
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;

    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function userForm (OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Field';
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $uniqueCollationKey = $data->_field->main_field_slug ?? '' . $changeID;
        $uniqueCollationKey = $uniqueCollationKey . '_' . $changeID;
        $keyValue = $event->getKeyValueInData($data, $data->inputName);
        $hookName = $data->hookName ?? '';

        if (!empty($hookName)) {

            if (!isset($this->fieldSelectionDropEventSlug[$hookName])) {
                $dropperEvent = FieldConfig::getFieldSelectionDropper();
                $fieldSlug = [...$dropperEvent->getFieldsByName($hookName), ...$data?->fieldSlug ?? []];
                $this->fieldSelectionDropEventSlug[$hookName] = $fieldSlug;
            } else {
                $fieldSlug = $this->fieldSelectionDropEventSlug[$hookName];
            }

        } else {
            $fieldSlug = array_combine($data?->fieldSlug ?? [], $data?->fieldSlug ?? []);
        }

        $fieldSlug = array_combine($fieldSlug, $fieldSlug);

        $expandField = (isset($data->expandField)) ? $data->expandField : '1';
        $defaultFieldSlug = (empty($keyValue)) ? $data?->defaultFieldSlug : $keyValue;

        $fieldSelectDropperFrag = '';
        if (isset($this->fieldsCollation[$uniqueCollationKey])) {
            $fields = $this->fieldsCollation[$uniqueCollationKey];
        } else {
            $fields = $this->getFields($fieldSlug);
            $this->fieldsCollation[$uniqueCollationKey] = $fields;
        }

        $fieldSelectionFrag = '';
        $defaultFieldSlugFrag = '';
        foreach ($fields as $field) {
            $uniqueSlug = "$field->field_slug";
            if (isset($fieldSlug[$field->field_slug])) {
                $fieldSelected = '';
                if ($uniqueSlug === $defaultFieldSlug) {
                    $data->selectedValue = $field->field_name;
                    $fieldSelected = 'selected';
                    $defaultFieldSlug = $field->field_slug;
                    if ($expandField === '1') {
                        $defaultFieldSlugFrag = $event->getFieldData()->generateFieldWithFieldSlug(
                            [$uniqueSlug],
                            getPostData(),
                        )->getHTMLFrag();
                    }
                }
                $fieldSelectionFrag .= <<<HTML
<option value="$uniqueSlug" $fieldSelected>$field->field_name</option>
HTML;
            }
        }

        if ($expandField === '1') {

            if (isset($data->_field->_children)) {
                helper()->garbageCollect(function () use ($defaultFieldSlug, $event, &$defaultFieldSlugFrag) {
                    $defaultFieldSlugFrag = FieldConfig::expandFieldWithChildrenFromMetaBox($event, $defaultFieldSlug);
                });

            }

            $fieldSelectDropperFrag = <<<FieldSelectionDropperFrag
<div style="margin: 0;" class="tonics-field-selection-dropper-container rowColumnItemContainer">

    <ul style="margin-left: 0; transform: unset; box-shadow: unset;" data-hook_name="$hookName" class="tonics-field-selection-dropper-ul row-col-item-user">
            $defaultFieldSlugFrag
     </ul>
         
</div>
FieldSelectionDropperFrag;
        }

        $isGroup = isset($data->group) && $data->group === '1';
        $isToggleable = null;
        if (isset($data->toggleable) && $data->toggleable !== '') {
            $isToggleable = $data->toggleable === '1';
        }

        if ($isGroup) {
            $frag = $event->_topHTMLWrapper($fieldName, $data, true, function ($isEditorWidgetSettings, $toggle) use ($hookName, $data, $event) {
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
<input type="hidden" name="hook_name" value="$hookName">
HTML;
            });
        } else {
            $frag = $event->_topHTMLWrapper($fieldName, $data, true, toggleUserSettings: $isToggleable);
        }
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$data->field_slug}_$changeID";

        $frag .= <<<HTML
<div class="form-group tonics-field-selection-dropper-form-group margin-top:0 owl">
     <label class="field-settings-handle-name owl" for="fieldSlug-$changeID">$fieldName
     <select style="width:50%" data-hook_name="$hookName" name="$inputName" class="default-selector-no-width mg-b-plus-1 tonics-field-selection-dropper-select" id="fieldSlug-$changeID">
        <option value="" label=" "></option>
        $fieldSelectionFrag
     </select>
    </label>
    $fieldSelectDropperFrag
</div>
HTML;

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
     * @param array $fieldSlug
     *
     * @return array
     * @throws \Exception
     */
    private function getFields (array $fieldSlug): array
    {
        if (empty($fieldSlug)) {
            return [];
        }

        $fields = [];
        $fieldSlug = array_values($fieldSlug);
        db(onGetDB: function (TonicsQuery $db) use ($fieldSlug, &$fields) {
            $fields = $db->Select('*')->From(Tables::getTable(Tables::FIELD))->WhereIn('field_slug', $fieldSlug)->FetchResult();
        });

        if (!empty($fields)) {
            // Create an associative array where the key is the field_slug and the value is the order
            $order = array_flip($fieldSlug);
            // Sort the fields array based on the order array
            usort($fields, function ($a, $b) use ($order) {
                return $order[$a->field_slug] <=> $order[$b->field_slug];
            });
        }

        return $fields;

    }
}