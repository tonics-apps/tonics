<?php
/*
 *     Copyright (c) 2022-2025. Olayemi Faruq <olayemi@tonics.app>
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

use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class InputSelect implements HandlerInterface
{
    private array $fieldInputSelects = [];

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Select', 'HTML Select',
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
    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Select';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $selectData = (isset($data->selectData)) ? helper()->htmlSpecChar($data->selectData) : '';
        $defaultValue = (isset($data->defaultValue)) ? $data->defaultValue : '';

        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';

        $fieldValidation = (isset($data->field_validations)) ? $data->field_validations : [];
        $fieldSanitization = (isset($data->field_sanitization[0])) ? $data->field_sanitization[0] : '';

        $multiSelect = (isset($data->multiSelect)) ? $data->multiSelect : '0';
        $multiSelect = $event->booleanOptionSelect($multiSelect);

        $validationFrag = $event->getFieldData()->getFieldsValidationSelection($fieldValidation, $changeID);
        $sanitizationFrag = $event->getFieldData()->getFieldsSanitizationSelection($event->getFieldSanitization(), $fieldSanitization, $changeID);
        $hookName = (isset($data->hookName)) ? $data->hookName : '';

        $moreSettings = $event->generateMoreSettingsFrag($data, <<<HTML

<div class="form-group d:flex flex-gap align-items:flex-end">

    <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="required-$changeID">Multi-Select
        <select name="multiSelect" class="default-selector mg-b-plus-1" id="multiSelect-$changeID">
        $multiSelect
        </select>
    </label>
    <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="hookName-$changeID">Hook Name
        <input id="hookName-$changeID" name="hookName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
        value="$hookName" placeholder="Name of the Selector hook">
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
     <label class="menu-settings-handle-name" for="selectData-$changeID">SelectsData (format: v1:k1, v2:k2), (uses key as value if kv is empty)
     <textarea name="selectData" id="selectData-$changeID" placeholder="Key and Value should be separated by comma">$selectData</textarea>
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="choice-default-value-$changeID">Default Value
            <input id="choice-default-value-$changeID" name="defaultValue" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$defaultValue" placeholder="Enter the key to use as default, e.g k1">
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
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Select';
        $keyValue = $event->getKeyValueInData($data, $data->inputName);
        $defaultValue = $keyValue;
        $hookName = $data->hookName ?? '';
        if (($keyValue === null || (is_string($keyValue) && mb_strlen($keyValue, 'UTF-8') === 0)) && isset($data->defaultValue)) {
            $defaultValue = $data->defaultValue;
        }
        $data->selectedValue = $defaultValue;

        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $selectData = (isset($data->selectData)) ? $data->selectData : '';
        $multiSelect = (isset($data->multiSelect)) ? $data->multiSelect : '0';
        $multiple = ($multiSelect === '1') ? 'multiple' : '';

        $choiceKeyValue = [];
        if (!empty($selectData)) {
            $selectData = explode(',', $selectData);
        }
        if (is_array($selectData)) {
            foreach ($selectData as $choice) {
                $choice = explode(':', $choice);
                if (key_exists(0, $choice)) {
                    $choiceKeyValue[$choice[0] ?? ''] = $choice[1] ?? $choice[0];
                }
            }
        }

        if (!empty($hookName)) {
            if (!isset($this->fieldInputSelects[$hookName])) {
                $dropperEvent = FieldConfig::getFieldSelectionDropper();
                $eventSelects = $dropperEvent->getInputSelectsByName($hookName, true);
                foreach ($eventSelects as $eventSelect) {
                    $choiceKeyValue[$eventSelect] = $eventSelect;
                }
                $this->fieldInputSelects[$hookName] = $choiceKeyValue;
            } else {
                $choiceKeyValue = $this->fieldInputSelects[$hookName];
            }
        }

        $slug = $data->field_slug;
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";

        $fieldValidation = (isset($data->field_validations)) ? $data->field_validations : [];
        $fieldSanitization = (isset($data->field_sanitization[0])) ? $data->field_sanitization[0] : '';

        $choiceFrag = '';
        $error = '';
        foreach ($choiceKeyValue as $key => $value) {
            $selected = '';

            if (is_array($defaultValue)) {
                foreach ($defaultValue as $default) {
                    if ($key === $default) {
                        $selected = 'selected';
                        $data->selectedValue = $default;
                        break;
                    }
                }
            }

            if ($selected === 'selected' || $key == $defaultValue) {
                if (!empty($fieldValidation)) {
                    $error = $event->validationMake([$inputName => $value], [$inputName => $data->field_validations]);
                }

                if (!empty($fieldSanitization)) {
                    $value = $event->sanitize($fieldSanitization, $value, $data);
                }

                $selected = 'selected';
            }
            $choiceFrag .= <<<HTML
<option $selected title="$value" value="$key">$value</option>
HTML;

        }

        $frag .= <<<FORM
<div data-draggable-ignore class="form-group margin-top:0">
$error
<label class="menu-settings-handle-name screen-reader-text" for="fieldName-$changeID">$fieldName</label>
<select class="default-selector mg-b-plus-1" name="$inputName" $multiple>
    $choiceFrag
</select>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

}