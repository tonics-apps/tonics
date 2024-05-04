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
use Devsrealm\TonicsTemplateSystem\TonicsView;

class AudioTonicsOrderDeliveryEmail extends AbstractJobInterface implements JobHandlerInterface
{

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle(): void
    {
        $appURL = AppConfig::getAppUrl();
        $helper = helper();
        $email = $this->getData()->email;
        $name = $helper->extractNameFromEmail($email);
        $subject = AppConfig::getAppName() . " - Audio Order Delivery {Order #{$this->getData()->slug_id}}";
        $messageToSend = view('Modules::Payment/Views/Email/AudioTonics/ordered-files', [
            'Username' => ucfirst($name),
            'Subject' => $subject,
            'SlugID' => $this->getData()->slug_id,
            'Email' => $email,
            'OrderDetails' => $this->getData(),
            'OrderDetailsURL' => $appURL . route('customer.order.audiotonics.details', ['slug_id' => $this->getData()->slug_id]),
            'ForgetPasswordLink' => $appURL . route('customer.password.request'),
        ], TonicsView::RENDER_CONCATENATE);

        $mail = MailConfig::getMailer();
        $mail->addAddress($email, $name);
        #
        # In case the user registers mail is incorrect, we also send the order details to the PaymentEmailAddress
        #
        if ((isset($this->getData()->others->payment_email_address) && !empty($this->getData()->others->payment_email_address))
            && $this->getData()->others->payment_email_address !== $email){
            $mail->addAddress($this->getData()->others->payment_email_address, $name);
        }

        $mail->Subject = $subject;
        $mail->msgHTML($messageToSend);

        try {
            $mail->send();
            $mail->clearAddresses();
            $mail->clearAttachments();
        } catch (\Exception $e) {
            // Log...
            $this->infoMessage('Mailer Error (' . htmlspecialchars($email) . ') ' . $mail->ErrorInfo);
        }
    }
}