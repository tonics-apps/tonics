<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\Schedules\AudioTonics;

use App\Modules\Core\Library\SchedulerSystem\AbstractSchedulerInterface;
use App\Modules\Core\Library\SchedulerSystem\ScheduleHandlerInterface;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use App\Modules\Core\Library\Tables;
use App\Modules\Payment\Controllers\PaymentSettingsController;
use App\Modules\Payment\EventHandlers\PayPal\HandleAudioTonicsPaymentCaptureCompletedEvent;
use App\Modules\Payment\Library\PayPalCapturedResponse;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class ManuallyConfirmAudioTonicsPaymentPayPal extends AbstractSchedulerInterface implements ScheduleHandlerInterface
{

    public function __construct()
    {
        $this->setName('Core_ManuallyConfirmAudioTonicsPaymentPayPal');
        $this->setPriority(Scheduler::PRIORITY_LOW);
        $this->setEvery(Scheduler::everySecond(30));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function handle(): void
    {
        $purchaseTable = Tables::getTable(Tables::PURCHASES);
        $customerTable = Tables::getTable(Tables::CUSTOMERS);
        db( onGetDB: function (TonicsQuery $db) use ($customerTable, $purchaseTable) {
            $purchaseRecord = $db->row(<<<SQL
SELECT total_price, email, $purchaseTable.others, $purchaseTable.slug_id
FROM $purchaseTable
JOIN {$customerTable} c ON c.user_id = $purchaseTable.fk_customer_id
WHERE `payment_status` = ?
LIMIT ?
FOR UPDATE SKIP LOCKED
SQL, 'pending', 1);

            if (is_object($purchaseRecord)){
                $purchaseRecord->others = json_decode($purchaseRecord->others);
                $response = PaymentSettingsController::getOrderDetails(PaymentSettingsController::getAccessToken(), $purchaseRecord->others->order_id);
                ## Meaning the token not found in cache, we retry
                if (isset($response->error) && $response->error === 'invalid_token'){
                    $response = PaymentSettingsController::getOrderDetails(PaymentSettingsController::getAccessToken(), $purchaseRecord->others->order_id);
                }
                $capturedResponse = new PayPalCapturedResponse($response);
                $invoiceID = $capturedResponse->getInvoiceID();
                if ($capturedResponse->isCompleted()){
                    HandleAudioTonicsPaymentCaptureCompletedEvent::validateTonicsTransactionAndPrepareOrderMail(
                        [
                            'invoice_id' => $invoiceID,
                            'total_amount' => $capturedResponse->getTotalAmount(),
                            'currency' => $capturedResponse->getCurrency(),
                            'purchase_record' => $purchaseRecord
                        ]
                    );
                }
            }
        });
    }
}