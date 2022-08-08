<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library\Mailer;

use PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{

    private array $mail = [];

    public function __construct($isSmtp)
    {
        /**
         * When the is_smtp is true then we use the smtp credentials to send email,
         * If is_smtp is false (thus, localhost), then it means we wanna use a mailserver has a relay to another mail server somewhere
         */
        $this->mail['is_smtp'] = $isSmtp; // This determines if we should use postfix (localhost) or smtp(remote)
        $this->mail['host'] = config('mail.mailers.smtp.host');
        $this->mail['username'] = config('mail.mailers.smtp.username');
        $this->mail['password'] = config('mail.mailers.smtp.password');
        $this->mail['port'] = config('mail.mailers.smtp.port');
        $this->mail['encryption'] = config('mail.mailers.smtp.encryption');
        $this->mail['app_name'] = config('app.name');
    }

    /**
     * @return Mailer
     * This initializes the PHP Mailer
     */
    public static function init($isSmtp): Mailer
    {
        return new Mailer($isSmtp);
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function subject(string $subject): static
    {
        $this->mail['subject'] = $subject;
        return $this;
    }

    /**
     * If the $from is empty, we default to the users general setting "from"
     * @param string $from
     * @return $this
     */
    public function from(string $from): static
    {
        $this->mail['from'] = $from;
        return $this;
    }

    /**
     * This prepares the mailer and configure the necessary mailer configuration
     * @throws Exception
     */
    public function prepareMailer(): PHPMailer\PHPMailer
    {
        $mail = new PHPMailer\PHPMailer(); // create a new instance

        if ($this->mail['is_smtp']) {                                   // If true, then user wanna send through smtp
            $mail->isSMTP();                                            // Send using SMTP
            $mail->SMTPAuth = true;                                    // Enable SMTP authentication
            $mail->Username = $this->mail['username'];
            $mail->Password = $this->mail['password'];
            $mail->Host = $this->mail['host'];
            $mail->Port = $this->mail['port'];
        } else { // If false, then user wanna send through localhost
            $mail->isMail();                                            // Send using Mail
            $mail->Host = "localhost";
            $mail->Port = 25;
        }
        $mail->CharSet = 'utf-8';
        $mail->Subject = $this->mail['subject'];
        $mail->SetFrom($this->mail['from'], $this->mail['app_name']);

        return $mail;
    }

}
