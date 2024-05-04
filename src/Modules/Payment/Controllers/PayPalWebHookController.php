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

namespace App\Modules\Payment\Controllers;

use App\Modules\Payment\Events\PayPal\OnAddPayPalWebHookEvent;
use App\Modules\Payment\Library\Helper;

class PayPalWebHookController
{
    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handleWebHook(): void
    {
        $entityBody = request()->getEntityBody();
        if (helper()->isJSON($entityBody)){
            $webhook = json_decode($entityBody);
            if (Helper::PayPalVerifyWebHookSignature($webhook)){
                $eventType = $webhook->event_type;
                /** @var $webHookEventObject OnAddPayPalWebHookEvent */
                $payPalWebHookEventObject = new OnAddPayPalWebHookEvent();
                $payPalWebHookEventObject->setWebHookData($webhook);
                $webHookEventObject = event()->dispatch($payPalWebHookEventObject)->event();
                $webHookEventObject->handleWebHookEvent($eventType);

                response()->onSuccess([], 'On Success');
            }
        }

        response()->onError(400);
    }
}