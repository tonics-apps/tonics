<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
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