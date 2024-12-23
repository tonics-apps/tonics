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

namespace App\Modules\Core\Library;


use App\Modules\Core\Configs\DatabaseConfig;

final class Tables
{
    const ARTISTS                = 'artists';
    const PAGES                  = 'pages';
    const CATEGORIES             = 'categories';
    const GENRES                 = 'genres';
    const LICENSES               = 'licenses';
    const FIELD_ITEMS            = 'field_items';
    const FIELD                  = 'fields';
    const MENU_ITEMS             = 'menu_items';
    const MENUS                  = 'menus';
    const MENU_ITEM_PERMISSION   = 'menu_item_permission';
    const MIGRATIONS             = 'migrations';
    const POST_CATEGORIES        = 'post_categories';
    const POSTS                  = 'posts';
    const PURCHASES              = 'purchases';
    const SESSIONS               = 'sessions';
    const GLOBAL                 = 'global';
    const BROKEN_LINKS           = 'broken_links';
    const TRACK_LIKES            = 'track_likes';
    const TRACKS                 = 'tracks';
    const TRACK_GENRES           = 'track_genres';
    const TRACK_CATEGORIES       = 'track_categories';
    const TRACK_TRACK_CATEGORIES = 'track_track_categories';
    // could have named this categories but the post is already using that, so, track_categories means one thing a track category
    // and not track that links to category that is what track_track_categories is for
    const PURCHASE_TRACKS              = 'purchase_tracks';
    const TRACK_WISH_LIST              = 'track_wish_list';
    const TRACK_DEFAULT_FILTERS        = 'track_default_filters';
    const TRACK_DEFAULT_FILTERS_TRACKS = 'track_default_filters_tracks';

