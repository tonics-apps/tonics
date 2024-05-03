<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Controllers;

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Field\Data\FieldData;
use App\Modules\Payment\Library\TonicsCloudHelper;
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