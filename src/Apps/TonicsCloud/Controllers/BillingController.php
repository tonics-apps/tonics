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

namespace App\Apps\TonicsCloud\Controllers;

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class BillingController
{
    private FieldData $fieldData;

    /**
     * @param FieldData $fieldData
     * @param AbstractDataLayer $abstractDataLayer
     */
    public function __construct(FieldData $fieldData, AbstractDataLayer $abstractDataLayer)
    {
        $this->fieldData = $fieldData;
    }


    /**
     * @throws \Throwable
     */
    public function billing(): void
    {
        view('Apps::TonicsCloud/Views/Billing/index', [
            'SiteURL' => AppConfig::getAppUrl(),
            'Email' => session()::getUserEmail(),
            'TimeZone' => AppConfig::getTimeZone(),
            'FieldItems' => $this->getFieldData()
                ->generateFieldWithFieldSlug(['app-tonicscloud-billing-page'])->getHTMLFrag()
        ]);
    }

    /**
     * If userID is empty, it defaults to the session User ID
     * @param null $userID
     * @return float|null
     * @throws \Exception
     */
    public static function RemainingCredit($userID = null): ?float
    {
        if (empty($userID)){
            $userID = session()::getUserID();
        }
        $data = null;
        db(onGetDB: function (TonicsQuery $db) use ($userID, &$data){
            $creditsTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CREDITS);
            $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
            $serviceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICES);
            $data = $db->row(<<<SQL
SELECT
        c.fk_customer_id,
        c.credit_amount - COALESCE(SUM(CEILING(TIMESTAMPDIFF(MINUTE, si.start_time, IFNULL(si.end_time, NOW())) / 60) * (s.monthly_rate / ?) ), 0) AS remaining_credits
        FROM $creditsTable c
        LEFT JOIN $serviceInstanceTable si ON c.fk_customer_id = si.fk_customer_id
        LEFT JOIN  $serviceTable s ON si.fk_service_id = s.service_id
        WHERE c.fk_customer_id = ?;
SQL, TonicsCloudSettingsController::TotalMonthlyHours(), $userID);
        });

        $remainCredit = $data->remaining_credits ?? null;

        if (empty($remainCredit)) {
            return 0;
        }

        return $remainCredit;
    }

    /**
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->fieldData;
    }

    /**
     * @param FieldData $fieldData
     */
    public function setFieldData(FieldData $fieldData): void
    {
        $this->fieldData = $fieldData;
    }
}