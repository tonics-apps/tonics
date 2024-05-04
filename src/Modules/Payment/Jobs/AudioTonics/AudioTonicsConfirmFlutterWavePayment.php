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

namespace App\Modules\Payment\Jobs\AudioTonics;

use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use App\Modules\Payment\EventHandlers\AudioTonicsPaymentHandler\AudioTonicsFlutterWaveHandler;
use App\Modules\Payment\Library\AudioTonicsHelper;
use App\Modules\Payment\Library\HandleTonicsPaymentCapture;
use App\Modules\Payment\Library\Helper;

class AudioTonicsConfirmFlutterWavePayment extends AbstractJobInterface implements JobHandlerInterface
{

    public function __construct()
    {
        $this->setJobName('Core_AudioTonicsConfirmFlutterWavePayment');
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
                    function ($purchaseRecord, $db){
                        AudioTonicsHelper::DeliverOrderEmail($purchaseRecord);
                    }
                );
            }
        }
    }
}