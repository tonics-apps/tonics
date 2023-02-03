<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\EventHandlers\PayPal;

use App\Modules\Core\Library\Tables;
use App\Modules\Payment\Controllers\PaymentSettingsController;
use App\Modules\Payment\Events\PayPal\OnAddPayPalWebHookEvent;
use App\Modules\Payment\Events\PayPal\PayPalWebHookEventInterface;
use App\Modules\Payment\Jobs\AudioTonics\AudioTonicsOrderDeliveryEmail;
use App\Modules\Payment\Library\PayPalPaymentCapturedCompletedWebHookResponse;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class HandleAudioTonicsPaymentCaptureCompletedEvent implements HandlerInterface, PayPalWebHookEventInterface
{

    public function handleEvent(object $event): void
    {
        /** @var $event OnAddPayPalWebHookEvent */
        $event->addWebHookEventHandler($this);
    }

    public function EventType(): string
    {
        return OnAddPayPalWebHookEvent::EventType_PaymentCapturedCompleted;
    }

    public function TonicsSolutionType(): string
    {
        return PaymentSettingsController::TonicsSolution_AudioTonics;
    }

    /**
     * @param OnAddPayPalWebHookEvent $payPalWebHookEvent
     * @return void
     * @throws \Exception
     */
    public function HandleWebHookEvent(OnAddPayPalWebHookEvent $payPalWebHookEvent): void
    {
        $capturedResponse = new PayPalPaymentCapturedCompletedWebHookResponse($payPalWebHookEvent->getWebHookData());
        # Only Handle AudioTonics Order
        $invoiceID = $capturedResponse->getInvoiceID();

        if ($capturedResponse->isCompleted()) {
            self::validateTonicsTransactionAndPrepareOrderMail(
                ['invoice_id' => $invoiceID, 'total_amount' => $capturedResponse->getTotalAmount(), 'currency' => $capturedResponse->getCurrency() ]
            );
        }

        response()->onSuccess([], 'success');
    }

    /**
     * If purchase_record is null, we get it ourselves
     * @param array $settings
     * e.g ['invoice_id' => $invoiceID, 'total_amount' => $totalAmount, 'purchase_record' => null, 'currency' => null]
     * @return void
     */
    public static function validateTonicsTransactionAndPrepareOrderMail(array $settings): void
    {
        $purchaseTable = Tables::getTable(Tables::PURCHASES);
        $customerTable = Tables::getTable(Tables::CUSTOMERS);

        $invoiceID = $settings['invoice_id'] ?? '';
        $totalAmount = $settings['total_amount'] ?? '';
        $purchaseRecord = $settings['purchase_record'] ?? null;
        $currency = $settings['currency'] ?? '';

        try {
            # Get the purchase records by invoiceID if it does not already exist
            if (empty($purchaseRecord)) {
                db(onGetDB: function (TonicsQuery $db) use ($purchaseTable, $customerTable, $invoiceID, &$purchaseRecord) {
                    $select = "total_price, email, $purchaseTable.others, $purchaseTable.slug_id";
                    $purchaseRecord = $db->row(<<<SQL
SELECT $select
FROM $purchaseTable
JOIN {$customerTable} c ON c.user_id = $purchaseTable.fk_customer_id
WHERE `invoice_id` = ? AND `payment_status` = ?
LIMIT ?
FOR UPDATE SKIP LOCKED
SQL, $invoiceID, 'pending', 1);
                });
            }

            if (str_starts_with($invoiceID, PaymentSettingsController::TonicsSolution_AudioTonics)) {
                # If it is the currency we are accepting
                # If there is a purchase record
                if ($currency === 'USD' && is_object($purchaseRecord))  {
                    # Validate the amount
                    # If what user pays is greater or equals to total_price, the payment is valid
                    if (helper()->moneyGreaterOrEqual($totalAmount, $purchaseRecord->total_price)) {
                        # Change From Pending to Completed
                        db(onGetDB: function (TonicsQuery $db) use ($purchaseTable, $invoiceID) {
                            $db->FastUpdate($purchaseTable,
                                ['payment_status' => 'completed'],
                                db()->WhereEquals('invoice_id', $invoiceID)->WhereEquals('payment_status', 'pending'));
                        });

                        # Queue Job For Order Delivery
                        $tonicsOrderDeliveryJob = new AudioTonicsOrderDeliveryEmail();
                        $tonicsOrderDeliveryJob->setJobName('AudioTonicsOrderDeliveryEmail');
                        if (helper()->isJSON($purchaseRecord->others)){
                            $purchaseRecord->others = json_decode($purchaseRecord->others);
                        }
                        $tonicsOrderDeliveryJob->setData($purchaseRecord);
                        job()->enqueue($tonicsOrderDeliveryJob);
                    } else {
                        # Decline Purchase
                        db(onGetDB: function (TonicsQuery $db) use ($purchaseTable, $invoiceID) {
                            $db->FastUpdate($purchaseTable,
                                ['payment_status' => 'declined'],
                                db()->WhereEquals('invoice_id', $invoiceID)->WhereEquals('payment_status', 'pending'));
                        });
                    }
                }
            }

        } catch (\Exception $exception) {
            // Log..
        }
    }
}