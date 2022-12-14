<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Page\EventHandlers\Fields;

use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Page\Events\OnPageTemplate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class PageTemplateFieldSelection implements HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox(
            'PageTemplateSelection',
            'Add a Page Template Selection',
            'Page',
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'PageTemplate';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $frag = $event->_topHTMLWrapper($fieldName, $data);


        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';
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
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;

    }

    /**
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName = $data->fieldName ?? 'Field';
        $keyValue =  $event->getKeyValueInData($data, $data->inputName);
        $templateSelected = $data->templateSelected ?? $keyValue;
        $changeID = $data->field_slug_unique_hash ?? 'CHANGEID';
        $slug = $data->field_slug;

        $onPageTemplateEvent = new OnPageTemplate();
        event()->dispatch($onPageTemplateEvent);
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $templateNames = $onPageTemplateEvent->getTemplateNames();

        $inputName =  $data->inputName ?? "{$slug}_$changeID";
        $fieldFrag = '';
        foreach ($templateNames as $templateName => $templateClass) {
            $isSelected = ($templateSelected === $templateName) ? 'selected' : '';
            $fieldFrag .= <<<HTML
<option value="$templateName" $isSelected>$templateName</option>
HTML;
        }

        $frag .= <<<FORM
<div class="form-group">
    <label class="field-settings-handle-name" for="$inputName-$changeID">Choose Page Template
        <select name="$inputName" class="default-selector mg-b-plus-1" id="$inputName-$changeID">
            $fieldFrag
        </select>
    </label>
</div>
FORM;
        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }
}