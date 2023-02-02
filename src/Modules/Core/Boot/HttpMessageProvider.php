<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Boot;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Configs\MailConfig;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Media\FileManager\LocalDriver;
use App\Modules\Payment\Controllers\PaymentSettingsController;
use App\Modules\Payment\EventHandlers\TrackPaymentMethods\AudioTonicsPayPalHandler;
use Devsrealm\TonicsContainer\Container;
use Devsrealm\TonicsContainer\Interfaces\ServiceProvider;
use Devsrealm\TonicsRouterSystem\Handler\Router;
use Devsrealm\TonicsTemplateSystem\TonicsView;
use Embera\Embera;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\MessageBodyCollection;
use Genkgo\Mail\Protocol\Smtp\ClientFactory;
use Genkgo\Mail\Transport\EnvelopeFactory;
use Genkgo\Mail\Transport\SmtpTransport;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Symfony\Component\Yaml\Tests\A;

/**
 * Class HttpMessageProvider
 * @package App
 */
class HttpMessageProvider implements ServiceProvider
{

    private Router $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router){
        $this->router = $router;
    }

    /**
     * @param Container $container
     * @throws \Exception
     */
    public function provide(Container $container): void
    {

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        $messageToSend = view('Modules::Core/Views/Emails/email-verification', [
            'Username' => 'Faruq',
            'Email' => 'faruq@exclusivemusicplus.com',
            'Verification_Code' => 55555555555,
        ], TonicsView::RENDER_CONCATENATE);

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = MailConfig::getMailHost();                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;       //Enable SMTP authentication
            $mail->Hostname = MailConfig::getMailHost();
            $mail->Username   = MailConfig::getMailUsername();                     //SMTP username
            $mail->Password   = MailConfig::getMailPassword();                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
            $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            $mail->XMailer = ' ';
            $mail->addCustomHeader('List-unsubscribe', '<mailto:mail@tonics.com>, <https://tonics.app/unsubscribe>');
            $mail->addReplyTo('exclusivemusicplsu@gmail.com', 'Faruq');
            $mail->setFrom(MailConfig::getMailFromAddress(), 'Olayemi Faruq');
            //Recipients
            $mail->addAddress('devsrealmer@gmail.com');               //Name is optional
            //Content
            $mail->isHTML();                                  //Set email format to HTML
            $mail->Subject = 'Here is the subject';
            $mail->Body    = $messageToSend;

            $mail->send();
            echo 'Message has been sent';
        } catch (\Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        exit();
       // dd(helper()->securePass('Horlaehorlaewale@gmail.com'));
     //  dd(AudioTonicsPayPalHandler::getOrderDetails(AudioTonicsPayPalHandler::getAccessToken(), '1PJ72004HS825671N'));
        try {
            $this->getRouter()->dispatchRequestURL();
        } catch (\Exception | \Throwable $e) {
             if ($e->getCode() === 404 ){
                 $redirect_to = $this->tryURLRedirection();
                 $reURL = url()->getRequestURL();
                 if ($redirect_to === false){
                     if (AppConfig::canLog404()){
                         try {
                             db()->Insert(
                                 Tables::getTable(Tables::BROKEN_LINKS),
                                 [
                                     'from' => $reURL,
                                     'to'   => null,
                                 ]
                             );
                         }catch (\Exception $exception){
                             // Log..
                         }
                     }
                 } else {
                     if (isset($redirect_to->to) && !empty($redirect_to->to)){
                         redirect($redirect_to->to, $redirect_to->redirection_type);
                     } else {
                         if (!empty($reURL)){
                             $hit = $redirect_to->hit ?? 1;
                             try {
                                 db()->FastUpdate(
                                     Tables::getTable(Tables::BROKEN_LINKS),
                                     [
                                         '`from`' => $reURL,
                                         '`to`'   => null,
                                         '`hit`'   => ++$hit,
                                     ],
                                     db()->WhereEquals('`from`', $reURL)
                                 );
                             } catch (\Exception $exception){
                                 // Log..
                             }
                         }
                     }
                 }
             }
            if (AppConfig::isProduction()){
                SimpleState::displayErrorMessage($e->getCode(),  $e->getMessage());
            } else {
                SimpleState::displayErrorMessage($e->getCode(),  $e->getMessage() . $e->getTraceAsString());
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function tryURLRedirection():object|bool
    {
        try {
            $table = Tables::getTable(Tables::BROKEN_LINKS);
            $result = db()->Select('*')->From($table)->WhereEquals(table()->pickTable($table, ['from']), url()->getRequestURL())->FetchFirst();
            if (is_object($result)){
                return $result;
            }
        } catch (\Exception $exception){
            // Log..
        }

       return false;
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
}