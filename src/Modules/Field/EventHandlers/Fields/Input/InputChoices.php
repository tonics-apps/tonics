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

class InputChoices implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Choices', 'A field for selecting and or deselecting single value out of many',
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
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Choice';
        $inputName =  (isset($data->inputName)) ? $data->inputName : '';
        $choiceType =  (isset($data->choiceType)) ? $data->choiceType : 'checkbox';
        $choices =  (isset($data->choices)) ? helper()->htmlSpecChar($data->choices) : '';
        $choiceTypes = [
            'Checkbox' => 'checkbox',
            'Radio' => 'radio',
        ];
        $choiceFrag = '';
        foreach ($choiceTypes as $choiceK => $choiceV){
            if ($choiceV === $choiceType){
                $choiceFrag .= <<<HTML
<option value="$choiceV" selected>$choiceK</option>
HTML;
            } else {
                $choiceFrag .= <<<HTML
<option value="$choiceV">$choiceK</option>
HTML;
            }
        }
        $defaultValue =  (isset($data->defaultValue)) ? $data->defaultValue : '';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
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
     <label class="menu-settings-handle-name" for="choice-type-$changeID">Choice Type
     <select name="choiceType" class="default-selector mg-b-plus-1" id="choice-type-$changeID">
        $choiceFrag
     </select>
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="choices-$changeID">Choices (format: k1:kv, k2:v2)
     <textarea name="choices" id="choices-$changeID" placeholder="Key and Value should be separated by comma">$choices</textarea>
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="choice-default-value-$changeID">Default Value
            <input id="choice-default-value-$changeID" name="defaultValue" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$defaultValue" placeholder="Enter the key to use as default, e.g k1">
    </label>
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
        $textType =  (isset($data->choiceType)) ? $data->choiceType : 'checkbox';
        $defaultValue = (isset($data->defaultValue)) ? $data->defaultValue : '';
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $choices =  (isset($data->choices)) ? $data->choices : '';
        $choiceKeyValue = [];
        if (!empty($choices)){
            $choices = explode(',', $choices);
        }
        if (is_array($choices)){
            foreach ($choices as $choice){
                $choice = explode(':', $choice);
                $choiceKeyValue[$choice[0] ?? ''] = $choice[1] ?? '';
            }
        }

        $slug = $data->field_slug;
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $inputName =  (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";

        $choiceFrag = '';
        foreach ($choiceKeyValue as $key => $value){
            $selected = '';
            if ($key === $defaultValue){
                $selected = 'checked';
            }
            $choiceFrag .=<<<HTML
<li>
    <input $selected type="$textType" title="$value" id="{$key}_$changeID" name="{$inputName}[]" value="$key">
    <label for="{$key}_$changeID">$value</label>
</li>
HTML;

        }
        $frag .= <<<FORM
<div class="form-group margin-top:0">
<ul style="margin-left: 0;" class="list:style:none margin-top:0">
    $choiceFrag
</ul>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function viewFrag(OnFieldMetaBox $event, $data): string
    {
        $event->defaultInputViewHandler('InputChoice', $data);
        return '';
    }
}