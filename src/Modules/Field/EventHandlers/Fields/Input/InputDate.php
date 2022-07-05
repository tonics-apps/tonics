<?php

namespace App\Modules\Field\EventHandlers\Fields\Input;

use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class InputDate implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Date', 'A field for entering Date, DateTimeLocal, Time, and Week',
            'input', '/js/views/field/native/script.js',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event){
                return $this->userForm($event, $data);
            },
            handleViewProcessing: function ($data) use ($event) {
                return $this->viewFrag($event, $data);
            }
        );
    }

    /**
     * @throws \Exception
     */
    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Date';
        $inputName =  (isset($data->inputName)) ? $data->inputName : "";
        $min =  (isset($data->min)) ? $data->min : '';
        $max =  (isset($data->max)) ? $data->max : '';
        $dateType =  (isset($data->dateType)) ? $data->dateType : 'date';
        $elementWrapper =  (isset($data->elementWrapper)) ? $data->elementWrapper : '';
        $attributes = (isset($data->attributes)) ? helper()->htmlSpecChar($data->attributes) : '';

        $dateTypes = [
            'Date' => 'date',
            'DateTime Local' => 'datetime-local',
            'Month' => 'month',
            'Week' => 'week',
            'Time' => 'time',
        ];
        $dateFrag = '';
        foreach ($dateTypes as $dateK => $dateV){
            $dateSelected = ($dateV === $dateType) ? 'selected' : '';
            $dateFrag .= <<<HTML
<option value="$dateV" name="dateType" $dateSelected>$dateK</option>
HTML;
        }

        if (isset($data->readOnly) && $data->readOnly === '1'){
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
        if ($required === '1'){
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
        $defaultValue =  (isset($data->defaultValue)) ? $data->defaultValue : '';

        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $fieldValidation = (isset($data->field_validations)) ? $data->field_validations : [];
        $validationFrag = $event->getFieldData()->getFieldsValidationSelection($fieldValidation, $changeID);
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

<div class="form-group d:flex flex-gap align-items:flex-end">
      <label class="menu-settings-handle-name" for="element-wrapper-$changeID">Element Wrapper
            <input id="element-wrapper-$changeID" name="elementWrapper" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$elementWrapper" placeholder="e.g div, section, input">
    </label>
      <label class="menu-settings-handle-name" for="element-attributes-$changeID">Element Attributes
            <input id="element-attributes-$changeID" name="attributes" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$attributes" placeholder="e.g class='class-name' id='id-name' or any attributes">
    </label>
</div>

{$event->handleViewProcessingFrag($data)}
{$event->getTemplateEngineFrag($data)}

<div class="form-group">
    $validationFrag
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Text';
        $inputName =  (isset(getPostData()[$data->inputName])) ? getPostData()[$data->inputName] : '';
        $defaultValue = (isset($data->defaultValue) && !empty($inputName)) ? $inputName : $data->defaultValue;
        $min = (isset($data->min)) ? "min=" . $data->min . '"' : '';
        $max = (isset($data->max)) ? "max=" . $data->max . '"' : '';
        $dateType =  (isset($data->dateType)) ? $data->dateType : 'date';
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';

        $slug = $data->field_slug;
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $inputName =  (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";
        $error = '';
        if ($data->_field->canValidate && !empty($data->field_validations)){
            $error = $event->validationMake([$inputName => $defaultValue], [$inputName => $data->field_validations]);
        }
        $defaultValue = str_replace(' ', 'T', $defaultValue);
        $frag .= <<<FORM
<div class="form-group margin-top:0">
$error
     <label class="menu-settings-handle-name" for="fieldName-$changeID">$fieldName
            <input id="fieldName-$changeID" name="$inputName" type="$dateType" $min $max
            class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$defaultValue">
    </label>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper(true);
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function viewFrag(OnFieldMetaBox $event, $data): string
    {
        if (isset($data->handleViewProcessing) && $data->handleViewProcessing === '1'){
            $event->handleTemplateEngineView($data);
        }
        return '';
    }

}