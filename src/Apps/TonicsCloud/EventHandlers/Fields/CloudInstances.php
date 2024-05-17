<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Apps\TonicsCloud\EventHandlers\Fields;

use App\Apps\TonicsCloud\Controllers\InstanceController;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class CloudInstances implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('CloudInstances', 'Cloud Instances Pertaining To The Logged in User/Customer', 'TonicsCloud',
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Cloud Instances';
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
     * @param OnFieldMetaBox $event
     * @param $data
     * @return string
     * @throws \Exception
     * @throws \Throwable
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Cloud Instances';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $slug = $data->field_slug;
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";

        $keyValue = $event->getKeyValueInData($data, $data->inputName);
        $customerInstances = InstanceController::getServiceInstances(['user_id' => \session()::getUserID(), 'fetch_all' => true]);

        $choiceFrag = '';
        foreach ($customerInstances as $customerInstance) {
            $selected = '';
            if ($customerInstance->provider_instance_id == $keyValue || request()->getParam('instance_id') === $customerInstance->provider_instance_id) {
                $selected = 'selected';
            }
            $choiceFrag .= <<<HTML
<option $selected title="$customerInstance->service_instance_name" value="$customerInstance->provider_instance_id">$customerInstance->service_instance_name</option>
HTML;

        }

        $frag .= <<<FORM
<div class="form-group margin-top:0">
<select class="default-selector mg-b-plus-1" name="$inputName">
  <option label=" "></option>
  $choiceFrag
</select>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

}