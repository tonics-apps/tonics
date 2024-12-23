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

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class InputText implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent (object $event): void
    {
        $script = AppConfig::getModuleAsset('Core', '/js/views/field/native/script.js');
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Text', 'A basic single-line text fields.',
            'input', $script,
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event) {
                return $this->userForm($event, $data);
            },
        );
    }

    public function getTestTypes (): array
    {
        return [
            'Text'      => 'text',
            'Number'    => 'number',
            'Telephone' => 'tel',
            'URL'       => 'url',
            'Email'     => 'email',
            'Hidden'    => 'hidden',
            'Password'  => 'password',
            'Search'    => 'search',
            'Textarea'  => 'textarea',
        ];
    }

    /**
     * @throws \Exception|\Throwable
     */
    public function settingsForm (OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Text';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $maxChar = (isset($data->maxChar)) ? $data->maxChar : '';
        $placeholder = (isset($data->placeholder)) ? $data->placeholder : '';
        $styles = (isset($data->styles)) ? helper()->htmlSpecChar($data->styles) : '';
        $textType = (isset($data->textType)) ? $data->textType : 'text';

        $textTypes = $this->getTestTypes();
        $textFrag = '';
        foreach ($textTypes as $textK => $textV) {
            if ($textV === $textType) {
                $textFrag .= <<<HTML
<option value="$textV" selected>$textK</option>
HTML;
            } else {
                $textFrag .= <<<HTML
<option value="$textV">$textK</option>
HTML;
            }
        }

        $readOnly = $event->booleanOptionSelect($data->readOnly ?? '0');
        $required = $event->booleanOptionSelect($data->required ?? '0');
        $toggleable = $event->booleanOptionSelectWithNull($data->toggleable ?? '');
        $defaultValue = (isset($data->defaultValue)) ? helper()->htmlSpecChar($data->defaultValue) : '';
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';

        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $fieldValidation = (isset($data->field_validations)) ? $data->field_validations : [];
        $fieldSanitization = (isset($data->field_sanitization[0])) ? $data->field_sanitization[0] : '';

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
     <label class="menu-settings-handle-name" for="text-type-$changeID">Text Type
     <select name="textType" class="default-selector mg-b-plus-1" id="text-type-$changeID">
        $textFrag
     </select>
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="default-value-$changeID">Default Value
     
      <textarea style="height: 60px;" id="default-value-$changeID"  name="defaultValue"
            class="menu-name color:black border-width:default border:black placeholder-color:gray" 
            placeholder="Enter Default Value">$defaultValue</textarea>
            
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
        $fieldName = $data->fieldName ?? 'Text';

        $defaultValue = $data->defaultValue ?? '';
        $keyValue = $event->getKeyValueInData($data, $data->inputName);
        $defaultValue = $keyValue ?? $defaultValue;
        $isToggleable = null;
        if (isset($data->toggleable) && $data->toggleable !== '') {
            $isToggleable = $data->toggleable === '1';
        }

        $maxChar = (isset($data->maxChar)) ? 'maxlength="' . $data->maxChar . '"' : '';
        $placeholder = (isset($data->placeholder)) ? $data->placeholder : '';
        $styles = (isset($data->styles)) ? $data->styles : '';
        $textType = (isset($data->textType)) ? $data->textType : 'text';
        $readOnly = ($data->readOnly == 1) ? 'readonly' : '';
        $required = ($data->required == 1) ? 'required' : '';
        $changeID = helper()->randString(10);

        $slug = $data->field_slug;
        $frag = $event->_topHTMLWrapper($fieldName, $data, toggleUserSettings: $isToggleable);
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";

        $error = '';
        $fieldValidation = (isset($data->field_validations)) ? $data->field_validations : [];
        $fieldSanitization = (isset($data->field_sanitization[0])) ? $data->field_sanitization[0] : '';
        if (!empty($fieldValidation)) {
            $error = $event->validationMake([$inputName => $defaultValue], [$inputName => $data->field_validations]);
        }

        if (!empty($fieldSanitization)) {
            $defaultValue = $event->sanitize($fieldSanitization, $defaultValue, $data);
        }

        $defaultValue = helper()->htmlSpecChar($defaultValue);

        if ($textType === 'textarea') {
            $frag .= <<<FORM
<div data-draggable-ignore class="form-group margin-top:0">
    $error
     <label class="menu-settings-handle-name screen-reader-text" for="fieldName-$changeID">$fieldName</label>
            <textarea style="$styles" id="fieldName-$changeID" $readOnly $required name="$inputName" $maxChar
            class="menu-name color:black border-width:default border:black placeholder-color:gray" 
            placeholder="$placeholder">$defaultValue</textarea>
</div>
FORM;
        } else {
            $frag .= <<<FORM
<div data-draggable-ignore class="form-group margin-top:0">
    $error
     <label class="menu-settings-handle-name screen-reader-text" for="fieldName-$changeID">$fieldName</label>
            <input style="$styles" id="fieldName-$changeID" $readOnly $required name="$inputName" type="$textType" $maxChar
            class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$defaultValue" placeholder="$placeholder">
</div>
FORM;
        }

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }
}