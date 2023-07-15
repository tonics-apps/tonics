<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\Jobs\TonicsCloud;

use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use App\Modules\Payment\EventHandlers\AudioTonicsPaymentHandler\AudioTonicsPayPalHandler;
use App\Modules\Payment\Library\HandleTonicsPaymentCapture;
use App\Modules\Payment\Library\Helper;
use App\Modules\Payment\Library\PayPalCapturedResponse;
use App\Modules\Payment\Library\TonicsCloudHelper;

class TonicsCloudConfirmPayPalPayment extends AbstractJobInterface implements JobHandlerInterface
{

    public function __construct()
    {
        $this->setJobName('Core_AudioTonicsConfirmPayPalPayment');
        $this->setPriority(Scheduler::PRIORITY_LOW);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle(): void
    {
        $purchaseRecord = $this->getDataAsObject();
        $purchaseRecord->others = json_decode($purchaseRecord->others);
        $response = Helper::PayPalOrderDetails(Helper::PayPalAccessToken(), $purchaseRecord->others->order_id);
        ## Meaning the token not found in cache, we retry
        if (isset($response->error) && $response->error === 'invalid_token') {
            $response = Helper::PayPalOrderDetails(Helper::PayPalAccessToken(), $purchaseRecord->others->order_id);
        }
        $capturedResponse = new PayPalCapturedResponse($response);
        $invoiceID = $capturedResponse->getInvoiceID();
        if ($capturedResponse->isCompleted()) {
            HandleTonicsPaymentCapture::validateTonicsTransactionAndPrepareOrderMail(
                [
                    'invoice_id' => $invoiceID,
                    'total_amount' => $capturedResponse->getTotalAmount(),
                    'currency' => $capturedResponse->getCurrency(),
                    'purchase_record' => $purchaseRecord
                ],
                function ($purchaseRecord, $db){
                    TonicsCloudHelper::UpdateCreditAndSendOrderMail($purchaseRecord, $db);
                }
            );
        }
    }
}