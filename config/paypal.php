<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

/**
 * PayPal Setting & API Credentials
 */

return [
    'mode'    => env('PAYPAL_MODE', 'sandbox'), // Can only be 'sandbox' Or 'live'. If empty or invalid, 'live' will be used.

    'sandbox' => [
        'client_id'    => env('PAYPAL_CLIENT_ID', 'AZ6yJT3N_jhaXvt8fKnDCyLNuav6akeaH2CClLbVrhd14FzESKd3w9Z_dDGo1jHgFak5nujnVkv8nUW_'),
        'client_secret'    => env('PAYPAL_CLIENT_SECRET', 'EHEOmnf09Yl0GfGL_Y6QTi3z5T1CjbsoQz0CUso_MGoL5MsACSGainGq5NrHZHQH2w4CU18fPlYzScLw'),
        'certificate' => env('PAYPAL_SANDBOX_API_CERTIFICATE', ''),
        'app_id'      => 'APP-80W284485P519543T', // Used for testing Adaptive Payments API in sandbox mode
    ],

    'live' => [
        'client_id'    => env('PAYPAL_CLIENT_ID', ''),
        'client_secret'    => env('PAYPAL_CLIENT_SECRET', ''),
        'certificate' => env('PAYPAL_SANDBOX_API_CERTIFICATE', ''),
        'app_id'      => 'APP-80W284485P519543T', // Used for testing Adaptive Payments API in sandbox mode
    ],

    'payment_action' => 'Sale', // Can only be 'Sale', 'Authorization' or 'Order'
    'currency'       => env('PAYPAL_CURRENCY', 'USD'),
    'billing_type'   => 'MerchantInitiatedBilling',
    'notify_url'     => '', // Change this accordingly for your application.
    'locale'         => '', // force gateway language  i.e. it_IT, es_ES, en_US ... (for express checkout only)
    'validate_ssl'   => true, // Validate SSL when creating api client.
];
