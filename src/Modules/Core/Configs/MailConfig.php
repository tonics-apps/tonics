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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailConfig
{
    private static ?PHPMailer $PHPMailer = null;

    public static function getMailMailer(): string
    {
        return env('MAIL_MAILER');
    }

    public static function getMailHost(): string
    {
        return env('MAIL_HOST');
    }

    public static function getMailPort(): string
    {
        return env('MAIL_PORT');
    }

    public static function getMailUsername(): string
    {
        return env('MAIL_USERNAME');
    }

    public static function getMailPassword(): string
    {
        return env('MAIL_PASSWORD');
    }

    public static function getMailEncryption(): string
    {
        return env('MAIL_ENCRYPTION');
    }

    public static function getMailFromAddress(): string
    {
        return env('MAIL_FROM_ADDRESS');
    }

    public static function getMailReplyTo(): string
    {
        return env('MAIL_REPLY_TO');
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
            $mail->setFrom(MailConfig::getMailFromAddress(),  ucfirst($helper->extractNameFromEmail(MailConfig::getMailFromAddress())));
            //Content
            $mail->isHTML();
        } catch (\Exception $e) {
            // Log..
          //  echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        return $mail;

    }

/*    public static function getMailDataSource(
        string|null $smtp = null,
        string|null $userName = null,
        string|null $password = null,
        string|null $host = null,
        int|null $port = null,
    ): string
    {
        if (!$smtp){ $smtp = 'smtp-starttls'; }
        if (!$port){ $port = self::getMailPort(); }
        if (!$host){ $host = self::getMailHost(); }
        if (!$userName){ $userName = self::getMailUsername(); }
        if (!$password){ $password = self::getMailPassword(); }

        return "$smtp://" . str_replace('@', '%40', $userName) . ':' .
            $password . '@' . $host . ':' . $port;
    }*/
}