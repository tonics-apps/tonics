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