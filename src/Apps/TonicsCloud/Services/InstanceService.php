<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Apps\TonicsCloud\Services;

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Throwable;

class InstanceService extends TonicsCloudAbstractService
{

    /**
     * @return array[]
     */
    public static function DataTableHeaders (): array
    {
        return [
            [
                'type'  => '', 'slug' => TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::' . 'service_instance_status',
                'title' => 'Status', 'minmax' => '40px, .4fr', 'td' => 'service_instance_status',
            ],

            ['type' => '', 'hide' => true, 'slug' => TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::' . 'provider_instance_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'provider_instance_id'],
            ['type' => '', 'hide' => true, 'slug' => TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::' . 'service_instance_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'service_instance_id'],

            [
                'type'        => 'select', 'slug' => TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::' . 'service_instance_status_action',
                'select_data' => 'Start, ShutDown, Reboot, Terminate', 'desc' => 'Signal Command',
                'title'       => 'Sig', 'minmax' => '40px, .4fr', 'td' => 'service_instance_status_action',
            ],

            [
                'type'   => '',
                'slug'   => TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::' . 'service_instance_name',
                'title'  => 'Instance', 'desc' => 'Name of the instance',
                'minmax' => '45px, .4fr', 'td' => 'service_instance_name',
            ],

            ['type' => '', 'slug' => TonicsCloudActivator::TONICS_CLOUD_SERVICES . '::' . 'service_description', 'title' => 'PLan', 'desc' => 'Current Plan', 'minmax' => '50px, .5fr', 'td' => 'service_description'],
        ];
    }

    /**
     * @param $providerInstanceID
     *
     * @return string
     */
    public static function EditLinkColumn ($providerInstanceID = null): string
    {
        if ($providerInstanceID) {
            return "/customer/tonics_cloud/instances/$providerInstanceID/edit";
        }
        return 'CONCAT("/customer/tonics_cloud/instances/", provider_instance_id, "/edit" ) as _edit_link';
    }

    /**
     * Settings can contain
     * - `instance_id` (optional)- if this is empty, ensure `user_id` is not empty, meaning, it would retrieve instances for the user_id
     * - `column` (optional) - column to check against, defaults to `provider_instance_id`
     * - `user_id` (optional)
     * - `fetch_all` (optional) - boolean, defaults to false
     *
     * @param array $settings
     *
     * @return mixed|null
     * @throws Throwable
     */
    public static function GetServiceInstances (array $settings): mixed
    {
        $serviceInstances = null;
        db(onGetDB: function (TonicsQuery $db) use ($settings, &$serviceInstances) {

            $instanceID = $settings['instance_id'] ?? '';
            $column = $settings['column'] ?? 'provider_instance_id';
            $userID = $settings['user_id'] ?? '';
            $fetchAll = $settings['fetch_all'] ?? false;

            $serviceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICES);
            $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
            $col = self::EditLinkColumn();
            $select = "service_instance_id, provider_instance_id, {$col}, service_instance_name, service_description, 
            service_instance_status, fk_provider_id, fk_service_id, fk_customer_id, start_time, end_time, $serviceInstanceTable.others";
            $col = table()->pick([TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES) => [$column]]);

            $db->Select($select)
                ->From($serviceInstanceTable)
                ->Join("$serviceTable", "$serviceInstanceTable.fk_service_id", "$serviceTable.service_id")
                ->when($userID, function (TonicsQuery $db) use ($serviceInstanceTable, $userID) {
                    $customerTable = Tables::getTable(Tables::CUSTOMERS);
                    $db->Join($customerTable, "$customerTable.user_id", "$serviceInstanceTable.fk_customer_id");
                    $db->WhereEquals('fk_customer_id', $userID);
                })
                ->when($instanceID, function (TonicsQuery $db) use ($col, $instanceID) {
                    $db->WhereEquals($col, $instanceID);
                })
                ->WhereNull('end_time');

            if ($fetchAll) {
                $serviceInstances = $db->FetchResult();
            } else {
                $serviceInstances = $db->FetchFirst();
            }

        });

        return $serviceInstances;
    }
}