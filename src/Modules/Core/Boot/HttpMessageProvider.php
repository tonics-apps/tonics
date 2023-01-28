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
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Media\FileManager\LocalDriver;
use App\Modules\Payment\Controllers\PaymentSettingsController;
use App\Modules\Payment\EventHandlers\TrackPaymentMethods\AudioTonicsPayPalHandler;
use Devsrealm\TonicsContainer\Container;
use Devsrealm\TonicsContainer\Interfaces\ServiceProvider;
use Devsrealm\TonicsRouterSystem\Handler\Router;
use Embera\Embera;

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
  "transmission_id": "e3599800-9ef1-11ed-bd9b-35fcef918fa2",
  "transmission_time": "2023-01-28T09:55:25Z",
  "cert_url": "https://api.sandbox.paypal.com/v1/notifications/certs/CERT-360caa42-fca2a594-5a29e601",
  "auth_algo": "SHA256withRSA",
  "transmission_sig": "cA7Xu6bPAob39e8exrzekfIYt8Uiv9Hmd074On9YmNUr5pwev9CqEBOQ7QtPVItO6UhfaXtnIxEgFKgQsqUuQOcbYEWMAZn66YBb/DAXtxZ4fCeqyezyf2I0Sb2836FDwJz3RepuQJr3dtlzUIRIBLKVapKVDUuwcLY7fHSqADlIEI7DwD8cmnGyBS0dzUMKJienKwFBPLjVhMyg6hUdosyFdIHIb7rIilc9E0I82cgb5VMhWquOQIzr9Vylz3O0AKUdZygeMpqTvIMDdzvMNB30SCy4EGoIlxkibJAuvxP6BaUiJYs9YapGm9K3Z69MhPMdEi+i6FInAp8w8UGoZg==",
  "webhook_id": "1HE90801975891415",
  "webhook_event": {
    "id": "WH-1DF88120YX720913L-8RP01552DT256654C",
    "event_version": "1.0",
    "create_time": "2023-01-28T09:55:22.380Z",
    "resource_type": "checkout-order",
    "resource_version": "2.0",
    "event_type": "CHECKOUT.ORDER.APPROVED",
    "summary": "An order has been approved by buyer",
    "resource": {
      "update_time": "2023-01-28T09:55:14Z",
      "create_time": "2023-01-28T09:55:05Z",
      "purchase_units": [
        {
          "reference_id": "default",
          "amount": {
            "currency_code": "USD",
            "value": "60.00",
            "breakdown": {
              "item_total": {
                "currency_code": "USD",
                "value": "60.00"
              }
            }
          },
          "payee": {
            "email_address": "sb-xh43ro24927521@business.example.com",
            "merchant_id": "335CB3EA63U2A"
          },
          "invoice_id": "AudioTonics_63d4f0f7b245d8.54148325",
          "items": [
            {
              "name": "Ferola - One Life",
              "unit_amount": {
                "currency_code": "USD",
                "value": "10.00"
              },
              "quantity": "1",
              "description": "At the time of the purchase, you bought the Basic License of Ferola - One Life wih the slug id a1c1cc7eab81805c"
            },
            {
              "name": "Horlaes - Vibez Cartel",
              "unit_amount": {
                "currency_code": "USD",
                "value": "50.00"
              },
              "quantity": "1",
              "description": "At the time of the purchase, you bought the EMP Basic License of Horlaes - Vibez Cartel wih the slug id 39a9a165d0291be1"
            }
          ],
          "shipping": {
            "name": {
              "full_name": "John Doe"
            },
            "address": {
              "address_line_1": "1 Main St",
              "admin_area_2": "San Jose",
              "admin_area_1": "CA",
              "postal_code": "95131",
              "country_code": "US"
            }
          },
          "payments": {
            "captures": [
              {
                "id": "8TP206213F3834453",
                "status": "COMPLETED",
                "amount": {
                  "currency_code": "USD",
                  "value": "60.00"
                },
                "final_capture": true,
                "seller_protection": {
                  "status": "ELIGIBLE",
                  "dispute_categories": [
                    "ITEM_NOT_RECEIVED",
                    "UNAUTHORIZED_TRANSACTION"
                  ]
                },
                "seller_receivable_breakdown": {
                  "gross_amount": {
                    "currency_code": "USD",
                    "value": "60.00"
                  },
                  "paypal_fee": {
                    "currency_code": "USD",
                    "value": "2.58"
                  },
                  "net_amount": {
                    "currency_code": "USD",
                    "value": "57.42"
                  }
                },
                "invoice_id": "AudioTonics_63d4f0f7b245d8.54148325",
                "links": [
                  {
                    "href": "https://api.sandbox.paypal.com/v2/payments/captures/8TP206213F3834453",
                    "rel": "self",
                    "method": "GET"
                  },
                  {
                    "href": "https://api.sandbox.paypal.com/v2/payments/captures/8TP206213F3834453/refund",
                    "rel": "refund",
                    "method": "POST"
                  },
                  {
                    "href": "https://api.sandbox.paypal.com/v2/checkout/orders/08F678773R544881S",
                    "rel": "up",
                    "method": "GET"
                  }
                ],
                "create_time": "2023-01-28T09:55:14Z",
                "update_time": "2023-01-28T09:55:14Z"
              }
            ]
          }
        }
      ],
      "links": [
        {
          "href": "https://api.sandbox.paypal.com/v2/checkout/orders/08F678773R544881S",
          "rel": "self",
          "method": "GET"
        }
      ],
      "id": "08F678773R544881S",
      "payment_source": {
        "paypal": {}
      },
      "intent": "CAPTURE",
      "payer": {
        "name": {
          "given_name": "John",
          "surname": "Doe"
        },
        "email_address": "sb-pqmqi24930098@personal.example.com",
        "payer_id": "5AGG5SSEJZ77E",
        "address": {
          "country_code": "US"
        }
      },
      "status": "COMPLETED"
    },
    "links": [
      {
        "href": "https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-1DF88120YX720913L-8RP01552DT256654C",
        "rel": "self",
        "method": "GET"
      },
      {
        "href": "https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-1DF88120YX720913L-8RP01552DT256654C/resend",
        "rel": "resend",
        "method": "POST"
      }
    ]
  }
}
JSON;
        dd(json_decode($json));
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