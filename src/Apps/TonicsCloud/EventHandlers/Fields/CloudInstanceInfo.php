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
use App\Apps\TonicsCloud\Controllers\TonicsCloudSettingsController;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Throwable;

class CloudInstanceInfo implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('CloudInstanceInfo', 'Cloud Instance Info', 'TonicsCloud',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event) {
                return $this->userForm($event, $data);
            },
        );
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     *
     * @return string
     * @throws \Exception
     */
    public function settingsForm (OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Cloud Instance Info';
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
    public function userForm (OnFieldMetaBox $event, $data): string
    {
        $frag = '';
        if (InstanceController::getCurrentControllerMethod() === InstanceController::EDIT_METHOD) {
            $handlerName = TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::CloudServerIntegrationType);
            if (isset(getPostData()['others'])) {
                $others = json_decode(getPostData()['others']);
                if (isset($others->serverHandlerName)) {
                    $handlerName = $others->serverHandlerName;
                }
            }

            $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Cloud Instance Info';
            $frag = $event->_topHTMLWrapper($fieldName, $data);

            $handler = TonicsCloudActivator::getCloudServerHandler($handlerName);
            $info = $handler->info(getGlobalVariableData()['Data']);

            $frag .= <<<FORM
<section class="dataTable owl">
    <table id="dt" style="grid-template-columns: minmax(150px, 1fr) minmax(100px, .7fr) minmax(300px, 1.6fr);">
            <thead>
            <tr>
                    <th title="Name" data-title="Name" data-slug="name">Region</th>
                    <th title="Version" data-title="Version" data-slug="version">IPV4</th>
                    <th title="Description" data-title="Description" data-slug="description">IPV6</th>
            </tr>
            </thead>
            <tbody class="max-height:300px overflow-x:auto">
                <tr class="cursor:text">
                    <td tabindex="-1">{$info['region']}</td>
                    <td tabindex="-1">{$info['ipv4']}</td>
                    <td tabindex="-1">{$info['ipv6']}</td>
                </tr>
            </tbody>
    </table>
</section>
FORM;

            $frag .= $event->_bottomHTMLWrapper();
        }

        return $frag;
    }

}