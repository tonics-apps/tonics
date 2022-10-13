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

class InputColor implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Color', 'Input Color',
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

    /**
     * @throws \Exception
     */
    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Color';
        $inputName =  (isset($data->inputName)) ? $data->inputName : '';
        $defaultValue =  (isset($data->defaultValue)) ? $data->defaultValue : '#000000';
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';

        $frag = $event->_topHTMLWrapper($fieldName, $data);

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
     <label class="menu-settings-handle-name" for="defaultValue-$changeID">Default Color
     <input name="defaultValue" value="$defaultValue" type="color" class="menu-name color:black border-width:default border:black placeholder-color:gray" id="defaultValue-$changeID">
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
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Color';
        $postData = (isset($data->_field->field_data)) ? $data->_field->field_data : '';
        $postData = $postData ?: getPostData();
        $inputName =  (isset($postData[$data->inputName])) ? $postData[$data->inputName] : '';
        $defaultValue = (isset($data->defaultValue) && !empty($inputName)) ? $inputName : $data->defaultValue;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $slug = $data->field_slug;
        $inputName =  (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";

        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $frag .= <<<FORM
<div class="form-group">
     <label class="menu-settings-handle-name" for="inputColor-$changeID">Choose Color
     <input name="$inputName" value="$defaultValue" type="color" class="menu-name color:black border-width:default border:black placeholder-color:gray" id="inputColor-$changeID">
    </label>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function viewData(OnFieldMetaBox $event, $data)
    {
        $event->defaultInputViewHandler('InputColor', $data);
    }

}