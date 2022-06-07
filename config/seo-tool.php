<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

// config for Devsrealm/ClassName

return [

    /**
     * SEO SITE TITLE: Add site title in front of every post title, The Second Option gets the app.name form env
     *
     */
    'sitetitle' => env('SEO_SITE_TITLE', env('APP_NAME')),
    /**
     * SEO Delimiter: delimits site title e.g Devsrealm | My First Post
     *
     */
    'delimiter' => env('SEO_SITE_DELIMITER', '| '),

    // For Google Analytics
    'ga_analytics' => env('GOOGLE_ANALYTICS', ''),

    /*
    |-----------------------------------------------------
    | TWITTER SETTINGS
    |----------------------------------------------------
    |
    | TWITTER_CREATOR should be @creator_name_on_twitter
    | TWITTER_SITE should be @site_name_on_twitter
    | TWITTER_CARD do not edit this except if you know what you are doing...
    |
    */

    'twitter_creator' => env('TWITTER_CREATOR', '@ferola'),

    'twitter_site' => env('TWITTER_SITE', '@ex_musicplus'),

    'twitter_card' => env('TWITTER_CARD', 'summary_large_image'),


];
