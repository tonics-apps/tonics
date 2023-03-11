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
     * @throws \Exception
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

        $fields = null;
        db(onGetDB: function ($db) use (&$fields){
            $table = Tables::getTable(Tables::FIELD);
            $fields = $db->run("SELECT * FROM $table");
        });

        $fieldFrag = '';
        foreach ($fields as $field) {
            $uniqueSlug = "$field->field_slug";
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
        $htmlFrag = '';

        $inputName = (isset($data->inputName)) ? $data->inputName : "{$fieldSlug}_$changeID";

        $fieldTable = $event->getFieldData()->getFieldTable();
        $fieldItemsTable = $event->getFieldData()->getFieldItemsTable();
        $fieldAndFieldItemsCols = $event->getFieldData()->getFieldAndFieldItemsCols();

        if ($expandField === '1') {
            $originalFieldItems = null;
            db(onGetDB: function ($db) use ($fieldSlug, $fieldTable, $fieldItemsTable, $fieldAndFieldItemsCols, &$originalFieldItems){
                $originalFieldItems = $db->Select($fieldAndFieldItemsCols)
                    ->From($fieldItemsTable)
                    ->Join($fieldTable, "$fieldTable.field_id", "$fieldItemsTable.fk_field_id")
                    ->WhereEquals('field_slug', $fieldSlug)
                    ->OrderBy('fk_field_id')->FetchResult();
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

                $htmlFrag .= $event->getUsersForm($sortedFieldWalkerItem->field_options->field_slug, $sortedFieldWalkerItem->field_options);
            }

            $frag .= $htmlFrag;
        } else {
            $fields = null;
            db(onGetDB: function ($db) use ($fieldTable, &$fields){
                $fields = $db->Select('*')->From($fieldTable)->FetchResult();
            });

            $fieldFrag = '';
            foreach ($fields as $field) {
                $uniqueSlug = "$field->field_slug";
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

}