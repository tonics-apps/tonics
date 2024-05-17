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

namespace App\Apps\TonicsCloud\Controllers;

use App\Modules\Payment\Events\TonicsCloud\OnAddTonicsCloudPaymentEvent;
use App\Modules\Payment\Events\TonicsPaymentInterface;

class PaymentController
{

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function RequestFlow(): void
    {
        $paymentHandlerName = url()->getHeaderByKey('PaymentHandlerName');

        /** @var $paymentObject OnAddTonicsCloudPaymentEvent */
        $paymentObject = event()->dispatch(new OnAddTonicsCloudPaymentEvent())->event();
        if ($paymentObject->exist($paymentHandlerName)){
            /** @var $paymentHandler TonicsPaymentInterface */
            $paymentHandler = $paymentObject->getPaymentHandler($paymentHandlerName);
            $paymentHandler->handlePayment();
        } else {
            response()->onError(400, 'No Valid Payment Handler');
        }
    }
}