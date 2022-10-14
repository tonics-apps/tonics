<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\EventHandlers\Fields\Modular;

use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Events\OnFieldFormHelper;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class FieldSelection implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox(
            'FieldSelection',
            'Add a Field',
            'Modular',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            }, userForm: function ($data) use ($event) {
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Field';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $fieldSlug = (isset($data->fieldSlug)) ? $data->fieldSlug : '';
        $expandField = (isset($data->expandField)) ? $data->expandField : '0';

        if ($expandField === '1') {
            $expandField = <<<HTML
<option value="0">False</option>
<option value="1" selected>True</option>
HTML;
        } else {
            $expandField = <<<HTML
<option value="0" selected>False</option>
<option value="1">True</option>
HTML;
        }

        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $table = Tables::getTable(Tables::FIELD);
        $fields = db()->run("SELECT * FROM $table");
        $fieldFrag = '';
        foreach ($fields as $field) {
            $uniqueSlug = "$field->field_slug:$field->field_id";
            $fieldSelected = ($fieldSlug === $uniqueSlug) ? 'selected' : '';
            $fieldFrag .= <<<HTML
<option value="$uniqueSlug" $fieldSelected>$field->field_name</option>
HTML;
        }
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';
        $moreSettings = $event->generateMoreSettingsFrag($data, <<<HTML
<div class="form-group">
     <label class="field-settings-handle-name" for="expandField-$changeID">Expand Field
     <select name="expandField" class="default-selector mg-b-plus-1" id="expandField-$changeID">
        $expandField
     </select>
    </label>
</div>
HTML
        );

        $frag .= <<<FORM
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="field-settings-handle-name" for="fieldName-$changeID">Field Name
            <input id="fieldName-$changeID" name="fieldName" type="text" class="field-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
    <label class="field-settings-handle-name" for="inputName-$changeID">Input Name
            <input id="inputName-$changeID" name="inputName" type="text" class="field-name color:black border-width:default border:black placeholder-color:gray"
            value="$inputName" placeholder="(Optional) Input Name">
    </label>
</div>
<div class="form-group">
    <label class="field-settings-handle-name" for="fieldSlug-$changeID">Choose Field
        <select name="fieldSlug" class="default-selector mg-b-plus-1" id="fieldSlug-$changeID">
        $fieldFrag
        </select>
    </label>
</div>
$moreSettings
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;

    }

    /**
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Field';
        $keyValue =  $event->getKeyValueInData($data, $data->inputName);
        $fieldSlug = (isset($data->fieldSlug) && !empty($keyValue)) ? $keyValue : $data->fieldSlug;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $expandField = (isset($data->expandField)) ? $data->expandField : '0';

        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $table = Tables::getTable(Tables::FIELD);
        $fields = db()->Select('*')->From($table)->FetchResult();
        $fieldFrag = '';
        foreach ($fields as $field) {
            $uniqueSlug = "$field->field_slug:$field->field_id";
            if ($fieldSlug === $uniqueSlug) {
                $fieldFrag .= <<<HTML
<option value="$uniqueSlug" selected>$field->field_name</option>
HTML;
            } else {
                $fieldFrag .= <<<HTML
<option value="$uniqueSlug">$field->field_name</option>
HTML;
            }
        }

        $inputName = (isset($data->inputName)) ? $data->inputName : "{$fieldSlug}_$changeID";
        $fieldSlug = explode(':', $fieldSlug);
        $fieldID = (isset($fieldSlug[1]) && is_numeric($fieldSlug[1])) ? (int)$fieldSlug[1] : '';

        if (!empty($fieldID) && $expandField === '1') {
            $onFieldUserForm = new OnFieldFormHelper([$fieldID], new FieldData(), getPostData());
            $frag .= $onFieldUserForm->getHTMLFrag();
        } else {
            $frag .= <<<HTML
<div class="form-group margin-top:0">
     <label class="field-settings-handle-name" for="fieldSlug-$changeID">Choose Field
     <select name="$inputName" class="default-selector mg-b-plus-1" id="fieldSlug-$changeID">
        $fieldFrag
     </select>
    </label>
</div>
HTML;
        }

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function viewFrag(OnFieldMetaBox $event, $data): string
    {
        $frag = '';
        $fieldData = (isset($data->_field->field_data)) ? $data->_field->field_data : '';
        $postData = !empty(getPostData()) ? getPostData() : $fieldData;
        $fieldSlug = (isset($postData[$data->inputName])) ? $postData[$data->inputName] : '';
        if (empty($fieldSlug)) {
            return $frag;
        }
        $fieldSlug = explode(':', $fieldSlug);
        $fieldID = (isset($fieldSlug[1]) && is_numeric($fieldSlug[1])) ? (int)$fieldSlug[1] : '';
        if (empty($fieldID)) {
            return $frag;
        }

        $onFieldUserForm = new OnFieldFormHelper([], new FieldData());
        $onFieldUserForm->handleFrontEnd([$fieldSlug[0]], getPostData());
        return $frag;
    }

}