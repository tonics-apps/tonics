<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

return [


    ##-+ APPLICATION NAME -+##
    'name' => env('APP_NAME', 'BeatStore'),

    ##-+ APPLICATION ENVIRONMENT -+##
    'env' => env('APP_ENV', 'production'),

    ##-+ APPLICATION URL -+##
    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL', null),

    ##-+ APPLICATION TIMEZONE -+##
    'timezone' => 'Africa/Lagos',

    'key' => env('APP_KEY'),

];
