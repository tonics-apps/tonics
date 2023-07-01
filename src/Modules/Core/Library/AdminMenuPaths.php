<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library;

final class AdminMenuPaths
{
    const TOOL = '/TOOLS';

    const WIDGET = self::TOOL . '/WIDGET';
    const WIDGET_NEW = self::WIDGET . '/NEW_WIDGET';
    const WIDGET_ALL = self::WIDGET . '/ALL_WIDGET';

    const MENU = self::TOOL . '/MENU';
    const MENU_NEW = self::MENU . '/NEW_MENU';
    const MENU_ALL = self::MENU . '/ALL_MENU';

    const FIELD = self::TOOL . '/FIELD';
    const FIELD_NEW = self::FIELD . '/NEW_FIELD';
    const FIELD_ALL = self::FIELD . '/ALL_FIELD';

    const APPS = self::TOOL . '/APPS';
    const APP_FORCE_UPDATE_CHECK = self::APPS . '/FORCE_UPDATE_CHECK_APP';
    const APP_UPLOAD_APP = self::APPS . '/UPLOAD_APP';

    const JOB_MANAGER = self::TOOL . '/JOBS_MANAGER';
    const JOBS = self::JOB_MANAGER . '/JOBS';
    const JOB_SCHEDULER = self::JOB_MANAGER . '/JOBS_SCHEDULER';

    const PAGE = '/PAGE';
    const PAGE_NEW = self::PAGE . '/NEW_PAGE';
    const PAGE_ALL = self::PAGE . '/ALL_PAGE';

    const MEDIA = '/MEDIA';
    const FILE_MANAGER = self::MEDIA . '/FILE_MANAGER';

    const TRACK = self::MEDIA . '/TRACK';
    const TRACK_NEW = self::TRACK . '/NEW_TRACK';
    const TRACK_ALL = self::TRACK . '/ALL_TRACK';

    const TRACK_CATEGORY = self::TRACK . '/TRACK_CATEGORY';
    const TRACK_CATEGORY_NEW = self::TRACK_CATEGORY . '/NEW_TRACK_CATEGORY';
    const TRACK_CATEGORY_ALL = self::TRACK_CATEGORY . '/ALL_TRACK_CATEGORY';

    const TRACK_LICENSE = self::TRACK . '/TRACK_LICENSE';
    const TRACK_LICENSE_NEW = self::TRACK_LICENSE . '/NEW_TRACK_LICENSE';
    const TRACK_LICENSE_ALL = self::TRACK_LICENSE . '/ALL_TRACK_LICENSE';

    const GENRE = self::MEDIA . '/GENRE';
    const GENRE_NEW = self::GENRE . '/NEW_GENRE';
    const GENRE_ALL = self::GENRE . '/ALL_GENRE';

    const ARTIST = self::MEDIA . '/ARTIST';
    const ARTIST_NEW = self::ARTIST . '/NEW_ARTIST';
    const ARTIST_ALL = self::ARTIST . '/ALL_ARTIST';

    const POST =  '/POST';
    const POST_NEW = self::POST . '/NEW_POST';
    const POST_ALL = self::POST . '/ALL_POST';
    const POST_CATEGORY_NEW = self::POST . '/NEW_CATEGORY';
    const POST_CATEGORY_ALL = self::POST . '/ALL_CATEGORY';

    const CUSTOMER_ORDERS = '/ORDERS';

    const PRIORITY_VERY_EXTREME = 1000;
    const PRIORITY_EXTREME = 999;
    const PRIORITY_VERY_URGENT = 998;
    const PRIORITY_URGENT = 997;
    const PRIORITY_VERY_HIGH = 996;
    const PRIORITY_VERY_MEDIUM = 995;
    const PRIORITY_MEDIUM = 994;
    const PRIORITY_LOW = 993;
    const PRIORITY_VERY_LOW = 992;
}