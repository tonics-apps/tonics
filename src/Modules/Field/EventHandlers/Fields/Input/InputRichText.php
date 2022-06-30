<?php

namespace App\Modules\Field\EventHandlers\Fields\Input;

use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class InputRichText implements HandlerInterface
{

    /**
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Rich Text', 'A basic single-line text fields.',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event){
                return $this->userForm($event, $data);
            },
            handleViewProcessing: function (){}
        );
    }

    /**
     * @throws \Exception
     */
    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Rich Text';
        $inputName =  (isset($data->inputName)) ? $data->inputName : '';
        $maxChar = (isset($data->maxChar)) ? $data->maxChar : '';
        $placeholder = (isset($data->placeholder)) ? $data->placeholder : '';
        $elementWrapper =  (isset($data->elementWrapper)) ? $data->elementWrapper : '';
        $attributes = (isset($data->attributes)) ? helper()->htmlSpecChar($data->attributes) : '';
        $handleViewProcessingFrag = $event->handleViewProcessingFrag((isset($data->handleViewProcessing)) ? $data->handleViewProcessing : '');
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
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $frag = $event->_topHTMLWrapper($fieldName, $data);

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
     <label class="menu-settings-handle-name" for="max-char-$changeID">Max Character (Blank for no limit)
            <input id="max-char-$changeID" name="maxChar" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$maxChar" placeholder="blank for no limit">
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="placeholder-$changeID">Placeholder
            <input id="placeholder-$changeID" name="placeholder" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$placeholder" placeholder="a placeholder">
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="readonly-$changeID">readOnly (Can't be edited by user)
     <select name="readOnly" class="default-selector mg-b-plus-1" id="readonly-$changeID">
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
<div class="form-group">
     <label class="menu-settings-handle-name" for="handleViewProcessing-$changeID">Automatically Handle View Processing
     <select name="handleViewProcessing" class="default-selector mg-b-plus-1" id="handleViewProcessing-$changeID">
        $handleViewProcessingFrag
     </select>
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Rich Text';
        $placeholder = (isset($data->placeholder)) ? $data->placeholder : '';
        $inputName =  (isset($data->_field->postData[$data->inputName])) ? $data->_field->postData[$data->inputName] : '';
        $defaultValue = (isset($data->defaultValue) && !empty($inputName)) ? $inputName : $data->defaultValue;
        $readOnly = ($data->readOnly == 1) ? 'readonly' : '';
        $required = ($data->required == 1) ? 'required' : '';
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';

        $slug = $data->field_slug;
        $inputName =  (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $error = '';
        if ($data->_field->canValidate && !empty($data->field_validations)){
            $error = $event->validationMake(['defaultValue' => $defaultValue], ['defaultValue' => $data->field_validations]);
        }
        $frag .= <<<FORM
<div class="form-group margin-top:0">
$error
     <label class="menu-settings-handle-name screen-reader-text" for="fieldName-$changeID">$fieldName</label>
         <textarea autofocus $readOnly $required id="" name="$inputName" class="tinyMCEBodyArea menu-name color:black border-width:default border:black widgetSettings" 
         placeholder="$placeholder">$defaultValue</textarea>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper(true);
        return $frag;
    }
}