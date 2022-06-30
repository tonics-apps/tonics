<?php

namespace App\Modules\Field\EventHandlers\Fields\Modular;

use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Events\OnFieldUserForm;
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
        $inputName =  (isset($data->inputName)) ? $data->inputName : '';
        $fieldSlug = (isset($data->fieldSlug)) ? $data->fieldSlug : '';

        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $table = Tables::getTable(Tables::FIELD);
        $fields = db()->run("SELECT * FROM $table");
        $fieldFrag = '';
        foreach ($fields as $field){
            $uniqueSlug = "$field->field_slug:$field->field_id";
            if ($fieldSlug === $uniqueSlug){
                $fieldFrag .= <<<HTML
<option value="$uniqueSlug" selected>$field->field_name</option>
HTML;
            } else {
                $fieldFrag .= <<<HTML
<option value="$uniqueSlug">$field->field_name</option>
HTML;
            }
        }
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';
        $handleViewProcessingFrag = $event->handleViewProcessingFrag((isset($data->handleViewProcessing)) ? $data->handleViewProcessing : '');
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
<div class="form-group">
     <label class="field-settings-handle-name" for="handleViewProcessing-$changeID">Automatically Handle View Processing
     <select name="handleViewProcessing" class="default-selector mg-b-plus-1" id="handleViewProcessing-$changeID">
        $handleViewProcessingFrag
     </select>
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
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Field';
        $inputName =  (isset($data->_field->postData[$data->inputName])) ? $data->_field->postData[$data->inputName] : '';
        $fieldSlug = (isset($data->fieldSlug) && !empty($inputName)) ? $inputName : $data->fieldSlug;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';

        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $table = Tables::getTable(Tables::FIELD);
        $fields = db()->run("SELECT * FROM $table");
        $fieldFrag = '';
        foreach ($fields as $field){
            $uniqueSlug = "$field->field_slug:$field->field_id";
            if ($fieldSlug === $uniqueSlug){
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
     <select name="fieldSlug" class="default-selector mg-b-plus-1" id="fieldSlug-$changeID">
        $fieldFrag
     </select>
    </label>
</div>
HTML;

        $frag .= $event->_bottomHTMLWrapper(true);
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function viewFrag(OnFieldMetaBox $event, $data): string
    {
        $frag = '';
        if (isset($data->handleViewProcessing) && $data->handleViewProcessing === '1') {
            $inputName = (isset($data->_field->postData[$data->inputName])) ? $data->_field->postData[$data->inputName] : '';
            $fieldSlug = (isset($data->fieldSlug) && !empty($inputName)) ? $inputName : $data->fieldSlug;
            if (empty($fieldSlug)) {
                return $frag;
            }
            $fieldSlug = explode(':', $fieldSlug);
            $fieldID = (isset($fieldSlug[1]) && is_numeric($fieldSlug[1])) ? (int)$fieldSlug[1]: '';
            if (empty($fieldID)){
                return $frag;
            }
            $onFieldUserForm = new OnFieldUserForm([$fieldID], new FieldData(), $data->_field->postData, true);
            return $onFieldUserForm->getHTMLFrag();
        }

        return $frag;
    }

}