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

class InputSelect implements HandlerInterface
{

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
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Select';
        $inputName =  (isset($data->inputName)) ? $data->inputName : '';
        $selectData =  (isset($data->selectData)) ? helper()->htmlSpecChar($data->selectData) : '';
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
     <label class="menu-settings-handle-name" for="selectData-$changeID">SelectsData (format: k1:kv, k2:v2), (uses key as value if kv is empty)
     <textarea name="selectData" id="selectData-$changeID" placeholder="Key and Value should be separated by comma">$selectData</textarea>
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="choice-default-value-$changeID">Default Value
            <input id="choice-default-value-$changeID" name="defaultValue" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$defaultValue" placeholder="Enter the key to use as default, e.g k1">
    </label>
</div>

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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Choice';
        $inputName =  (isset(getPostData()[$data->inputName])) ? getPostData()[$data->inputName] : '';
        $defaultValue = (isset($data->defaultValue) && !empty($inputName)) ? $inputName : $data->defaultValue;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $selectData =  (isset($data->selectData)) ? $data->selectData : '';

        $choiceKeyValue = [];
        if (!empty($selectData)){
            $selectData = explode(',', $selectData);
        }
        if (is_array($selectData)){
            foreach ($selectData as $choice){
                $choice = explode(':', $choice);
                if (key_exists(0, $choice)){
                    $choiceKeyValue[$choice[0] ?? ''] = $choice[1] ?? $choice[0];
                }
            }
        }

        $slug = $data->field_slug;
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $inputName =  (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";

        $choiceFrag = ''; $error = '';
        foreach ($choiceKeyValue as $key => $value){
            $selected = '';
            if ($key == $defaultValue){
                if ($data->_field->canValidate && !empty($data->field_validations)){
                    $error = $event->validationMake([$inputName => $defaultValue], [$inputName => $data->field_validations]);
                }
                $selected = 'selected';
            }
            $choiceFrag .=<<<HTML
<option $selected title="$value" value="$key">$value</option>
HTML;

        }
        $frag .= <<<FORM
<div class="form-group margin-top:0">
$error
<select class="default-selector mg-b-plus-1" name="$inputName">
    $choiceFrag
</select>
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
        $event->defaultInputViewHandler('InputSelect', $data);
        return '';
    }

}