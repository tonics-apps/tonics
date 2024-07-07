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

class FieldSelectionDropper extends AbstractFieldHandler
{
    const FieldSlug = 'modular_fieldselectiondropper';

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

        if ($expandField === '1') {
            $expandField = <<<HTML
<option value="0">False</option>
<option value="1" selected>True</option>
HTML;
        } else {
            $expandField = <<<HTML
<option value="0" selected>False</option>
<option value="1">True</option>
HTML;
        }

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

        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';
        $moreSettings = $event->generateMoreSettingsFrag($data, <<<HTML
<div class="form-group">
     <label class="field-settings-handle-name" for="expandField-$changeID">Expand Field
     <select name="expandField" class="default-selector mg-b-plus-1" id="expandField-$changeID">
        $expandField
     </select>
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
        $keyValue = $event->getKeyValueInData($data, $data->inputName);
        $fieldSlug = array_combine($data?->fieldSlug ?? [], $data?->fieldSlug ?? []);
        $expandField = (isset($data->expandField)) ? $data->expandField : '1';
        $defaultFieldSlug = (empty($keyValue)) ? $data?->defaultFieldSlug : $keyValue;
        $fieldSelectDropperFrag = '';
        $fields = null;
        db(onGetDB: function ($db) use (&$fields) {
            $table = Tables::getTable(Tables::FIELD);
            $fields = $db->run("SELECT * FROM $table");
        });

        $fieldSelectionFrag = '';
        $defaultFieldSlugFrag = '';
        foreach ($fields as $field) {
            $uniqueSlug = "$field->field_slug";
            if (isset($fieldSlug[$field->field_slug])) {
                $fieldSelected = '';
                if ($uniqueSlug === $defaultFieldSlug) {
                    $fieldSelected = 'selected';
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
                $defaultFieldSlugFrag = FieldConfig::expandFieldWithChildrenFromMetaBox($event, $defaultFieldSlug);
            }

            $fieldSelectDropperFrag = <<<FieldSelectionDropperFrag
<div class="tonics-field-selection-dropper-container">
        <ul style="margin-left: 0; transform: unset; box-shadow: unset;" data-cell_position="1" class="tonics-field-selection-dropper-ul row-col-item-user margin-top:0 owl">
                $defaultFieldSlugFrag
         </ul>
    </div>
FieldSelectionDropperFrag;
        }

        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$data->field_slug}_$changeID";

        $frag .= <<<HTML
<div class="form-group tonics-field-selection-dropper-form-group margin-top:0 owl">
     <label class="field-settings-handle-name owl" for="fieldSlug-$changeID">Choose Field
     <select name="$inputName" class="default-selector mg-b-plus-1 tonics-field-selection-dropper-select" id="fieldSlug-$changeID">
        <option label=" "></option>
        $fieldSelectionFrag
     </select>
    </label>
    $fieldSelectDropperFrag
</div>
HTML;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }
}