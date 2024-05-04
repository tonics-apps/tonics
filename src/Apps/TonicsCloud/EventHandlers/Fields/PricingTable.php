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

use App\Apps\TonicsCloud\Controllers\TonicsCloudSettingsController;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class PricingTable implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('PricingTable', 'Cloud Instance Pricing Table', 'TonicsCloud',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event){
                return $this->userForm($event, $data);
            },
            handleViewProcessing: function (){}
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'TonicsCloud PricingTable';
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $moreSettings = $event->generateMoreSettingsFrag($data);
        $frag .= <<<FORM
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Field Name
            <input id="fieldName-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'TonicsCloud PricingTable';
        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $frag .= $this->getPricingTableFrag($event, $data);

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @return string
     * @throws \Exception
     */
    private function getPricingTableFrag(OnFieldMetaBox $event, $data): string
    {
        $providerName = TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::CloudServerIntegrationType);
        if (isset(getPostData()['others'])) {
            $others = json_decode(getPostData()['others']);
            if (isset($others->serverHandlerName)) {
                $providerName = $others->serverHandlerName;
            }
        }

        $services = null;

        $tableFrag = <<<FRAG
<style>
input[type='radio'] {
    accent-color: #000000;
}
</style>
<section class="dataTable disable-select owl" data-event-click="true" data-event-dblclick="true" data-event-scroll-bottom="true">
    <table id="dt" style="grid-template-columns: minmax(150px, 1fr) minmax(300px, 1.6fr) minmax(150px, 1fr);">
            <thead>
            <tr>
                    <th title="Plan" data-type="" data-title="Name" data-slug="name" data-minmax="300px, 1.6fr" data-td="name">Plan</th>
                    <th title="Description" data-type="" data-title="Description" data-slug="description" data-minmax="150px, 1fr" data-td="description">Monthly</th>
                    <th title="Type" data-type="" data-title="Type" data-slug="type" data-minmax="150px, 1fr" data-td="type">Hourly</th>
            </tr>
            </thead>
            <tbody class="max-height:300px overflow-x:auto">
FRAG;


        db( onGetDB: function (TonicsQuery $db) use ($providerName, &$services){

            $tcs = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICES);
            $tcp = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_PROVIDER);

            $services = $db->Select('tcs.service_id, tcs.service_name, tcs.service_description, tcs.monthly_rate')
                ->From("$tcs tcs")
                ->Join("$tcp tcp", 'tcs.service_provider_id', 'tcp.provider_id')
                ->WhereEquals('tcp.provider_perm_name', $providerName)
                ->FetchResult();
        });

        $trFrag = '';
        if (is_array($services)){
            $servicePlanValue =  $event->getKeyValueInData($data, 'fk_service_id');
            foreach ($services as $service){
                $hourly = $service->monthly_rate/TonicsCloudSettingsController::TotalMonthlyHours();
                $hourly = round($hourly, 4);
                if ($servicePlanValue === $service->service_id){
                    $trFrag .=<<<Frag
        <tr class="disable-select">
            <td tabindex="-1" style="opacity: 50%;">
                <label aria-label="$service->service_description" class="d:flex flex-gap align-items:center">
                    <div aria-label="This is your current plan" style="padding: 5px; border: 2px solid black;"><span>Current Plan</span></div>
                    $service->service_description
                </label>
            </td>
            <td tabindex="-1" style="opacity: 50%;">$$service->monthly_rate</td>
            <td tabindex="-1" style="opacity: 50%;">$$hourly</td>  
        </tr>
Frag;
                    continue;
                }

                $trFrag .=<<<Frag
        <tr class="">
            <td tabindex="-1">
                <label aria-label="$service->service_description" class="d:flex flex-gap">
                    <input type="radio"  name="service_plan" role="radio" value="$service->service_id">$service->service_description
                </label>
            </td>
            <td tabindex="-1">$$service->monthly_rate</td>
            <td tabindex="-1">$$hourly</td>  
        </tr>
Frag;
            }
        }

        $tableFrag .= $trFrag;
        $tableFrag .= <<<CLOSE
            </tbody>
    </table>
</section>
CLOSE;

        return $tableFrag;
    }
}