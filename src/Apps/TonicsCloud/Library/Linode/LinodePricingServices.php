<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Library\Linode;

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class LinodePricingServices
{
    const PermName = 'Akamai';

    public static function priceList(): array
    {
        return [
            'g6-nanode-1' => [
                'service_type' => 'Server',
                'description' => 'Shared 1GB RAM - 1CPU Core - 25GB SSD',
                'price' => [
                    "hourly" => 0.017,
                    "monthly" => 12.0
                ],
                "memory" => 1024,
                "disk" => 25600
            ],
            'g6-standard-1' => [
                'service_type' => 'Server',
                'description' => 'Shared 2GB RAM - 1CPU Core - 50GB SSD',
                'price' => [
                    "hourly" => 0.03,
                    "monthly" => 20
                ],
                "memory" => 2048,
                "disk" => 51200
            ],
            'g6-standard-2' => [
                'service_type' => 'Server',
                'description' => 'Shared 4GB RAM - 2CPU Core - 80GB SSD',
                'price' => [
                    "hourly" => 0.05,
                    "monthly" => 40
                ],
                "memory" => 4096,
                "disk" => 81920
            ],
            'g6-dedicated-2' => [
                'service_type' => 'Server',
                'description' => 'Dedicated 4GB RAM - 2CPU Core - 80GB SSD',
                'price' => [
                    "hourly" => 0.077,
                    "monthly" => 55
                ],
                "memory" => 4096,
                "disk" => 81920
            ],
            'g6-dedicated-4' => [
                'service_type' => 'Server',
                'description' => 'Dedicated 8GB RAM - 4CPU Core - 160GB SSD',
                'price' => [
                    "hourly" => 0.14,
                    "monthly" => 100
                ],
                "memory" => 8192,
                "disk" => 163840
            ],
        ];
    }

    /**
     * @throws \Exception
     */
    public static function insertLinodeDefaultServices(): void
    {
        $providersToInsert = [
            'provider_name' => 'Linode (Akamai)',
            'provider_perm_name' => self::PermName,
        ];

        db(onGetDB: function (TonicsQuery $db) use (&$providersToInsert) {
            $cloudProviderTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_PROVIDER);
            $db->insertOnDuplicate($cloudProviderTable, $providersToInsert, ['provider_name']);

            $providerID = $db->Q()->Select('provider_id')
                ->From($cloudProviderTable)
                ->WhereEquals('provider_perm_name', self::PermName)->FetchFirst()?->provider_id;

            $services = [];
            $cloudServiceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICES);
            foreach (self::priceList() as $key => $value){
                $services[] = [
                    'service_name' => $key,
                    'service_description' => $value['description'],
                    'service_provider_id' => $providerID,
                    'monthly_cap' => $value['price']['monthly'],
                    'hourly_rate' => $value['price']['hourly'],
                    'others' => json_encode($value)
                ];
            }

            $db->Q()->insertOnDuplicate($cloudServiceTable, $services, ['service_description', 'monthly_cap', 'hourly_rate', 'others']);
        });
    }
}