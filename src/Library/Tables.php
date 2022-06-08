<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Library;


use App\Configs\DatabaseConfig;

final class Tables
{
    static array $TABLES = [
        self::POST_TYPE,
        self::ARTISTS,
        self::PAGES,
        self::CAT_RELS,
        self::CATEGORIES,
        self::GENRES,
        self::LICENSES,
        self::MENU_ITEMS,
        self::MENU_LOCATIONS,
        self::MENUS,
        self::FIELD_ITEMS,
        self::FIELD,
        self::MIGRATIONS,
        self::PLUGINS,
        self::POST_CATEGORIES,
        self::POSTS,
        self::PURCHASES,
        self::PURCHASE_TRACKS,
        self::SESSIONS,
        self::GLOBAL,
        self::TAG_RELS,
        self::TAGS,
        self::TRACK_LIKES,
        self::TRACKS,
        self::USER_TYPE,
        self::USERS,
        self::ADMINS,
        self::CUSTOMERS,
        self::WIDGET_ITEMS,
        self::WIDGET_LOCATIONS,
        self::WIDGETS,
        self::WISH_LIST,
        self::DRIVE_SYSTEM,
        self::DRIVE_BLOB_COLLATOR
    ];

    const ARTISTS = 'artists';
    const PAGES =  'pages';
    const CAT_RELS = 'cat_rels';
    const CATEGORIES = 'categories';
    const GENRES = 'genres';
    const LICENSES = 'licenses';

    const POST_TYPE = 'post_type';

    const FIELD_ITEMS = 'field_items';
    const FIELD = 'fields';

    const MENU_ITEMS = 'menu_items';
    const MENU_LOCATIONS = 'menu_locations';
    const MENUS = 'menus';

    const MIGRATIONS = 'migrations';
    const PLUGINS = 'plugins';
    const POST_CATEGORIES = 'post_categories';
    const POSTS = 'posts';

    const PURCHASES = 'purchases';
    const SESSIONS = 'sessions';
    const GLOBAL = 'global';
    const TAG_RELS = 'tag_rels';
    const TAGS = 'tags';

    const TRACK_LIKES = 'track_likes';
    const TRACKS = 'tracks';
    const PURCHASE_TRACKS = 'purchase_tracks';

    const USER_TYPE = 'usertype';
    const USERS = 'user';
    const ADMINS = 'admin';
    const CUSTOMERS = 'customer';

    const WIDGET_ITEMS = 'widget_items';
    const WIDGET_LOCATIONS = 'widget_locations';
    const WIDGETS = 'widgets';
    const WISH_LIST = 'wish_list';

    const DRIVE_SYSTEM = 'drive_system';
    static array $DRIVE_SYSTEM_COLUMN = [
        'drive_id', 'drive_parent_id', 'drive_unique_id', 'drive_name', 'type', 'filename', 'status', 'properties', 'security'
    ];

    const DRIVE_BLOB_COLLATOR = 'drive_blob_collator';

    public static function getTable(string $tablename): string
    {
        if (!key_exists($tablename, array_flip(self::$TABLES))){
            throw new \InvalidArgumentException("`$tablename` is an invalid table name");
        }

        return DatabaseConfig::getPrefix() . $tablename;
    }

    private static function DbTablePrefix(): string
    {
        return DatabaseConfig::getPrefix();
    }

    static function removeColumnFromTable(array $tableColumns, array $columnToRemove, $implodeColumns = false): array|string
    {
        if ($implodeColumns){
            return implode(',', array_diff($tableColumns, $columnToRemove));
        }
        return array_diff($tableColumns, $columnToRemove);
    }

    static function addColumnsToTable(array $tableColumns, array $columnsToAdd, $implodeColumns = false): array|string
    {
        if ($implodeColumns){
            return implode(',', array_merge($tableColumns, $columnsToAdd));
        }
        return array_merge($tableColumns, $columnsToAdd);
    }
}