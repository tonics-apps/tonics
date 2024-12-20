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

class InputDate implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent (object $event): void
    {
        $script = AppConfig::getModuleAsset('Core', '/js/views/field/native/script.js');
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Date', 'A field for entering Date, DateTimeLocal, Time, and Week',
            'input', $script,
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Date';
        $inputName = (isset($data->inputName)) ? $data->inputName : "";
        $min = (isset($data->min)) ? $data->min : '';
        $max = (isset($data->max)) ? $data->max : '';
        $dateType = (isset($data->dateType)) ? $data->dateType : 'date';

        $dateTypes = [
            'Date'           => 'date',
            'DateTime Local' => 'datetime-local',
            'Month'          => 'month',
            'Week'           => 'week',
            'Time'           => 'time',
        ];

        $dateFrag = '';
        foreach ($dateTypes as $dateK => $dateV) {
            $dateSelected = ($dateV === $dateType) ? 'selected' : '';
            $dateFrag .= <<<HTML
<option value="$dateV" name="dateType" $dateSelected>$dateK</option>
HTML;
        }

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
        $defaultValue = (isset($data->defaultValue)) ? $data->defaultValue : '';

        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';

        $fieldValidation = (isset($data->field_validations)) ? $data->field_validations : [];
        $fieldSanitization = (isset($data->field_sanitization[0])) ? $data->field_sanitization[0] : '';

        $validationFrag = $event->getFieldData()->getFieldsValidationSelection($fieldValidation, $changeID);
        $sanitizationFrag = $event->getFieldData()->getFieldsSanitizationSelection($event->getFieldSanitization(), $fieldSanitization, $changeID);

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
     <label class="menu-settings-handle-name" for="text-type-$changeID">Date Type
     <select name="dateType" class="default-selector mg-b-plus-1" id="text-type-$changeID">
        $dateFrag
     </select>
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="min-date-$changeID">Min Date (Blank for no min)
            <input id="min-date-$changeID" name="min" type="$dateType" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$min" placeholder="blank for no limit">
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="max-date-$changeID">Max Date (Blank for no max)
            <input id="max-date-$changeID" name="max" type="$dateType" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$max" placeholder="a placeholder">
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="date-readonly-$changeID">readOnly (Can't be edited by user)
     <select name="readonly" class="default-selector mg-b-plus-1" id="date-readonly-$changeID">
           $readOnly
      </select>
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="required-$changeID">Required
     <select name="required" class="default-selector mg-b-plus-1" id="required-$changeID">
           $required
      </select>
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="default-value-$changeID">Default Value
            <input id="default-value-$changeID" name="defaultValue" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$defaultValue" placeholder="a default value">
    </label>
</div>

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
     * @throws \Exception
     * @throws \Throwable
     */
    public function userForm (OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Date';

        $keyValue = $event->getKeyValueInData($data, $data->inputName);
        $defaultValue = (isset($data->defaultValue) && !empty($keyValue)) ? $keyValue : $data->defaultValue;

        $min = (isset($data->min)) ? "min='$data->min'" : '';
        $max = (isset($data->max)) ? "max='$data->max'" : '';
        $dateType = (isset($data->dateType)) ? $data->dateType : 'date';
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';

        $slug = $data->field_slug;
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";
        $fieldValidation = (isset($data->field_validations)) ? $data->field_validations : [];
        $fieldSanitization = (isset($data->field_sanitization[0])) ? $data->field_sanitization[0] : '';
        $error = '';
        if (!empty($fieldValidation)) {
            $error = $event->validationMake([$inputName => $defaultValue], [$inputName => $data->field_validations]);
        }
        if (!empty($fieldSanitization)) {
            $defaultValue = $event->sanitize($fieldSanitization, $defaultValue, $data);
        }

        $defaultValue = str_replace(' ', 'T', $defaultValue);
        $frag .= <<<FORM
<div data-draggable-ignore class="form-group margin-top:0">
$error
     <label class="menu-settings-handle-name screen-reader-text" for="fieldName-$changeID">$fieldName</label>
            <input id="fieldName-$changeID" name="$inputName" type="$dateType" $min $max
            class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$defaultValue">
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

}