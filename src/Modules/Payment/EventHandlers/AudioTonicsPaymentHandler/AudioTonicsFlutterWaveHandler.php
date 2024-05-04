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

namespace App\Modules\Payment\EventHandlers\AudioTonicsPaymentHandler;

use App\Modules\Payment\Controllers\PaymentSettingsController;
use App\Modules\Payment\Events\AudioTonics\OnAddTrackPaymentEvent;
use App\Modules\Payment\Events\TonicsPaymentInterface;
use App\Modules\Payment\Jobs\AudioTonics\AudioTonicsConfirmFlutterWavePayment;
use App\Modules\Payment\Library\AudioTonicsHelper;
use App\Modules\Payment\Library\Helper;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class AudioTonicsFlutterWaveHandler implements HandlerInterface, TonicsPaymentInterface
{
    const Query_ClientCredentials = 'ClientPaymentCredentials';
    const Query_GenerateInvoiceID = 'GenerateInvoiceID';
    const Query_CapturedPaymentDetails = 'CapturedPaymentDetails';

    public function handleEvent(object $event): void
    {
        /** @var $event OnAddTrackPaymentEvent */
        $event->addPaymentHandler($this);
    }

    public function name(): string
    {
        return 'AudioTonicsFlutterWaveHandler';
    }

    /**
     * @throws \Exception
     */
    public function enabled(): bool
    {
        return PaymentSettingsController::isEnabled(PaymentSettingsController::FlutterWave_Enabled);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handlePayment(): void
    {
        $queryType = url()->getHeaderByKey('PaymentQueryType');
        if ($queryType === self::Query_GenerateInvoiceID) {
            $this->generateInvoiceID();
            return;
        }

        if ($queryType === self::Query_ClientCredentials) {
            response()->onSuccess(Helper::FlutterWavePublicKey());
        }

        if ($queryType === self::Query_CapturedPaymentDetails) {
            try {
                $body = url()->getEntityBody();
                $body = json_decode($body);

                $data = AudioTonicsHelper::CapturePaymentDetails($body, function ($customerData, $purchaseInfo, $cartItemsSlugID) use ($body){
                    return [
                        'fk_customer_id' => $customerData->user_id,
                        'total_price' => $purchaseInfo['totalPrice'] ?? 0,
                        'others' => json_encode([
                            'downloadables' => $purchaseInfo['downloadables'] ?? [],
                            'payment_email_address' => (isset($body->orderData->customer->email)) ? $body->orderData->customer->email : '',
                            'itemIds' => $cartItemsSlugID,
                            'invoice_id' => $body->invoice_id,
                            'transaction_id' => $body->orderData->transaction_id,
                            'tx_ref' => $body->orderData->tx_ref,
                            'flw_ref' => $body->orderData->flw_ref,
                            'payment_method' => 'TonicsFlutterWave',
                            'tonics_solution' => PaymentSettingsController::TonicsSolution_AudioTonics
                        ]),
                    ];
                });
                if (isset($data['PURCHASE_RECORD'])){
                    $audioTonicsConfirmFlutterWavePayment = new AudioTonicsConfirmFlutterWavePayment();
                    $audioTonicsConfirmFlutterWavePayment->setData($data['PURCHASE_RECORD']);
                    job()->enqueue($audioTonicsConfirmFlutterWavePayment);
                }
                response()->onSuccess(['email' => $data['CHECKOUT_EMAIL']], $data['MESSAGE']);
            } catch (\Exception $exception) {
                response()->onError(400, $exception->getMessage());
                // Log..
            } catch (\Throwable $exception) {
                response()->onError(400, $exception->getMessage());
                // Log..
            }
        }
    }

    /**
     * @throws \Exception|\Throwable
     */
    public function generateInvoiceID(): void
    {
        response()->onSuccess(uniqid('AudioTonics_', true));
    }
}