    // would hold the common filters values and their types
    const ROLES               = 'roles';
    const PERMISSIONS         = 'permissions'; // a junction table connect track_default_filters to tracks
    const ROLE_PERMISSIONS    = 'role_permissions';
    const USERS               = 'user';
    const CUSTOMERS           = 'customer';
    const SCHEDULER           = 'scheduler';
    const JOBS                = 'jobs';
    const COMMENTS            = 'comments';
    const COMMENT_USER_TYPE   = 'comments_user_type';
    const DRIVE_SYSTEM        = 'drive_system';
    const DRIVE_BLOB_COLLATOR = 'drive_blob_collator';
    static array $TABLES = [
        self::ARTISTS    => ['artist_id', 'artist_name', 'artist_slug', 'artist_bio', 'image_url', 'created_at', 'updated_at'],
        self::PAGES      => ['page_id', 'field_ids', 'page_title', 'page_template', 'page_slug', 'page_status', 'field_settings', 'created_at', 'updated_at'],
        self::CATEGORIES => ['cat_id', 'cat_parent_id', 'slug_id', 'cat_name', 'cat_slug', 'cat_status', 'field_settings', 'created_at', 'updated_at'],
        self::GENRES     => ['genre_id', 'genre_name', 'genre_slug', 'genre_description', 'genre_status', 'created_at', 'updated_at'],
        self::LICENSES   => ['license_id', 'license_name', 'license_slug', 'license_status', 'license_attr', 'created_at', 'updated_at'],

        self::MENU_ITEMS           => ['id', 'fk_menu_id', 'mt_id', 'mt_parent_id', 'slug_id', 'mt_name', 'mt_icon', 'mt_classes', 'mt_target', 'mt_url_slug', 'created_at', 'updated_at'],
        self::MENUS                => ['menu_id', 'menu_name', 'menu_slug', 'menu_can_edit', 'created_at', 'updated_at'],
        self::MENU_ITEM_PERMISSION => ['menu_item_permissions_id', 'fk_menu_item_slug_id', 'fk_permission_id'],

        self::FIELD_ITEMS     => ['id', 'fk_field_id', 'field_id', 'field_parent_id', 'field_name', 'field_options', 'created_at', 'updated_at'],
        self::FIELD           => ['field_id', 'field_name', 'field_slug', 'created_at', 'updated_at'],
        self::MIGRATIONS      => ['id', 'migration'],
        self::POST_CATEGORIES => ['id', 'fk_cat_id', 'fk_post_id', 'created_at', 'updated_at'],
        self::POSTS           => ['post_id', 'slug_id', 'user_id', 'image_url', 'post_title', 'post_excerpt', 'post_slug', 'post_status', 'field_settings', 'created_at', 'updated_at'],
        self::PURCHASES       => ['purchase_id', 'slug_id', 'fk_customer_id', 'total_price', 'payment_status', 'others', 'created_at', 'updated_at'],
        self::SESSIONS        => ['id', 'session_id', 'session_data', 'updated_at'],
        self::GLOBAL          => ['id', 'key', 'value', 'created_at', 'updated_at'],

        self::TRACK_LIKES                  => ['id', 'fk_customer_id', 'fk_track_id', 'is_like', 'created_at', 'updated_at'],
        self::TRACKS                       => ['track_id', 'slug_id', 'track_slug', 'image_url', 'audio_url', 'track_title', 'track_plays', 'track_bpm', 'track_status', 'license_attr_id_link', 'field_settings', 'fk_artist_id', 'fk_license_id', 'created_at', 'updated_at'],
        self::TRACK_GENRES                 => ['id', 'fk_genre_id', 'fk_track_id', 'created_at', 'updated_at'],
        self::TRACK_CATEGORIES             => ['track_cat_id', 'track_cat_parent_id', 'slug_id', 'track_cat_name', 'track_cat_slug', 'track_cat_status', 'field_settings', 'created_at', 'updated_at'],
        self::TRACK_TRACK_CATEGORIES       => ['id', 'fk_track_cat_id', 'fk_track_id', 'created_at', 'updated_at'],
        self::PURCHASE_TRACKS              => ['pt_id', 'fk_purchase_id', 'fk_track_id', 'price', 'created_at', 'updated_at'],
        self::TRACK_WISH_LIST              => ['wl_id', 'fk_customer_id', 'track_id', 'created_at', 'updated_at'],
        self::TRACK_DEFAULT_FILTERS        => ['tdf_id', 'tdf_name', 'tdf_type'],
        self::TRACK_DEFAULT_FILTERS_TRACKS => ['id', 'fk_track_id', 'fk_tdf_id'],

        self::USERS => ['user_id', 'user_name', 'email', 'email_verified_at', 'user_password', 'role', 'settings', 'created_at', 'updated_at'],

        self::ROLES            => ['role_id', 'role_name', 'created_at', 'updated_at'],
        self::PERMISSIONS      => ['permission_id', 'permission_display_name', 'permission_name', 'created_at', 'updated_at'],
        self::ROLE_PERMISSIONS => ['id', 'fk_role_id', 'fk_permission_id'],

        self::CUSTOMERS           => ['user_id', 'user_name', 'email', 'email_verified_at', 'user_password', 'is_guest', 'role', 'settings', 'created_at', 'updated_at'],
        self::DRIVE_SYSTEM        => ['drive_id', 'drive_parent_id', 'drive_unique_id', 'drive_name', 'filename', '`type`', 'status', 'properties', '`security`'],
        self::DRIVE_BLOB_COLLATOR => ['id', 'hash_id', 'blob_name', 'blob_chunk_part', 'blob_chunk_size', 'live_blob_chunk_size', 'missing_blob_chunk_byte', 'moreBlobInfo'],

        self::SCHEDULER => ['schedule_id', 'schedule_name', 'schedule_parent_name', 'schedule_priority', 'schedule_data', 'schedule_parallel', 'schedule_ticks', 'schedule_every', 'schedule_next_run', 'created_at', 'updated_at'],

        self::JOBS => ['job_id', 'job_name', 'job_status', 'job_priority', 'job_data', 'created_at', 'updated_at', 'time_completed'],

        self::COMMENT_USER_TYPE => ['comment_usertype_id', 'comment_usertype_name'],
        self::COMMENTS          => ['id', 'fk_comment_usertype_id', 'comment_id', 'comment_parent_id', 'comment_body', 'comment_status', 'comment_others', 'ip_bin', 'ip_to_text', 'agent', 'created_at', 'updated_at'],
        self::BROKEN_LINKS      => ['id', 'from', 'to', 'hit', 'redirection_type', 'others', 'created_at', 'updated_at'],
    ];
    // Backward Compatibility
    static array $DRIVE_SYSTEM_COLUMN = [
        'drive_id', 'drive_parent_id', 'drive_unique_id', 'drive_name', 'type', 'filename', 'status', 'properties', 'security',
    ];

    public function __construct () {}

    public static function getTable (string $tablename): string
    {
        if (!key_exists($tablename, self::$TABLES)) {
            throw new \InvalidArgumentException("`$tablename` is an invalid table name");
        }

        return DatabaseConfig::getPrefix() . $tablename;
    }

    /**
     * @param string $tablename
     *
     * @return bool
     */
    public static function isTable (string $tablename): bool
    {
        return isset(self::$TABLES[$tablename]);
    }

    /**
     * @param string $table
     * @param $col
     *
     * @return bool
     */
    public static function hasColumn (string $table, $col): bool
    {
        $column = self::$TABLES[$table];
        $column = array_flip($column);
        return key_exists($col, $column);
    }

    private static function DbTablePrefix (): string
    {
        return DatabaseConfig::getPrefix();
    }

    static function removeColumnFromTable (array $tableColumns, array $columnToRemove, $implodeColumns = false): array|string
    {
        if ($implodeColumns) {
            return implode(',', array_diff($tableColumns, $columnToRemove));
        }
        return array_diff($tableColumns, $columnToRemove);
    }

    static function addColumnsToTable (array $tableColumns, array $columnsToAdd, $implodeColumns = false): array|string
    {
        if ($implodeColumns) {
            return implode(',', array_merge($tableColumns, $columnsToAdd));
        }
        return array_merge($tableColumns, $columnsToAdd);
    }
}