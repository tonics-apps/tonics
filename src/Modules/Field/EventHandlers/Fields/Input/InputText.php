<?php

namespace App\Modules\Field\EventHandlers\Fields\Input;

use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class InputText implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Text', 'A basic single-line text fields.',
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

    public function getTestTypes()
    {
        return [
            'Text' => 'text',
            'Number' => 'number',
            'Telephone' => 'tel',
            'URL' => 'url',
            'Email' => 'email',
            'Hidden' => 'hidden',
            'Password' => 'password',
            'Search' => 'search',
            'Textarea' => 'textarea',
        ];
    }

    /**
     * @throws \Exception
     */
    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Text';
        $inputName =  (isset($data->inputName)) ? $data->inputName : '';
        $maxChar =  (isset($data->maxChar)) ? $data->maxChar : '';
        $placeholder =  (isset($data->placeholder)) ? $data->placeholder : '';
        $textType =  (isset($data->textType)) ? $data->textType : 'text';
        $elementWrapper =  (isset($data->elementWrapper)) ? $data->elementWrapper : '';
        $attributes = (isset($data->attributes)) ? helper()->htmlSpecChar($data->attributes) : '';
        $handleViewProcessingFrag = $event->handleViewProcessingFrag((isset($data->handleViewProcessing)) ? $data->handleViewProcessing : '');
        $textTypes = $this->getTestTypes();
        $textFrag = '';
        foreach ($textTypes as $textK => $textV){
            if ($textV === $textType){
                $textFrag .= <<<HTML
<option value="$textV" selected>$textK</option>
HTML;
            } else {
                $textFrag .= <<<HTML
<option value="$textV">$textK</option>
HTML;
            }
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
        $form = '';
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        if (isset($data->_topHTMLWrapper)){
            $topHTMLWrapper = $data->_topHTMLWrapper;
            $slug = $data->_field->field_name ?? null;
            $form = $topHTMLWrapper($fieldName, $slug);
        }
        $fieldValidation = (isset($data->field_validations)) ? $data->field_validations : [];
        $validationFrag = $event->getFieldData()->getFieldsValidationSelection($fieldValidation, $changeID);
        $form .= <<<FORM
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

        if (isset($data->_bottomHTMLWrapper)){
            $form .= $data->_bottomHTMLWrapper;
        }

        return $form;
    }

    /**
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Text';
        $inputName =  (isset($data->_field->postData[$data->inputName])) ? $data->_field->postData[$data->inputName] : '';
        $defaultValue = (isset($data->defaultValue) && !empty($inputName)) ? $inputName : $data->defaultValue;
        $maxChar =  (isset($data->maxChar)) ?  "maxlength=" .$data->maxChar . '"' : '';
        $placeholder =  (isset($data->placeholder)) ? $data->placeholder : '';
        $textType =  (isset($data->textType)) ? $data->textType : 'text';
        $readOnly =  ($data->readOnly == 1) ? 'readonly' : '';
        $required =  ($data->required == 1) ? 'required' : '';
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $topHTMLWrapper = $data->_topHTMLWrapper;
        $slug = $data->field_slug;
        $inputName =  (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";
        $form = $topHTMLWrapper($fieldName, $slug);

        $error = '';
        if ($data->_field->canValidate && !empty($data->field_validations)){
            $error = $event->validationMake([$inputName => $defaultValue], [$inputName => $data->field_validations]);
        }

        if ($textType === 'textarea') {
            $form .= <<<FORM
<div class="form-group margin-top:0">
    $error
     <label class="menu-settings-handle-name screen-reader-text" for="fieldName-$changeID">$fieldName</label>
            <textarea id="fieldName-$changeID" $readOnly $required name="$inputName" $maxChar
            class="menu-name color:black border-width:default border:black placeholder-color:gray" 
            placeholder="$placeholder">$defaultValue</textarea>
</div>
FORM;
        } else {
            $form .= <<<FORM
<div class="form-group margin-top:0">
    $error
     <label class="menu-settings-handle-name screen-reader-text" for="fieldName-$changeID">$fieldName</label>
            <input id="fieldName-$changeID" $readOnly $required name="$inputName" type="$textType" $maxChar
            class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$defaultValue" placeholder="$placeholder">
</div>
FORM;
        }

        if (isset($data->_bottomHTMLWrapper)){
            $form .= $data->_bottomHTMLWrapper;
        }

        return $form;
    }

    /**
     * @throws \Exception
     */
    public function viewFrag(OnFieldMetaBox $event, $data): string
    {
        $inputName =  (isset($data->_field->postData[$data->inputName])) ? $data->_field->postData[$data->inputName] : '';
        $defaultValue = (isset($data->defaultValue) && !empty($inputName)) ? $inputName : $data->defaultValue;
        $frag = '';
        $elementName = strtolower($data->elementWrapper);
        if (isset($data->handleViewProcessing) && $data->handleViewProcessing === '1'){

            if (key_exists($elementName, helper()->htmlTags())) {
                $frag .= <<<HTML
<$elementName
HTML;
                if (!empty($data->attributes)) {
                    $attributes = $event->flatHTMLTagAttributes($data->attributes);
                    $frag .= $attributes;
                }
                $frag .= ">";
            }
            $frag .= helper()->htmlSpecChar($defaultValue);
            if (key_exists($elementName, helper()->htmlTags())) {
                $frag .= <<<HTML
</$elementName>
HTML;
            }
        }
        return $frag;
    }

}