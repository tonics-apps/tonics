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

namespace App\Modules\Field\EventHandlers\Fields\Input;

use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class InputRichText implements HandlerInterface
{

    /**
     * @throws \Exception
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Rich Text', 'A basic single-line text fields.',
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
     */
    public function settingsForm (OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Rich Text';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $maxChar = (isset($data->maxChar)) ? $data->maxChar : '';
        $placeholder = (isset($data->placeholder)) ? $data->placeholder : '';
        if (isset($data->readOnly) && $data->readOnly === '1') {
            $readOnly = <<<HTML
<option value="0">False</option>
<option value="1" selected>True</option>
HTML;
        } else {
            $readOnly = <<<HTML
<option value="0" selected>False</option>
<option value="1">True</option>
HTML;
        }
        $required = (isset($data->required)) ? $data->required : '1';
        if ($required === '1') {
            $required = <<<HTML
<option value="0">False</option>
<option value="1" selected>True</option>
HTML;
        } else {
            $required = <<<HTML
<option value="0" selected>False</option>
<option value="1">True</option>
HTML;
        }
        $defaultValue = (isset($data->defaultValue)) ? helper()->htmlSpecChar($data->defaultValue) : '';
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $fieldValidation = (isset($data->field_validations)) ? $data->field_validations : [];
        $fieldSanitization = (isset($data->field_sanitization[0])) ? $data->field_sanitization[0] : '';

        $toggleable = $event->booleanOptionSelectWithNull($data->toggleable ?? '');

        $validationFrag = $event->getFieldData()->getFieldsValidationSelection($fieldValidation, $changeID);
        $sanitizationFrag = $event->getFieldData()->getFieldsSanitizationSelection($event->getFieldSanitization(), $fieldSanitization, $changeID);

        $moreSettings = $event->generateMoreSettingsFrag($data, <<<HTML
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="placeholder-$changeID">Placeholder
            <input id="placeholder-$changeID" name="placeholder" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$placeholder" placeholder="a placeholder">
    </label>
     <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="max-char-$changeID">Max Character (Blank for no limit)
            <input id="max-char-$changeID" name="maxChar" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$maxChar" placeholder="blank for no limit">
    </label>
</div>

<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="readonly-$changeID">readOnly (Can't be edited by user)
     <select name="readOnly" class="default-selector mg-b-plus-1" id="readonly-$changeID">
           $readOnly
      </select>
    </label>
    
    <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="required-$changeID">Required
     <select name="required" class="default-selector mg-b-plus-1" id="required-$changeID">
           $required
      </select>
    </label>
</div>

<div class="form-group d:flex flex-gap align-items:flex-end">
        <label class="menu-settings-handle-name d:flex width:100% flex-d:column d:flex width:100% flex-d:column" for="toggleable-$changeID">Toggable
        <select name="toggleable" class="default-selector mg-b-plus-1" id="toggleable-$changeID">
           $toggleable
        </select>
    </label>
</div>
HTML,
        );

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
     <label class="menu-settings-handle-name" for="default-value-$changeID">Default Value
            <input id="default-value-$changeID" name="defaultValue" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$defaultValue" placeholder="a default value">
    </label>
</div>

$moreSettings
<div class="form-group">
    $validationFrag
</div>

<div class="form-group">
    $sanitizationFrag
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception|\Throwable
     */
    public function userForm (OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Rich Text';
        $placeholder = (isset($data->placeholder)) ? $data->placeholder : '';

        $keyValue = $event->getKeyValueInData($data, $data->inputName);
        $defaultValue = (isset($data->defaultValue) && !empty($keyValue)) ? $keyValue : $data->defaultValue;
        $readOnly = ($data->readOnly == 1) ? 'readonly' : '';
        $required = ($data->required == 1) ? 'required' : '';
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';

        $isToggleable = null;
        if (isset($data->toggleable) && $data->toggleable !== '') {
            $isToggleable = $data->toggleable === '1';
        }

        $slug = $data->field_slug;
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";
        $frag = $event->_topHTMLWrapper($fieldName, $data, toggleUserSettings: $isToggleable);

        $fieldValidation = (isset($data->field_validations)) ? $data->field_validations : [];
        $fieldSanitization = (isset($data->field_sanitization[0])) ? $data->field_sanitization[0] : '';
        $error = '';
        if (!empty($fieldValidation)) {
            $error = $event->validationMake([$inputName => $defaultValue], [$inputName => $data->field_validations]);
        }

        if (!empty($fieldSanitization)) {
            $defaultValue = $event->sanitize($fieldSanitization, $defaultValue, $data);
        }

        $defaultValue = helper()->htmlSpecChar($defaultValue);
        $frag .= <<<FORM
<div class="form-group margin-top:0">
$error
     <label class="menu-settings-handle-name screen-reader-text" for="fieldName-$changeID">$fieldName</label>
         <textarea $readOnly $required id="" name="$inputName" data-field_unique_slug="$changeID" class="tinyMCEBodyArea menu-name color:black border-width:default border:black widgetSettings" 
         placeholder="$placeholder">$defaultValue</textarea>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }
}