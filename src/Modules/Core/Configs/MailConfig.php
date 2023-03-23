<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Configs;

use App\Modules\Core\Controllers\CoreSettingsController;
use PHPMailer\PHPMailer\PHPMailer;

class MailConfig
{
    private static ?PHPMailer $PHPMailer = null;

    /**
     * @throws \Exception
     */
    public static function getMailMailer(): string
    {
        return CoreSettingsController::getSettingsValue(CoreSettingsController::Mail_Mailer, env('MAIL_MAILER'));
    }

    /**
     * @throws \Exception
     */
    public static function getMailHost(): string
    {
        return CoreSettingsController::getSettingsValue(CoreSettingsController::Mail_MailHost, env('MAIL_HOST'));
    }

    /**
     * @throws \Exception
     */
    public static function getMailPort(): string
    {
        return CoreSettingsController::getSettingsValue(CoreSettingsController::Mail_MailPort, env('MAIL_PORT'));
    }

    /**
     * @throws \Exception
     */
    public static function getMailUsername(): string
    {
        return CoreSettingsController::getSettingsValue(CoreSettingsController::Mail_MailUsername, env('MAIL_USERNAME'));
    }

    /**
     * @throws \Exception
     */
    public static function getMailPassword(): string
    {
        return CoreSettingsController::getSettingsValue(CoreSettingsController::Mail_MailPassword, env('MAIL_PASSWORD'));
    }

    /**
     * @throws \Exception
     */
    public static function getMailEncryption(): string
    {
        return CoreSettingsController::getSettingsValue(CoreSettingsController::Mail_MailEncryption, env('MAIL_ENCRYPTION'));
    }

    /**
     * @throws \Exception
     */
    public static function getMailFromAddress(): string
    {
        return CoreSettingsController::getSettingsValue(CoreSettingsController::Mail_MailFromAddress, env('MAIL_FROM_ADDRESS'));
    }

    /**
     * @throws \Exception
     */
    public static function getMailReplyTo(): string
    {
        return CoreSettingsController::getSettingsValue(CoreSettingsController::Mail_MailReplyTo, env('MAIL_REPLY_TO'));
    }

    /**
     * @return PHPMailer|null
     * @throws \Exception
     */
    public static function getMailer(): ?PHPMailer
    {
        if (!self::$PHPMailer) {
            self::$PHPMailer = new PHPMailer(true);
        }
        $helper = helper();
        $mail = self::$PHPMailer;
        try {
            //Server settings
           //  $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->isSMTP();
            $mail->Host       = MailConfig::getMailHost();
            $mail->SMTPAuth   = true;
            $mail->Hostname = MailConfig::getMailHost();
            $mail->Username   = MailConfig::getMailUsername();
            $mail->Password   = MailConfig::getMailPassword();
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            $mail->XMailer = ' ';
           // $mail->addCustomHeader('List-unsubscribe', '<mailto:mail@tonics.com>, <https://tonics.app/unsubscribe>');
            $mail->addReplyTo(MailConfig::getMailReplyTo(), ucfirst($helper->extractNameFromEmail(MailConfig::getMailReplyTo())));
            $mail->setFrom(MailConfig::getMailFromAddress(),  ucfirst($helper->extractNameFromEmail(MailConfig::getMailFromAddress())) . ' From ' . AppConfig::getAppName());
            //Content
            $mail->isHTML();
        } catch (\Exception $e) {
            // Log..
          //  echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        return $mail;

    }
}