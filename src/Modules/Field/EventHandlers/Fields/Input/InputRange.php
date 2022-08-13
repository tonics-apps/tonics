<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\EventHandlers\Fields\Input;

use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class InputRange implements HandlerInterface
{
    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Range', 'A Range slider for setting a numeric value',
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
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Range';
        $inputName =  (isset($data->inputName)) ? $data->inputName : '';
        $min =  (isset($data->min)) ? $data->min : '';
        $max =  (isset($data->max)) ? $data->max : '';
        $step =  (isset($data->step)) ? $data->step : '';
        $defaultValue =  (isset($data->defaultValue)) ? $data->defaultValue : '';
        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $fieldValidation = (isset($data->field_validations)) ? $data->field_validations : [];
        $validationFrag = $event->getFieldData()->getFieldsValidationSelection($fieldValidation, $changeID);
        $frag .=<<<FORM
<div class="form-group d:flex flex-gap">
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
     <label class="menu-settings-handle-name" for="min-range-$changeID">Min Range (Blank for no min)
            <input id="min-range-$changeID" name="min" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$min">
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="max-range-$changeID">Max Range (Blank for no max)
            <input id="max-range-$changeID" name="max" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$max">
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="step-$changeID">Step (Blank for no step)
     <input id="step-$changeID" name="step" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$step">
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="default-value-$changeID">Default Value
            <input id="default-value-$changeID" name="defaultValue" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$defaultValue" placeholder="a default value">
    </label>
</div>

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
        $maxChar = (isset($data->maxChar)) ? "maxlength=" . $data->maxChar . '"' : '';
        $step = (isset($data->step)) ? "step=" . $data->step . '"' : '';
        $min = (isset($data->min)) ? "min=" . $data->min . '"' : '';
        $max= (isset($data->max)) ? "max=" . $data->max . '"' : '';
        $textType = 'range';
        $inputName =  (isset(getPostData()[$data->inputName])) ? getPostData()[$data->inputName] : '';
        $defaultValue = (isset($data->defaultValue) && !empty($inputName)) ? $inputName : $data->defaultValue;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';

        $slug = $data->field_slug;
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $inputName =  (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";

        $error = '';
        if ($data->_field->canValidate && !empty($data->field_validations)){
            $error = $event->validationMake([$inputName => $defaultValue], [$inputName => $data->field_validations]);
        }
        $frag .= <<<FORM
<div class="form-group margin-top:0">
$error
     <label class="menu-settings-handle-name" for="fieldName-$changeID">$fieldName
            <input id="fieldName-$changeID" $min $max $step  name="$inputName" type="$textType" $maxChar
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
        $event->defaultInputViewHandler('InputRange', $data);
        return '';
    }
}