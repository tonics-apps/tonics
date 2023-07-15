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

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use App\Modules\Payment\EventHandlers\AudioTonicsPaymentHandler\AudioTonicsFlutterWaveHandler;
use App\Modules\Payment\Library\HandleTonicsPaymentCapture;
use App\Modules\Payment\Library\Helper;
use App\Modules\Payment\Library\TonicsCloudHelper;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class TonicsCloudConfirmFlutterWavePayment extends AbstractJobInterface implements JobHandlerInterface
{

    public function __construct()
    {
        $this->setJobName('Core_TonicsCloudConfirmFlutterWavePayment');
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
        $response = Helper::FlutterWaveOrderDetails(Helper::FlutterWaveSecretKey(), $purchaseRecord->others->transaction_id);
        $invoiceID = $purchaseRecord->others->invoice_id;
        if (isset($response->data)){
            if ($response->data->status === 'successful'){
                HandleTonicsPaymentCapture::validateTonicsTransactionAndPrepareOrderMail(
                    [
                        'invoice_id' => $invoiceID,
                        'total_amount' => $response->data->amount ?? '',
                        'currency' => $response->data->currency ?? '',
                        'purchase_record' => $purchaseRecord
                    ],
                    function ($purchaseRecord, TonicsQuery $db) use ($response){
                        TonicsCloudHelper::UpdateCreditAndSendOrderMail($purchaseRecord, $db);
                    }
                );
            }
        }
    }
}