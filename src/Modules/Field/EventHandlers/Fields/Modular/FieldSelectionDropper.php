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
    const FieldSlug = 'modular_fieldselectiondropper';

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
        $expandField = (isset($data->expandField)) ? $data->expandField : '1';

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
        $fields = null;
        db(onGetDB: function ($db) use (&$fields){
            $table = Tables::getTable(Tables::FIELD);
            $fields = $db->run("SELECT * FROM $table");
        });

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
        $keyValue =  $event->getKeyValueInData($data, $data->inputName);
        $fieldSlug = array_combine($data?->fieldSlug ?? [], $data?->fieldSlug ?? []);
        $expandField = (isset($data->expandField)) ? $data->expandField : '1';
        $defaultFieldSlug = (empty($keyValue)) ? $data?->defaultFieldSlug : $keyValue;
        $fieldSelectDropperFrag = '';
        $fields = null;
        db(onGetDB: function ($db) use (&$fields){
            $table = Tables::getTable(Tables::FIELD);
            $fields = $db->run("SELECT * FROM $table");
        });

        $fieldSelectionFrag = '';
        $defaultFieldSlugFrag = '';
        foreach ($fields as $field) {
            $uniqueSlug = "$field->field_slug";
            if (isset($fieldSlug[$field->field_slug])){
                $fieldSelected = '';
                if ($uniqueSlug === $defaultFieldSlug){
                    $fieldSelected = 'selected';
                    if ($expandField === '1'){
                        $defaultFieldSlugFrag = $event->getFieldData()->generateFieldWithFieldSlug(
                            [$uniqueSlug],
                            []
                        )->getHTMLFrag();
                    }
                }
                $fieldSelectionFrag .= <<<HTML
<option value="$uniqueSlug" $fieldSelected>$field->field_name</option>
HTML;
            }
        }

        if ($expandField === '1'){
            if (isset($data->_field->_children)){

                $originalFieldItems = null;
                db(onGetDB: function ($db) use ($event, $defaultFieldSlug, &$originalFieldItems){
                    $fieldTable = $event->getFieldData()->getFieldTable();
                    $fieldItemsTable = $event->getFieldData()->getFieldItemsTable();
                    $fieldAndFieldItemsCols = $event->getFieldData()->getFieldAndFieldItemsCols();

                    $originalFieldItems = $db->Select($fieldAndFieldItemsCols)
                        ->From($fieldItemsTable)
                        ->Join($fieldTable, "$fieldTable.field_id", "$fieldItemsTable.fk_field_id")
                        ->WhereEquals('field_slug', $defaultFieldSlug)->OrderBy('fk_field_id')->FetchResult();
                });

                foreach ($originalFieldItems as $originalFieldItem){
                    $fieldOption = json_decode($originalFieldItem->field_options);
                    $originalFieldItem->field_options = $fieldOption;
                }

                // Sort and Arrange OriginalFieldItems
                $originalFieldItems = helper()->generateTree(['parent_id' => 'field_parent_id', 'id' => 'field_id'], $originalFieldItems);
                if (isset($data->_field->_children)){
                    $sortedFieldWalkerItems = $event->getFieldData()->sortFieldWalkerTree($originalFieldItems, $data->_field->_children);
                } else {
                    $sortedFieldWalkerItems = $originalFieldItems;
                }

                foreach ($sortedFieldWalkerItems as $sortedFieldWalkerItem) {
                    if (isset($sortedFieldWalkerItem->_children)) {
                        $sortedFieldWalkerItem->field_options->_children = $sortedFieldWalkerItem->_children;
                    }
                }
                $fieldCategories = [$defaultFieldSlug => $sortedFieldWalkerItems];
                $defaultFieldSlugFrag = $event->getFieldData()->getUsersFormFrag($fieldCategories);
            }

            $fieldSelectDropperFrag = <<<FieldSelectionDropperFrag
<div class="tonics-field-selection-dropper-container">
        <ul style="margin-left: 0; transform: unset; box-shadow: unset;" data-cell_position="1" class="tonics-field-selection-dropper-ul row-col-item-user margin-top:0 owl">
                $defaultFieldSlugFrag
         </ul>
    </div>
FieldSelectionDropperFrag;
        }

        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$data->field_slug}_$changeID";

        $frag .= <<<HTML
<div class="form-group tonics-field-selection-dropper-form-group margin-top:0 owl">
     <label class="field-settings-handle-name owl" for="fieldSlug-$changeID">Choose Field
     <select name="$inputName" class="default-selector mg-b-plus-1 tonics-field-selection-dropper-select" id="fieldSlug-$changeID">
        $fieldSelectionFrag
     </select>
    </label>
    $fieldSelectDropperFrag
</div>
HTML;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

}