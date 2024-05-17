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

namespace App\Apps\TonicsCloud\Library\Linode;

use App\Apps\TonicsCloud\Controllers\TonicsCloudSettingsController;

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