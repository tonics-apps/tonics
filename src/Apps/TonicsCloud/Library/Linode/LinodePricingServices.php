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

use App\Apps\TonicsCloud\Controllers\TonicsCloudSettingsController;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class LinodePricingServices
{
    const DisplayName = 'Linode (Akamai)';
    const PermName = 'Akamai';

    /**
     * @throws \Exception
     */
    public static function priceList(): array
    {
        $prices = TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::LinodePriceList);
        if (helper()->isJSON($prices)) {
            $prices = json_decode($prices, true);
        } else {
            $prices = [
                'g6-nanode-1' => [
                    'service_type' => 'Server',
                    'description' => 'Shared 1GB RAM - 1CPU Core - 25GB SSD',
                    'price' => [
                        "monthly" => 12.0
                    ],
                    "memory" => 1024,
                    "disk" => 25600
                ],
                'g6-standard-1' => [
                    'service_type' => 'Server',
                    'description' => 'Shared 2GB RAM - 1CPU Core - 50GB SSD',
                    'price' => [
                        "monthly" => 20
                    ],
                    "memory" => 2048,
                    "disk" => 51200
                ],
                'g6-standard-2' => [
                    'service_type' => 'Server',
                    'description' => 'Shared 4GB RAM - 2CPU Core - 80GB SSD',
                    'price' => [
                        "monthly" => 40,
                    ],
                    "memory" => 4096,
                    "disk" => 81920
                ],
                'g6-dedicated-2' => [
                    'service_type' => 'Server',
                    'description' => 'Dedicated 4GB RAM - 2CPU Core - 80GB SSD',
                    'price' => [
                        "monthly" => 55,
                    ],
                    "memory" => 4096,
                    "disk" => 81920
                ],
                'g6-dedicated-4' => [
                    'service_type' => 'Server',
                    'description' => 'Dedicated 8GB RAM - 4CPU Core - 160GB SSD',
                    'price' => [
                        "monthly" => 100,
                    ],
                    "memory" => 8192,
                    "disk" => 163840
                ],
            ];
        }

        return $prices;
    }
}