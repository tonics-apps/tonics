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

use App\Apps\TonicsCloud\Services\ContainerService;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Exception;

class CloudContainersOfInstance implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('CloudContainersOfInstance', 'Cloud Containers of an Instance Pertaining To The Logged in User/Customer', 'TonicsCloud',
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
     * @throws Exception
     */
    public function settingsForm (OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Cloud Containers Instance';
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
     *
     * @return string
     * @throws Exception
     * @throws \Throwable
     */
    public function userForm (OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Cloud Containers Instance';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $slug = $data->field_slug;
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";

        $keyValue = $event->getKeyValueInData($data, $data->inputName);

        $foundURLRequiredParam = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams();
        $container = ContainerService::getContainer($foundURLRequiredParam[0]);


        if (isset($container->service_instance_id)) {

            $containers = null;
            db(onGetDB: function (TonicsQuery $db) use (&$containers, $container) {
                $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
                $containerTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINERS);
                $containers = $db->Select("container_name, CONCAT('tc-', $containerTable.slug_id, '.incus') as incus_name")
                    ->From($containerTable)
                    ->Join("$serviceInstanceTable", "$serviceInstanceTable.service_instance_id", "$containerTable.service_instance_id")
                    ->WhereEquals("$containerTable.service_instance_id", $container->service_instance_id)
                    ->WhereNull("$serviceInstanceTable.end_time")
                    ->WhereNull("$containerTable.end_time")
                    ->FetchResult();
            });

            $choiceFrag = '';
            foreach ($containers as $container) {
                $selected = '';
                if ($container->incus_name == $keyValue) {
                    $selected = 'selected';
                }
                $choiceFrag .= <<<HTML
<option $selected title="$container->container_name" value="$container->incus_name">$container->container_name</option>
HTML;

            }

            $frag .= <<<FORM
<div class="form-group margin-top:0">
<select class="default-selector mg-b-plus-1" name="$inputName">
    $choiceFrag
</select>
</div>
FORM;

        }

        $frag .= $event->_bottomHTMLWrapper();

        return $frag;
    }

}