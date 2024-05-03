<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\EventHandlers\Fields;

use App\Apps\TonicsCloud\Controllers\InstanceController;
use App\Apps\TonicsCloud\Controllers\TonicsCloudSettingsController;
use App\Apps\TonicsCloud\Interfaces\CloudServerInterface;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Throwable;

class CloudRegions implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('CloudRegions', 'Cloud Instance Regions', 'TonicsCloud',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event) {
                return $this->userForm($event, $data);
            },
            handleViewProcessing: function () {
            }
        );
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @return string
     * @throws \Exception
     */
    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Cloud Regions';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';
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
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;

    }

    /**
     * @throws \Exception|Throwable
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $frag = '';
        if (InstanceController::getCurrentControllerMethod() === InstanceController::CREATE_METHOD) {
            $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Cloud Regions';
            $frag = $event->_topHTMLWrapper($fieldName, $data);
            $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
            $slug = $data->field_slug;
            $inputName = (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";

            $defaultValue = (isset($data->defaultValue)) ? $data->defaultValue : '';
            $keyValue = $event->getKeyValueInData($data, $data->inputName);
            $defaultValue = $keyValue ?: $defaultValue;

            $handler = TonicsCloudActivator::getCloudServerHandler(TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::CloudServerIntegrationType));
            $regions = $handler->regions();

            $choiceFrag = '';
            foreach ($regions as $region) {
                $selected = '';
                $id = $region['id'];
                $label = $region['label'];
                if ($id == $defaultValue) {
                    $selected = 'selected';
                }
                $choiceFrag .= <<<HTML
<option $selected title="$label ($id)" value="$id">$label ($id)</option>
HTML;

            }

            $frag .= <<<FORM
<div class="form-group margin-top:0">
<select class="default-selector mg-b-plus-1" name="$inputName">
    $choiceFrag
</select>
</div>
FORM;

            $frag .= $event->_bottomHTMLWrapper();
        }

        return $frag;
    }

}