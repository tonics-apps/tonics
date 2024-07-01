<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Core\Library;

final class AdminMenuHelper
{
    const DASHBOARD = '/DASHBOARD';

    const TOOL = self::DASHBOARD . '/TOOLS';

    const APPEARANCE = self::TOOL . '/APPEARANCE';
    const LOGS       = self::TOOL . '/LOGS';
    const WIDGET     = self::APPEARANCE . '/WIDGET';

    const WIDGET_NEW  = self::WIDGET . '/NEW_WIDGET';
    const WIDGET_EDIT = self::WIDGET . '/EDIT_WIDGET';

    const MENU      = self::APPEARANCE . '/MENU';
    const MENU_NEW  = self::MENU . '/NEW_MENU';
    const MENU_EDIT = self::MENU . '/EDIT_MENU';

    const FIELD            = self::APPEARANCE . '/FIELD';
    const FIELD_NEW        = self::FIELD . '/NEW_FIELD';
    const FIELD_EDIT       = self::FIELD . '/EDIT_FIELD';
    const FIELD_ITEMS_EDIT = self::FIELD . '/EDIT_FIELD_ITEMS';

    const APPS                   = self::TOOL . '/APPS';
    const APP_SETTINGS           = self::APPS . '/APPS_SETTINGS';
    const APP_FORCE_UPDATE_CHECK = self::APPS . '/FORCE_UPDATE_CHECK_APP';
    const APP_UPLOAD_APP         = self::APPS . '/UPLOAD_APP';

    const CUSTOMER = self::TOOL . '/CUSTOMERS';

    const JOB_MANAGER   = self::TOOL . '/JOBS_MANAGER';
    const JOBS          = self::JOB_MANAGER . '/JOBS';
    const JOB_SCHEDULER = self::JOB_MANAGER . '/JOBS_SCHEDULER';

    const PAGE      = self::DASHBOARD . '/PAGE';
    const PAGE_NEW  = self::PAGE . '/NEW_PAGE';
    const PAGE_EDIT = self::PAGE . '/ALL_PAGE';

    const MEDIA              = self::DASHBOARD . '/MEDIA';
    const FILE_MANAGER       = self::MEDIA . '/FILE_MANAGER';
    const LICENSE            = self::MEDIA . '/LICENSE';
    const LICENSE_NEW        = self::LICENSE . '/NEW_LICENSE';
    const LICENSE_EDIT       = self::LICENSE . '/EDIT_LICENSE';
    const LICENSE_ITEMS_EDIT = self::LICENSE . '/EDIT_LICENSE_ITEMS';

    const TRACK      = self::MEDIA . '/TRACK';
    const TRACK_NEW  = self::TRACK . '/NEW_TRACK';
    const TRACK_EDIT = self::TRACK . '/EDIT_TRACK';

    const TRACK_CATEGORY      = self::TRACK . '/TRACK_CATEGORY';
    const TRACK_CATEGORY_NEW  = self::TRACK_CATEGORY . '/NEW_TRACK_CATEGORY';
    const TRACK_CATEGORY_EDIT = self::TRACK_CATEGORY . '/EDIT_TRACK_CATEGORY';

    const GENRE      = self::MEDIA . '/GENRE';
    const GENRE_NEW  = self::GENRE . '/NEW_GENRE';
    const GENRE_EDIT = self::GENRE . '/EDIT_GENRE';

    const ARTIST      = self::MEDIA . '/ARTIST';
    const ARTIST_NEW  = self::ARTIST . '/NEW_ARTIST';
    const ARTIST_EDIT = self::ARTIST . '/EDIT_ARTIST';

    const POST               = self::DASHBOARD . '/POST';
    const POST_NEW           = self::POST . '/NEW_POST';
    const POST_EDIT          = self::POST . '/EDIT_POST';
    const POST_CATEGORY_NEW  = self::POST . '/NEW_CATEGORY';
    const POST_CATEGORY_ALL  = self::POST . '/ALL_CATEGORY';
    const POST_CATEGORY_EDIT = self::POST . '/EDIT_CATEGORY';

    const CUSTOMER_ORDERS = self::DASHBOARD . '/ORDERS';

    const PRIORITY_VERY_EXTREME = 1000;
    const PRIORITY_EXTREME      = 999;
    const PRIORITY_VERY_URGENT  = 998;
    const PRIORITY_URGENT       = 997;
    const PRIORITY_VERY_HIGH    = 996;
    const PRIORITY_VERY_MEDIUM  = 995;
    const PRIORITY_MEDIUM       = 994;
    const PRIORITY_LOW          = 993;
    const PRIORITY_VERY_LOW     = 992;
}