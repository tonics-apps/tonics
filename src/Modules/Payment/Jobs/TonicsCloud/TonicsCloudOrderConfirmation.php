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

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\MailConfig;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class TonicsCloudOrderConfirmation extends AbstractJobInterface implements JobHandlerInterface
{

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle(): void
    {
        $helper = helper();
        $email = $this->getData()->email;
        $name = $helper->extractNameFromEmail($email);
        $subject = AppConfig::getAppName() . " - Payment Confirmation {Order #{$this->getData()->slug_id}}";
        $messageToSend = view('Modules::Payment/Views/Email/TonicsCloud/payment-verified', [
            'Username' => ucfirst($name),
            'Subject' => $subject,
            'OrderAmount' => $this->getData()->total_price,
            'SlugID' => $this->getData()->slug_id,
            'Email' => $email,
            'OrderDetails' => $this->getData(),
        ], TonicsView::RENDER_CONCATENATE);

        $mail = MailConfig::getMailer();
        $mail->addAddress($email, $name);

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