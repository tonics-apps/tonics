<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Customer\Jobs;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\MailConfig;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class CustomerRegistrationVerificationCodeEmail extends AbstractJobInterface implements JobHandlerInterface
{

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $messageToSend = view('Modules::Core/Views/Emails/email-verification', [
            'Username' => $this->getData()->user_name,
            'Email' => $this->getData()->email,
            'Verification_Code' => $this->getData()->verification->verification_code,
        ], TonicsView::RENDER_CONCATENATE);

        $mail = MailConfig::getMailer();
        $mail->addAddress($this->getData()->email, $this->getData()->user_name);
        $mail->Subject = AppConfig::getAppName() . ' - Verify Your Email';
        $mail->msgHTML($messageToSend);

        $mail->send();
        $mail->clearAddresses();
        $mail->clearAttachments();

    }
}