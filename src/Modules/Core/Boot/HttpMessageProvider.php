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
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Payment\Events\PayPal\OnAddPayPalWebHookEvent;
use Devsrealm\TonicsContainer\Container;
use Devsrealm\TonicsContainer\Interfaces\ServiceProvider;
use Devsrealm\TonicsRouterSystem\Handler\Router;

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
        $json = <<<JSON
{
  "id": "WH-12P352976M872801U-4VG268328G5994910",
  "create_time": "2023-01-28T09:55:18.338Z",
  "resource_type": "capture",
  "event_type": "PAYMENT.CAPTURE.COMPLETED",
  "summary": "Payment completed for $ 60.0 USD",
  "resource": {
    "amount": {
      "value": "160.00",
      "currency_code": "USD"
    },
    "seller_protection": {
      "dispute_categories": [
        "ITEM_NOT_RECEIVED",
        "UNAUTHORIZED_TRANSACTION"
      ],
      "status": "ELIGIBLE"
    },
    "supplementary_data": {
      "related_ids": {
        "order_id": "08F678773R544881S"
      }
    },
    "update_time": "2023-01-28T09:55:14Z",
    "create_time": "2023-01-28T09:55:14Z",
    "final_capture": true,
    "seller_receivable_breakdown": {
      "paypal_fee": {
        "value": "2.58",
        "currency_code": "USD"
      },
      "gross_amount": {
        "value": "60.00",
        "currency_code": "USD"
      },
      "net_amount": {
        "value": "57.42",
        "currency_code": "USD"
      }
    },
    "invoice_id": "AudioTonics_63dc7af567af00.03646774",
    "links": [
      {
        "method": "GET",
        "rel": "self",
        "href": "https://api.sandbox.paypal.com/v2/payments/captures/8TP206213F3834453"
      },
      {
        "method": "POST",
        "rel": "refund",
        "href": "https://api.sandbox.paypal.com/v2/payments/captures/8TP206213F3834453/refund"
      },
      {
        "method": "GET",
        "rel": "up",
        "href": "https://api.sandbox.paypal.com/v2/checkout/orders/08F678773R544881S"
      }
    ],
    "id": "8TP206213F3834453",
    "status": "COMPLETED"
  },
  "status": "SUCCESS",
  "transmissions": [
    {
      "webhook_url": "https://tonics.eu-1.sharedwithexpose.com/payment/paypal_web_hook_endpoint",
      "http_status": 200,
      "reason_phrase": "HTTP/1.1 200 Connection established",
      "response_headers": {
        "Transfer-Encoding": "chunked",
        "Server": "nginx/1.18.0",
        "Access-Control-Allow-Origin": "https://cringe.com",
        "Access-Control-Allow-Methods": "GET, POST, PATCH, PUT, DELETE, OPTIONS",
        "Access-Control-Allow-Credentials": "true",
        "Connection": "keep-alive",
        "Date": "Sat, 28 Jan 2023 09:55:37 GMT",
        "Access-Control-Allow-Headers": "Origin, Accept, X-Requested-With, Content-Type, Authorization",
        "Content-Type": "text/html; charset=UTF-8"
      },
      "transmission_id": "e0d229d0-9ef1-11ed-ad1c-0b20a097f2fe",
      "status": "SUCCESS",
      "timestamp": "2023-01-28T09:55:21Z"
    }
  ],
  "links": [
    {
      "href": "https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-12P352976M872801U-4VG268328G5994910",
      "rel": "self",
      "method": "GET",
      "encType": "application/json"
    },
    {
      "href": "https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-12P352976M872801U-4VG268328G5994910/resend",
      "rel": "resend",
      "method": "POST",
      "encType": "application/json"
    }
  ],
  "event_version": "1.0",
  "resource_version": "2.0"
}
JSON;
        $payPalWebHookEventObject = new OnAddPayPalWebHookEvent();
        $payPalWebHookEventObject->setWebHookData(json_decode($json));
        $webHookEventObject = event()->dispatch($payPalWebHookEventObject)->event();
        $webHookEventObject->handleWebHookEvent('PAYMENT.CAPTURE.COMPLETED');

        exit();
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