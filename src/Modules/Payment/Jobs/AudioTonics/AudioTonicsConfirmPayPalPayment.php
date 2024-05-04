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

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\MailConfig;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use App\Modules\Payment\Controllers\PaymentSettingsController;
use App\Modules\Payment\EventHandlers\PayPal\HandleAudioTonicsPaymentCaptureCompletedEvent;
use App\Modules\Payment\EventHandlers\AudioTonicsPaymentHandler\AudioTonicsPayPalHandler;
use App\Modules\Payment\Library\AudioTonicsHelper;
use App\Modules\Payment\Library\HandleTonicsPaymentCapture;
use App\Modules\Payment\Library\Helper;
use App\Modules\Payment\Library\PayPalCapturedResponse;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class AudioTonicsConfirmPayPalPayment extends AbstractJobInterface implements JobHandlerInterface
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
                    AudioTonicsHelper::DeliverOrderEmail($purchaseRecord);
                }
            );
        }
    }
}