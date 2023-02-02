<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Jobs;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\MailConfig;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class ForgotPasswordEmail extends AbstractJobInterface implements JobHandlerInterface
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

/*        $message = (new MessageBodyCollection())
            ->withHtml($messageToSend)
            ->createMessage()
            ->withHeader(new Subject(AppConfig::getAppName() . ' - Verify Your Email'))
            ->withHeader(From::fromEmailAddress(MailConfig::getMailFromAddress()))
            ->withHeader(To::fromSingleRecipient($this->getData()->email, $this->getData()->user_name));

        $transport = new SmtpTransport(
            ClientFactory::fromString(MailConfig::getMailDataSource())->newClient(),
            EnvelopeFactory::useExtractedHeader()
        );

        $transport->send($message);*/
    }
}