<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Jobs\Billing;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\MailConfig;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class CloudJobBillingLowCreditNotification extends AbstractJobInterface implements JobHandlerInterface
{

    /**
     * @throws \Throwable
     */
    public function handle(): void
    {
        $email = $this->getData()->Email;
        $name = helper()->extractNameFromEmail($email);
        $subject = AppConfig::getAppName() . ' - Low Credit Notification';
        $messageToSend = view('Apps::TonicsCloud/Views/Email/low-credit-notification', [
            'Username' => ucfirst($name),
            'Subject' => $subject,
            'BillingAddress' => AppConfig::getAppUrl() .  \route('tonicsCloud.billings.setting'),
            'RemainingCredit' => round($this->getData()->RemainingCredit ?? 0, 4),
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