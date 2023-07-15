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

use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class HandleTonicsPaymentCapture
{
    /**
     * If purchase_record is null, we get it ourselves
     * @param array $settings
     * e.g ['invoice_id' => $invoiceID, 'total_amount' => $totalAmount, 'purchase_record' => null, 'currency' => null]
     * @param callable $onSuccess
     * You get the purchaseRecord and DB as the param
     * @return void
     * @throws \Throwable
     */
    public static function validateTonicsTransactionAndPrepareOrderMail(array $settings, callable $onSuccess): void
    {
        $purchaseTable = Tables::getTable(Tables::PURCHASES);
        $customerTable = Tables::getTable(Tables::CUSTOMERS);

        $invoiceID = $settings['invoice_id'] ?? '';
        $purchaseRecord = $settings['purchase_record'] ?? null;
        $currency = $settings['currency'] ?? '';

        try {
            # Get the purchase records by invoiceID if it does not already exist
            if (empty($purchaseRecord)) {
                db(onGetDB: function (TonicsQuery $db) use ($invoiceID, $purchaseTable, &$purchaseRecord, $customerTable) {
                    $select = "total_price, email, $purchaseTable.others, $purchaseTable.slug_id";
                    $purchaseRecord = $db->row(<<<SQL
SELECT $select
FROM $purchaseTable
JOIN $customerTable c ON c.user_id = $purchaseTable.fk_customer_id
WHERE `invoice_id` = ? AND `payment_status` = ?
LIMIT ?
SQL, $invoiceID, 'pending', 1);
                });
            }

            # If it is the currency we are accepting
            # If there is a purchase record
            db(onGetDB: function (TonicsQuery $db) use ($currency, $onSuccess, $settings, $purchaseTable, $invoiceID, &$purchaseRecord) {
                $db->beginTransaction();

                if ($currency === 'USD' && is_object($purchaseRecord)) {
                    $totalAmount = $settings['total_amount'] ?? '';

                    $totalPriceFromDB = $purchaseRecord->total_price;
                    if (isset($purchaseRecord->others->payment_multiplier)) {
                        $totalPriceFromDB = $totalPriceFromDB * $purchaseRecord->others->payment_multiplier;
                    }
                    # Validate the amount
                    # If what user pays is greater or equals to total_price, the payment is valid
                    if (helper()->moneyGreaterOrEqual($totalAmount, $totalPriceFromDB)) {
                        # Change From Pending to Completed
                        $db->FastUpdate($purchaseTable,
                            ['payment_status' => 'completed'],
                            db()->WhereEquals('invoice_id', $invoiceID)->WhereEquals('payment_status', 'pending'));

                        $onSuccess($purchaseRecord, $db);
                    } else {
                        # Decline Purchase
                        $db->FastUpdate($purchaseTable,
                            ['payment_status' => 'declined'],
                            db()->WhereEquals('invoice_id', $invoiceID)->WhereEquals('payment_status', 'pending'));
                    }
                } else {
                    # Decline Purchase
                    $db->FastUpdate($purchaseTable,
                        ['payment_status' => 'declined'],
                        db()->WhereEquals('invoice_id', $invoiceID)->WhereEquals('payment_status', 'pending'));
                }

                $db->commit();
            });

        } catch (\Exception $exception) {
            // Log..
        }
    }
}