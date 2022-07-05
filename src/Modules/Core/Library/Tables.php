<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Core\Library;


use App\Modules\Core\Configs\DatabaseConfig;

final class Tables
{
    static array $TABLES = [
        self::ARTISTS => ['artist_id', 'artist_name', 'artist_slug', 'artist_bio', 'image_url', 'created_at', 'updated_at'],
        self::PAGES => ['page_id', 'field_ids', 'page_title', 'page_slug', 'page_status', 'field_settings', 'created_at', 'updated_at'],
        self::CAT_RELS => ['catrel_id', 'post_id', 'cat_parent_id'],
        self::CATEGORIES => ['cat_id', 'cat_parent_id', 'cat_name', 'cat_slug', 'cat_url_slug', 'cat_content', 'cat_status', 'created_at', 'updated_at'],
        self::GENRES => ['genre_id', 'genre_name', 'genre_slug', 'genre_description', 'can_delete', 'created_at', 'updated_at'],
        self::LICENSES => ['license_id', 'license_name', 'license_slug', 'license_status', 'license_attr'],
        self::MENU_ITEMS => ['id', 'fk_menu_id', 'mt_id', 'mt_parent_id', 'mt_name', 'mt_icon', 'mt_classes', 'mt_target', 'mt_url_slug', 'created_at', 'updated_at'],
        self::MENUS => ['menu_id', 'menu_name', 'menu_slug', 'created_at', 'updated_at'],
        self::FIELD_ITEMS => ['id', 'fk_field_id', 'field_id', 'field_parent_id', 'field_name', 'field_options', 'created_at', 'updated_at'],
        self::FIELD => ['field_id', 'field_name', 'field_slug', 'can_delete', 'created_at', 'updated_at'],
        self::MIGRATIONS => ['id', 'migration'],
        self::POST_CATEGORIES => ['id', 'fk_cat_id', 'fk_post_id', 'created_at', 'updated_at'],
        self::POSTS => ['post_id', 'slug_id', 'user_id', 'field_ids', 'image_url', 'post_title', 'post_slug', 'post_status', 'field_settings', 'created_at', 'updated_at'],
        self::PURCHASES => ['purchase_id', 'slug_id', 'fk_customer_id', 'total_price', 'payment_status', '`others`', 'created_at', 'updated_at'],
        self::PURCHASE_TRACKS => ['pt_id', 'fk_purchase_id', 'fk_track_id', 'price', 'created_at', 'updated_at'],
        self::SESSIONS => ['id', 'session_id', 'session_data', 'updated_at'],
        self::GLOBAL => ['id', '`key`', 'value', 'created_at', 'updated_at'],
        self::TRACK_LIKES => ['id', 'fk_customer_id', 'fk_track_id', 'is_like', 'created_at', 'updated_at'],
        self::TRACKS => ['track_id', 'slug_id', 'track_slug', 'image_url', 'audio_url', 'track_title', 'track_plays', 'track_bpm', 'track_status', 'license_attr_id_link', 'field_settings', 'fk_genre_id', 'fk_artist_id', 'fk_license_id', 'field_ids', 'created_at', 'updated_at'],
        self::USER_TYPE => ['user_type_id', 'user_type_name'],
        self::USERS => ['user_id', 'user_type', 'user_name', 'email', 'email_verified_at', 'user_password', 'created_at', 'updated_at'],
        self::ADMINS => ['user_id', 'user_type_id', '`role`', 'settings'],
        self::CUSTOMERS => ['user_id', 'user_type_id', 'is_guest', 'settings'],
        self::WIDGET_ITEMS => ['id', 'fk_widget_id', 'wgt_id', 'wgt_name', 'wgt_options', 'created_at', 'updated_at'],
        self::WIDGETS => ['widget_id', 'widget_name', 'widget_slug', 'created_at', 'updated_at'],
        self::WISH_LIST => ['wl_id', 'fk_customer_id', 'track_id', 'created_at', 'updated_at'],
        self::DRIVE_SYSTEM => ['drive_id', 'drive_parent_id', 'drive_unique_id', 'drive_name', 'filename', '`type`', 'status', 'properties', '`security`'],
        self::DRIVE_BLOB_COLLATOR => [ 'id', 'hash_id', 'blob_name', 'blob_chunk_part', 'blob_chunk_size', 'live_blob_chunk_size', 'missing_blob_chunk_byte', 'moreBlobInfo'],
    ];

    const ARTISTS = 'artists';
    const PAGES =  'pages';
    const CAT_RELS = 'cat_rels';
    const CATEGORIES = 'categories';
    const GENRES = 'genres';
    const LICENSES = 'licenses';

    const FIELD_ITEMS = 'field_items';
    const FIELD = 'fields';

    const MENU_ITEMS = 'menu_items';
    const MENUS = 'menus';

    const MIGRATIONS = 'migrations';
    const POST_CATEGORIES = 'post_categories';
    const POSTS = 'posts';

    const PURCHASES = 'purchases';
    const SESSIONS = 'sessions';
    const GLOBAL = 'global';

    const TRACK_LIKES = 'track_likes';
    const TRACKS = 'tracks';
    const PURCHASE_TRACKS = 'purchase_tracks';

    const USER_TYPE = 'usertype';
    const USERS = 'user';
    const ADMINS = 'admin';
    const CUSTOMERS = 'customer';

    const WIDGET_ITEMS = 'widget_items';
    const WIDGETS = 'widgets';
    const WISH_LIST = 'wish_list';

    const DRIVE_SYSTEM = 'drive_system';
    // Backward Compatibility
    static array $DRIVE_SYSTEM_COLUMN = [
        'drive_id', 'drive_parent_id', 'drive_unique_id', 'drive_name', 'type', 'filename', 'status', 'properties', 'security'
    ];

    const DRIVE_BLOB_COLLATOR = 'drive_blob_collator';

    public static function getTable(string $tablename): string
    {
        if (!key_exists($tablename, self::$TABLES)){
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