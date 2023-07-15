<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\Library;

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Configs\MailConfig;
use App\Modules\Core\Library\Tables;
use App\Modules\Payment\Events\OnPurchaseCreate;
use App\Modules\Payment\Jobs\TonicsCloud\TonicsCloudOrderConfirmation;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class TonicsCloudHelper
{
    /**
     * @throws \Throwable
     */
    public static function CapturePaymentDetails(array $purchaseData): array
    {
        $return = [];
        $purchaseDataReturn = null;
        db(onGetDB: function (TonicsQuery $db) use ($purchaseData, &$purchaseDataReturn){
            $purchaseDataReturn = $db->insertReturning(Tables::getTable(Tables::PURCHASES), $purchaseData, Tables::$TABLES[Tables::PURCHASES], 'purchase_id');
        });

        $onPurchaseCreate = new OnPurchaseCreate($purchaseDataReturn);
        event()->dispatch($onPurchaseCreate);

        $mailReplyTo = MailConfig::getMailReplyTo();
        $orderID = $onPurchaseCreate->getSlugID();
        $mailTo = <<<MAILTO
<a href="mailto:$mailReplyTo?subject=Failed To Get Order #$orderID">Contact US</a>
MAILTO;

        $purchaseData['email'] = session()::getUserEmail();
        $purchaseData['slug_id'] = $onPurchaseCreate->getSlugID();
        $return['MESSAGE'] = <<<MESSAGE
<p>Pending Review, please $mailTo if you got stucked.</p>
<span>Please <a href="">Refresh The Page</a> To Buy More Credit</span>
MESSAGE;
        $return['PURCHASE_RECORD'] = $purchaseData;
        return $return;
    }

    /**
     * @param $customerID
     * @return mixed|null
     * @throws \Exception
     */
    public static function GetUserCredit($customerID): mixed
    {
        $credit = null;
        db(onGetDB: function (TonicsQuery $db) use ($customerID, &$credit){
            $creditsTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CREDITS);
            $credit = $db->Q()->Select('*')->From($creditsTable)
                ->WhereEquals('fk_customer_id', $customerID)->FetchFirst();
        });

        return $credit;
    }

    /**
     * @param $customerID
     * @return void
     * @throws \Exception
     */
    public static function CreatCustomerCredit($customerID): void
    {
        db(onGetDB: function (TonicsQuery $db) use ($customerID, &$credit){
            $creditsTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CREDITS);
            $db->Insert($creditsTable, ['fk_customer_id' => $customerID]);
        });
    }

    /**
     * @throws \Throwable
     */
    public static function DeliverOrderEmail($purchaseRecord): void
    {
        # Queue Job For Order Delivery
        $tonicsOrderDeliveryJob = new TonicsCloudOrderConfirmation();
        $tonicsOrderDeliveryJob->setJobName('TonicsCloudOrderConfirmation');
        if (helper()->isJSON($purchaseRecord->others)){
            $purchaseRecord->others = json_decode($purchaseRecord->others);
        }
        $tonicsOrderDeliveryJob->setData($purchaseRecord);
        job()->enqueue($tonicsOrderDeliveryJob);
    }

    /**
     * @throws \Throwable
     */
    public static function UpdateCreditAndSendOrderMail($purchaseRecord, TonicsQuery $db): void
    {
        $creditsTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CREDITS);
        $credits = self::GetUserCredit($purchaseRecord->fk_customer_id);
        if ($credits === null){
            self::CreatCustomerCredit($purchaseRecord->fk_customer_id);
        }

        $db->row(<<<SQL
UPDATE $creditsTable
SET credit_amount = credit_amount + ?
WHERE fk_customer_id = ?;
SQL, $purchaseRecord->total_price, $purchaseRecord->fk_customer_id);

        self::DeliverOrderEmail($purchaseRecord);
    }
}