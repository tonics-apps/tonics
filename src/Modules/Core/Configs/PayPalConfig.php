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


class PayPalConfig
{

    public static function getMode()
    {
        return env('PAYPAL_MODE', 'sandbox');
    }

    /**
     * @param string $action
     * Can only be 'Sale', 'Authorization' or 'Order'
     * @return string
     */
    public static function getPaymentAction(string $action = 'Sale'): string
    {
        return $action;

    }

    /**
     * @return string
     */
    public static function getCurrency(): string
    {
        return env('PAYPAL_CURRENCY', 'USD');
    }

    /**
     * @param string $type
     * @return string
     */
    public static function getBillingType(string $type = 'MerchantInitiatedBilling'): string
    {
        return $type;
    }

    /**
     * @param string $url
     * @return string
     */
    public static function getNotificationUrl(string $url = ''): string
    {
        return $url;
    }

    /**
     * force gateway language  i.e. it_IT, es_ES, en_US ... (for express checkout only)
     * @param string $locale
     * @return string
     */
    public static function getLocale(string $locale = 'en_US'): string
    {
        return $locale;
    }

    /**
     * Should PayPal Validate SSL Cert
     * @return bool
     */
    public static function validateSSL(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public static function getClientID(): string
    {
        return env('PAYPAL_CLIENT_ID', 'null');
    }

    /**
     * @return string
     */
    public static function getClientSecret(): string
    {
        return  env('PAYPAL_CLIENT_SECRET', 'null');
    }

    /**
     * @return string
     */
    public static function getCertificate(): string
    {
        return env('PAYPAL_SANDBOX_API_CERTIFICATE', '');
    }

    /**
     *  Used for testing Adaptive Payments API in sandbox mode
     * @return string
     */
    public static function getAppID(): string
    {
        return env('PAYPAL_APP_ID', 'null');
    }

}