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

class FieldSelectionDropper implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox(
            'FieldSelectionDropper',
            'Add a Field',
            'Modular',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            }, userForm: function ($data) use ($event) {
            return $this->userForm($event, $data);
        },
            handleViewProcessing: function ($data) use ($event) {
                return '';
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
        $fieldSlug = (isset($data->fieldSlug)) ? $data->fieldSlug : [];

        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $table = Tables::getTable(Tables::FIELD);
        $fields = db()->run("SELECT * FROM $table");
        $fieldFrag = '';
        $fieldSlug = array_combine($fieldSlug, $fieldSlug);
        $selectedFields = [];
        foreach ($fields as $field) {
            $uniqueSlug = "$field->field_slug";
            $fieldSelected = (isset($fieldSlug[$uniqueSlug])) ? 'selected' : '';
            if ($fieldSelected === 'selected'){
                $selectedFields[] = $field;
            }
            $fieldFrag .= <<<HTML
<option value="$uniqueSlug" $fieldSelected>$field->field_name</option>
HTML;
        }

        $selectedFieldFrag = '';
        foreach ($selectedFields as $field){
            $uniqueSlug = "$field->field_slug";
            $selected = '';
            if ($uniqueSlug === $data?->defaultFieldSlug){
                $selected = 'selected';
            }
            $selectedFieldFrag .= <<<HTML
<option value="$uniqueSlug" $selected>$field->field_name</option>
HTML;
        }

        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';
        $moreSettings = $event->generateMoreSettingsFrag($data);

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
    <label class="field-settings-handle-name" for="fieldSlug-$changeID">Choose Field(s)
        <select name="fieldSlug" multiple class="default-selector mg-b-plus-1" id="fieldSlug-$changeID">
        $fieldFrag
        </select>
    </label>
</div>
<div class="form-group">
    <label class="field-settings-handle-name" for="defaultFieldSlug-$changeID">Choose Default Field Slug
           <select name="defaultFieldSlug" class="default-selector mg-b-plus-1" id="defaultFieldSlug-$changeID">
                $selectedFieldFrag
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
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $fieldSlug = array_combine($data?->fieldSlug ?? [], $data?->fieldSlug ?? []);

        $table = Tables::getTable(Tables::FIELD);
        $fields = db()->run("SELECT * FROM $table");

        $fieldSelectionFrag = ''; $defaultFieldSlug = '';
        foreach ($fields as $field) {
            $uniqueSlug = "$field->field_slug";
            if (isset($fieldSlug[$field->field_slug])){
                $fieldSelected = '';
                if ($uniqueSlug === $data?->defaultFieldSlug){
                    $fieldSelected = 'selected';
                    $defaultFieldSlug = $event->getFieldData()->generateFieldWithFieldSlug(
                        [$uniqueSlug],
                        []
                    )->getHTMLFrag();
                }
                $fieldSelectionFrag .= <<<HTML
<option value="$uniqueSlug" $fieldSelected>$field->field_name</option>
HTML;
            }
        }

        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$data->field_slug}_$changeID";

        $frag .= <<<HTML
<div class="form-group margin-top:0 owl">
     <label class="field-settings-handle-name owl" for="fieldSlug-$changeID">Choose Field
     <select name="$inputName" class="default-selector mg-b-plus-1" id="fieldSlug-$changeID">
        $fieldSelectionFrag
     </select>
    </label>
    <div class="tonics-field-selection-dropper">
        <ul style="margin-left: 0; transform: unset; box-shadow: unset;" data-cell_position="1" class="row-col-item-user margin-top:0 owl">
                $defaultFieldSlug
         </ul>
    </div>
</div>
HTML;

        $frag .= $event->_bottomHTMLWrapper();

        return $frag;
    }

}