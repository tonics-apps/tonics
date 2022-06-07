<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

return [

    /**
     * Public Key: Your Rave publicKey. Sign up on https://rave.flutterwave.com/ to get one from your settings page
     *
     */
    'publicKey' => env('RAVE_PUBLIC_KEY'),

    /**
     * Secret Key: Your Rave secretKey. Sign up on https://rave.flutterwave.com/ to get one from your settings page
     *
     */
    'secretKey' => env('RAVE_SECRET_KEY'),

    /**
     * Company/Business/Store Name: The name of your store
     *
     */
    'title' => env('title', "Rave Payment Gateway"),

    /**
     * Environment: This can either be 'staging' or 'live'
     *
     */
    'env' => env('RAVE_ENVIRONMENT', "staging"),

    /**
     * Logo: Enter the URL of your company/business logo
     *
     */
    'logo' => env('RAVE_LOGO', "https://cdn.devsrealm.com/wp-content/uploads/2020/01/Devsreal-Author-Logo.svg"),

    /**
     * Prefix: This is added to the front of your transaction reference numbers
     *
     */
    'prefix' => env('RAVE_PREFIX', "rave"),

    /**
     * Prefix: This is added to the front of your transaction reference numbers
     *
     */
    'secretHash' => env('RAVE_SECRET_HASH', ''),
];
