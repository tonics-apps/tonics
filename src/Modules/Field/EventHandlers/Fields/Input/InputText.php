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

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\AbstractDataTableFieldInterface;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class InputText implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        $script = AppConfig::getModuleAsset('Core', '/js/views/field/native/script.js');
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Text', 'A basic single-line text fields.',
            'input', $script,
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event){
                return $this->userForm($event, $data);
            },
            handleViewProcessing: function ($data) use ($event) {
                $this->viewData($event, $data);
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

        $readOnly = (isset($data->readOnly)) ? $data->readOnly : '1';
        $readOnly = $event->booleanOptionSelect($readOnly);

        $required = (isset($data->required)) ? $data->required : '1';
        $required = $event->booleanOptionSelect($required);

        $defaultValue =  (isset($data->defaultValue)) ? helper()->htmlSpecChar($data->defaultValue) : '';
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';

        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $fieldValidation = (isset($data->field_validations)) ? $data->field_validations : [];
        $validationFrag = $event->getFieldData()->getFieldsValidationSelection($fieldValidation, $changeID);

        $moreSettings = $event->generateMoreSettingsFrag($data, <<<HTML
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
HTML);

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
            <input id="default-value-$changeID" name="defaultValue" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$defaultValue" placeholder="a default value">
    </label>
</div>

$moreSettings
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
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Text';
        $postData = (isset($data->_field->field_data)) ? $data->_field->field_data : [];
        $inputName =  (isset($postData[$data->inputName])) ? $postData[$data->inputName] : '';
        $defaultValue = (isset($data->defaultValue) && !empty($inputName)) ? $inputName : $data->defaultValue;
        $defaultValue = helper()->htmlSpecChar($defaultValue);

        $maxChar =  (isset($data->maxChar)) ?  'maxlength="' . $data->maxChar . '"' : '';
        $placeholder =  (isset($data->placeholder)) ? $data->placeholder : '';
        $textType =  (isset($data->textType)) ? $data->textType : 'text';
        $readOnly =  ($data->readOnly == 1) ? 'readonly' : '';
        $required =  ($data->required == 1) ? 'required' : '';
        $changeID = helper()->randString(10);

        $slug = $data->field_slug;
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $inputName =  (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";

        $error = '';
        if (!empty($data->field_validations)){
            $error = $event->validationMake([$inputName => $defaultValue], [$inputName => $data->field_validations]);
        }

        if ($textType === 'textarea') {
            $frag .= <<<FORM
<div class="form-group margin-top:0">
    $error
     <label class="menu-settings-handle-name screen-reader-text" for="fieldName-$changeID">$fieldName</label>
            <textarea id="fieldName-$changeID" $readOnly $required name="$inputName" $maxChar
            class="menu-name color:black border-width:default border:black placeholder-color:gray" 
            placeholder="$placeholder">$defaultValue</textarea>
</div>
FORM;
        } else {
            $frag .= <<<FORM
<div class="form-group margin-top:0">
    $error
     <label class="menu-settings-handle-name screen-reader-text" for="fieldName-$changeID">$fieldName</label>
            <input id="fieldName-$changeID" $readOnly $required name="$inputName" type="$textType" $maxChar
            class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$defaultValue" placeholder="$placeholder">
</div>
FORM;
        }

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function viewData(OnFieldMetaBox $event, $data)
    {
        $event->defaultInputViewHandler('InputText', $data);
    }
}