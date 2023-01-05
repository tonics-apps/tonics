<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\Helper;


class TrackRedirection
{
    /**
     * @param array $track
     * @return string
     */
    public static function getTrackAbsoluteURLPath(array $track): string
    {
        if (isset($track['slug_id']) && isset($track['track_slug'])){
            return "/tracks/{$track['slug_id']}/{$track['track_slug']}";
        }

        return '';
    }

    public static function getTrackCategoryAbsoluteURLPath(array $track): string
    {
        if (isset($track['slug_id']) && isset($track['track_cat_slug'])){
            return "/track_categories/{$track['slug_id']}/{$track['track_cat_slug']}";
        }

        return '';
    }
}