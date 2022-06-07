<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Configs;


class SeoConfig
{

    public static function getSiteTitle()
    {
        return env('SEO_SITE_TITLE', env('APP_NAME'));
    }

    public static function getDelimiter()
    {
        return env('SEO_SITE_DELIMITER', '| ');
    }

    public static function getGoogleAnalyticsCode()
    {
        return env('GOOGLE_ANALYTICS', 'null');
    }
}