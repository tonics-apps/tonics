<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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
     */
    public function handle(): void
    {
        $helper = helper();
        $name = $helper->extractNameFromEmail($this->getData()->email);
        $subject = AppConfig::getAppName() . " - Audio Order Delivery {Order #{$this->getData()->slug_id}}";
        $messageToSend = view('Modules::Payment/Views/Email/AudioTonics/ordered-files', [
            'Username' => ucfirst($name),
            'Subject' => $subject,
            'SlugID' => $this->getData()->slug_id,
            'Email' => $this->getData()->email,
            'Files' => (array)$this->getData()->others->downloadables,
            'TotalPrice' => (array)$this->getData()->total_price,
        ], TonicsView::RENDER_CONCATENATE);

        $mail = MailConfig::getMailer();
        $mail->SMTPKeepAlive = true; //SMTP connection will not close after each email sent, reduces SMTP overhead
        $mail->addAddress($this->getData()->email, $name);
        #
        # In case the user registers mail is incorrect, we also send the order details to the PaymentEmailAddress
        #
        if (isset($this->getData()->others->payment_email_address) && $this->getData()->others->payment_email_address !== $this->getData()->email){
            $mail->addAddress($this->getData()->others->payment_email_address, $name);
        }
        $mail->Subject = $subject;
        $mail->msgHTML($messageToSend);

        try {
            $mail->send();
        } catch (\Exception $e) {
            // Log...
            $this->infoMessage('Mailer Error (' . htmlspecialchars($this->getData()->email) . ') ' . $mail->ErrorInfo);
        }
    }
}