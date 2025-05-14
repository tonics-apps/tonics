<?php
/*
 *     Copyright (c) 2024-2025. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Post\Services;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Library\View\CustomTokenizerState\SimpleShortCode\TonicsSimpleShortCode;
use App\Modules\Core\Services\SimpleShortCodeService;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsTemplateSystem\Loader\TonicsTemplateArrayLoader;

class PostService
{
    const QUERY_LOOP_SETTINGS_AUTHOR_IN = 'Author';
    const QUERY_LOOP_SETTINGS_AUTHOR_NOT_IN = 'AuthorNotIn';
    const QUERY_LOOP_SETTINGS_AUTHOR_OR_AUTHOR = 'OrAuthor';
    const QUERY_LOOP_SETTINGS_OR_AUTHOR_NOT_IN = 'OrAuthorNotIn';
    /**
     * By default, for id, category will search either cat_id (if int) or cat_slug (if string), you can use
     * QUERY_LOOP_SETTINGS_CATEGORY_ID_FIELD to change the field to say slug_id
     */
    const QUERY_LOOP_SETTINGS_CATEGORY_FIELD_NAME = 'CategoryFieldName';
    const QUERY_LOOP_SETTINGS_CATEGORY_IN = 'Category';
    const QUERY_LOOP_SETTINGS_CATEGORY_NOT_IN = 'CategoryNotIn';
    const QUERY_LOOP_SETTINGS_CATEGORY_OR_CATEGORY = 'OrCategory';
    const QUERY_LOOP_SETTINGS_CATEGORY_OR_CATEGORY_NOT_IN = 'OrCategoryNotIn';

    /**
     * By default, for id, post will search either post_id (if int) or post_title (if string), you can use
     * QUERY_LOOP_SETTINGS_POST_FIELD_NAME to change the field to say slug_id
     */
    const QUERY_LOOP_SETTINGS_POST_FIELD_NAME = 'POSTFieldName';
    const QUERY_LOOP_SETTINGS_POST_IN = 'Post';
    const QUERY_LOOP_SETTINGS_POST_OR_POST = 'OrPost';
    const QUERY_LOOP_SETTINGS_DATE_CREATED_TIME = 'CreatedTime';
    const QUERY_LOOP_SETTINGS_DATE_AFTER_CREATED_TIME = 'AfterCreatedTime';
    const QUERY_LOOP_SETTINGS_DATE_BEFORE_CREATED_TIME = 'BeforeCreatedTime';
    const QUERY_LOOP_SETTINGS_DATE_UPDATED_TIME = 'UpdatedTime';
    const QUERY_LOOP_SETTINGS_DATE_AFTER_UPDATED_TIME = 'AfterUpdatedTime';
    const QUERY_LOOP_SETTINGS_DATE_BEFORE_UPDATED_TIME = 'BeforeUpdatedTime';
    const QUERY_LOOP_SETTINGS_ORDER_DIRECTION_ASC = 'ASC';
    const QUERY_LOOP_SETTINGS_ORDER_DIRECTION_DESC = 'DESC';
    const QUERY_LOOP_SETTINGS_STATUS_TYPE_IN = 'Status';
    const QUERY_LOOP_SETTINGS_STATUS_TYPE_NOT_IN = 'StatusNotIn';
    const QUERY_LOOP_SETTINGS_STATUS_OR_STATUS = 'OrStatus';
    const QUERY_LOOP_SETTINGS_STATUS_OR_STATUS_NOT_IN = 'OrStatusNotIn';
    const QUERY_LOOP_SETTINGS_CHILDREN_NESTED = 'ChildrenNested';
    const QUERY_LOOP_SETTINGS_PAGINATION_PER_PAGE = 'PaginationParametersPerPage';
    const QUERY_LOOP_SETTINGS_SEARCH = 'searchParameter';

    /**
     * For CustomMeta
     */
    const QUERY_LOOP_SETTINGS_CUSTOM_META_IN = 'CustomMeta';
    const QUERY_LOOP_SETTINGS_CUSTOM_META_OR = 'OrCustomMeta';

    private static array $categorySettings = [];
    private static array $postSettings = [];

    /**
     * Example Usage:
     *
     * // Note, you can swap the id for slug
     *
     * ```
     * [
     *      // FOR AUTHOR
     *      PostService::QUERY_LOOP_SETTINGS_AUTHOR_IN,
     *      PostService::QUERY_LOOP_SETTINGS_AUTHOR_NOT_IN,
     *      PostService::QUERY_LOOP_SETTINGS_AUTHOR_OR_AUTHOR,
     *      PostService::QUERY_LOOP_SETTINGS_OR_AUTHOR_NOT_IN => " id='1,2,3' "
     *
     *      // FOR CATEGORY
     *      PostService::QUERY_LOOP_SETTINGS_CATEGORY_IN,
     *      PostService::QUERY_LOOP_SETTINGS_CATEGORY_NOT_IN,
     *      PostService::QUERY_LOOP_SETTINGS_CATEGORY_OR_CATEGORY,
     *      PostService::QUERY_LOOP_SETTINGS_CATEGORY_OR_CATEGORY_NOT_IN => " id='1,2,3' "
     *
     *       // FOR POST
     *       PostService::QUERY_LOOP_SETTINGS_POST_IN,
     *      PostService::QUERY_LOOP_SETTINGS_POST_OR_POST => " id='1,2,3' "
     *
     *      // FOR DATES
     *      PostService::QUERY_LOOP_SETTINGS_DATE_CREATED_TIME,
     *      PostService::QUERY_LOOP_SETTINGS_DATE_AFTER_CREATED_TIME,
     *      PostService::QUERY_LOOP_SETTINGS_DATE_BEFORE_CREATED_TIME,
     *      PostService::QUERY_LOOP_SETTINGS_DATE_UPDATED_TIME,
     *      PostService::QUERY_LOOP_SETTINGS_DATE_AFTER_UPDATED_TIME,
     *      PostService::QUERY_LOOP_SETTINGS_DATE_BEFORE_UPDATED_TIME  => "2024-08-04 04:38:05"
     *
     *      // FOR ORDER BY
     *      PostService::QUERY_LOOP_SETTINGS_ORDER_DIRECTION_ASC => 'post_id or post_title or post_slug or created_at or updated_at'
     *      PostService::QUERY_LOOP_SETTINGS_ORDER_DIRECTION_DESC => 'post_id or post_title or post_slug or created_at or updated_at'
     *
     *      // FOR STATUS
     *      PostService::QUERY_LOOP_SETTINGS_STATUS_TYPE_IN,
     *      PostService::QUERY_LOOP_SETTINGS_STATUS_TYPE_NOT_IN,
     *      PostService::QUERY_LOOP_SETTINGS_STATUS_OR_STATUS,
     *      PostService::QUERY_LOOP_SETTINGS_STATUS_OR_STATUS_NOT_IN => [1] // 1 is published, 0 is draft and -1 is trash
     *
     *      // FOR SEARCH
     *      PostService::QUERY_LOOP_SETTINGS_SEARCH => 'search-keyword',
     *
     *      // FOR CUSTOM META
     *      PostService::QUERY_LOOP_SETTINGS_CUSTOM_META_IN,
     *      PostService::QUERY_LOOP_SETTINGS_CUSTOM_META_OR => " KeyName='value1,value2,value3' ",
     * ]
     * ```
     *
     * @param array $settings
     *
     * @return array|object
     * @throws \Exception
     */
    public static function QueryLoop(array $settings): object|array
    {
        $postData = [];
        self::$postSettings = $settings;

        try {
            db(onGetDB: function (TonicsQuery $db) use ($settings, &$postData) {
                $postTable = self::PostTable();
                $cols = table()->pick([$postTable => ['post_id', 'post_title', 'slug_id', 'post_slug', 'post_status', 'field_settings', 'created_at', 'updated_at', 'image_url']]) . ', ' .
                    table()->pick([self::UserTable() => ['user_id', 'user_name', 'role']])
                    . ', CONCAT(cat_id, "::", cat_slug ) as fk_cat_id'
                    . ', CONCAT_WS("/", "/posts", post_slug) as _preview_link, post_title as _title'
                    . ", post_excerpt AS _excerpt"
                    . ", DATE_FORMAT($postTable.created_at, '%a, %d %b') as created_at_friendly"
                    . ", DATE_FORMAT($postTable.updated_at, '%a, %d %b %Y %T') as rssPubDate";

                $db->Select($cols)
                    ->From($postTable)
                    ->Join(
                        self::PostToCategoryTable(),
                        table()->pickTable(self::PostToCategoryTable(), ['fk_post_id']),
                        table()->pickTable($postTable, ['post_id']),
                    )
                    ->Join(
                        self::CategoryTable(),
                        table()->pickTable(self::CategoryTable(), ['cat_id']),
                        table()->pickTable(self::PostToCategoryTable(), ['fk_cat_id']),
                    )
                    ->Join(
                        self::UserTable(),
                        table()->pickTable(self::UserTable(), ['user_id']),
                        table()->pickTable($postTable, ['user_id']),
                    );

                $groupByInserted = false;
                $groupInsertedFunction = function () use ($db, $postTable, &$groupByInserted) {
                    if ($groupByInserted === false) {
                        $db->GroupBy(table()->pick([$postTable => ['slug_id']]));
                        $groupByInserted = true;
                    }
                };

                $orderedSettings = self::OrderSettings($settings);
                foreach ($orderedSettings as $key => $callBack) {

                    if (is_callable($callBack)) {

                        if ($key === PostService::QUERY_LOOP_SETTINGS_ORDER_DIRECTION_DESC || $key === PostService::QUERY_LOOP_SETTINGS_ORDER_DIRECTION_ASC) {
                            $groupInsertedFunction();
                        }

                        $callBack($db, $settings[$key], self::PostTable());
                    }
                }

                $groupInsertedFunction();
                // $postData = AbstractDataLayer::CursorPagination($db, ['slug_id'], url()->getParam('cursor'), limit: $settings[self::QUERY_LOOP_SETTINGS_PAGINATION_PER_PAGE] ?? AppConfig::getAppPaginationMax());
                $postData = $db->SimplePaginate($settings[self::QUERY_LOOP_SETTINGS_PAGINATION_PER_PAGE] ?? AppConfig::getAppPaginationMax());
            });
        } catch (\Throwable $exception) {
            // Log...
        }

        self::$postSettings = [];
        return $postData ?? [];
    }

    /**
     * @return string
     */
    public static function PostTable(): string
    {
        return Tables::getTable(Tables::POSTS);
    }

    /**
     * @return string
     */
    public static function UserTable(): string
    {
        return Tables::getTable(Tables::USERS);
    }

    /**
     * @return string
     */
    public static function PostToCategoryTable(): string
    {
        return Tables::getTable(Tables::POST_CATEGORIES);
    }

    /**
     * @return string
     */
    public static function CategoryTable(): string
    {
        return Tables::getTable(Tables::CATEGORIES);
    }

    /**
     * @param array $settings
     *
     * @return array
     */
    private static function OrderSettings(array $settings): array
    {
        $priorityOrder = self::LoopKeyAndCallback();
        // Filter settings based on the defined priority
        return array_filter($priorityOrder, function ($key) use ($settings) {
            return array_key_exists($key, $settings);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @return array
     */
    private static function LoopKeyAndCallback(): array
    {
        return [
            self::QUERY_LOOP_SETTINGS_SEARCH => function (TonicsQuery $db, $value, $type) {
                if ($type === self::CategoryTable()) {
                    $db->WhereLike('cat_name', $value);
                } else {
                    $db->WhereLike('post_title', $value);
                }
            },

            self::QUERY_LOOP_SETTINGS_AUTHOR_IN => function (TonicsQuery $db, $value) {
                self::ValidateValueAndCallBack($value,
                    [
                        'id' => function ($value) use ($db) {
                            $db->WhereIn(table()->pickTable(self::UserTable(), ['user_id']), $value);
                        },
                        'slug' => function ($value) use ($db) {
                            $db->WhereIn(table()->pickTable(self::UserTable(), ['user_name']), $value);
                        },
                    ],
                );
            },
            self::QUERY_LOOP_SETTINGS_AUTHOR_NOT_IN => function (TonicsQuery $db, $value) {
                self::ValidateValueAndCallBack($value,
                    [
                        'id' => function ($value) use ($db) {
                            $db->WhereNotIn(table()->pickTable(self::UserTable(), ['user_id']), $value);
                        },
                        'slug' => function ($value) use ($db) {
                            $db->WhereNotIn(table()->pickTable(self::UserTable(), ['user_name']), $value);
                        },
                    ],
                );
            },
            self::QUERY_LOOP_SETTINGS_AUTHOR_OR_AUTHOR => function (TonicsQuery $db, $value) {
                self::ValidateValueAndCallBack($value,
                    [
                        'id' => function ($value) use ($db) {
                            $db->OrWhereIn(table()->pickTable(self::UserTable(), ['user_id']), $value);
                        },
                        'slug' => function ($value) use ($db) {
                            $db->OrWhereIn(table()->pickTable(self::UserTable(), ['user_name']), $value);
                        },
                    ],
                );
            },
            self::QUERY_LOOP_SETTINGS_OR_AUTHOR_NOT_IN => function (TonicsQuery $db, $value) {
                self::ValidateValueAndCallBack($value,
                    [
                        'id' => function ($value) use ($db) {
                            $db->OrWhereNotIn(table()->pickTable(self::UserTable(), ['user_id']), $value);
                        },
                        'slug' => function ($value) use ($db) {
                            $db->OrWhereNotIn(table()->pickTable(self::UserTable(), ['user_name']), $value);
                        },
                    ],
                );
            },

            self::QUERY_LOOP_SETTINGS_CATEGORY_IN => function (TonicsQuery $db, $value) {

                $fieldName = null;
                if (isset(self::$categorySettings[self::QUERY_LOOP_SETTINGS_CATEGORY_FIELD_NAME])) {
                    $fieldName = self::$categorySettings[self::QUERY_LOOP_SETTINGS_CATEGORY_FIELD_NAME];
                }

                self::ValidateValueAndCallBack($value,
                    [
                        'id' => function ($value) use ($db, $fieldName) {
                            $db->WhereIn(table()->pickTable(self::CategoryTable(), [$fieldName ?? 'cat_id']), $value);
                        },
                        'slug' => function ($value) use ($db) {
                            $db->WhereIn(table()->pickTable(self::CategoryTable(), ['cat_slug']), $value);
                        },
                    ],
                );
            },
            self::QUERY_LOOP_SETTINGS_CATEGORY_NOT_IN => function (TonicsQuery $db, $value) {

                $fieldName = null;
                if (isset(self::$categorySettings[self::QUERY_LOOP_SETTINGS_CATEGORY_FIELD_NAME])) {
                    $fieldName = self::$categorySettings[self::QUERY_LOOP_SETTINGS_CATEGORY_FIELD_NAME];
                }

                self::ValidateValueAndCallBack($value,
                    [
                        'id' => function ($value) use ($db, $fieldName) {
                            $db->WhereNotIn(table()->pickTable(self::CategoryTable(), [$fieldName ?? 'cat_id']), $value);
                        },
                        'slug' => function ($value) use ($db) {
                            $db->WhereNotIn(table()->pickTable(self::CategoryTable(), ['cat_slug']), $value);
                        },
                    ],
                );
            },
            self::QUERY_LOOP_SETTINGS_CATEGORY_OR_CATEGORY => function (TonicsQuery $db, $value) {

                $fieldName = null;
                if (isset(self::$categorySettings[self::QUERY_LOOP_SETTINGS_CATEGORY_FIELD_NAME])) {
                    $fieldName = self::$categorySettings[self::QUERY_LOOP_SETTINGS_CATEGORY_FIELD_NAME];
                }

                self::ValidateValueAndCallBack($value,
                    [
                        'id' => function ($value) use ($db, $fieldName) {
                            $db->OrWhereIn(table()->pickTable(self::CategoryTable(), [$fieldName ?? 'cat_id']), $value);
                        },
                        'slug' => function ($value) use ($db) {
                            $db->OrWhereIn(table()->pickTable(self::CategoryTable(), ['cat_slug']), $value);
                        },
                    ],
                );
            },
            self::QUERY_LOOP_SETTINGS_CATEGORY_OR_CATEGORY_NOT_IN => function (TonicsQuery $db, $value) {

                $fieldName = null;
                if (isset(self::$categorySettings[self::QUERY_LOOP_SETTINGS_CATEGORY_FIELD_NAME])) {
                    $fieldName = self::$categorySettings[self::QUERY_LOOP_SETTINGS_CATEGORY_FIELD_NAME];
                }

                self::ValidateValueAndCallBack($value,
                    [
                        'id' => function ($value) use ($db, $fieldName) {
                            $db->OrWhereNotIn(table()->pickTable(self::CategoryTable(), [$fieldName ?? 'cat_id']), $value);
                        },
                        'slug' => function ($value) use ($db) {
                            $db->OrWhereNotIn(table()->pickTable(self::CategoryTable(), ['cat_slug']), $value);
                        },
                    ],
                );
            },
            self::QUERY_LOOP_SETTINGS_CUSTOM_META_IN => function (TonicsQuery $db, $value) {
                $customMetaPrefix = "_CustomMeta_";
                $metas = self::ElementsAttribute($value ?? '');
                foreach ($metas as $metaKey => $metaValues) {
                    $metaKey = $customMetaPrefix . $metaKey;
                    foreach ($metaValues as $metaValue) {
                        $metaValue = '"' . $metaValue . '"';
                        $db->WhereJsonContains(table()->pickTable(self::PostTable(), ['field_settings']), $metaKey, $metaValue);
                    }
                }
            },
            self::QUERY_LOOP_SETTINGS_CUSTOM_META_OR => function (TonicsQuery $db, $value) {
                $customMetaPrefix = "_CustomMeta_";
                $metas = self::ElementsAttribute($value ?? '');
                foreach ($metas as $metaKey => $metaValues) {
                    $metaKey = $customMetaPrefix . $metaKey;
                    foreach ($metaValues as $metaValue) {
                        $metaValue = '"' . $metaValue . '"';
                        $db->WhereJsonContains(table()->pickTable(self::PostTable(), ['field_settings']), $metaKey, $metaValue, ifWhereUse: 'OR');
                    }
                }
            },
            self::QUERY_LOOP_SETTINGS_POST_IN => function (TonicsQuery $db, $value) {

                $fieldName = null;
                if (isset(self::$postSettings[self::QUERY_LOOP_SETTINGS_POST_FIELD_NAME])) {
                    $fieldName = self::$postSettings[self::QUERY_LOOP_SETTINGS_POST_FIELD_NAME];
                }

                self::ValidateValueAndCallBack($value,
                    [
                        'id' => function ($value) use ($db, $fieldName) {
                            $db->WhereIn(table()->pickTable(self::PostTable(), [$fieldName ?? 'post_id']), $value);
                        },
                        'slug' => function ($value) use ($db) {
                            $db->WhereIn(table()->pickTable(self::PostTable(), ['post_slug']), $value);
                        },
                    ],
                );
            },
            self::QUERY_LOOP_SETTINGS_POST_OR_POST => function (TonicsQuery $db, $value) {

                $fieldName = null;
                if (isset(self::$postSettings[self::QUERY_LOOP_SETTINGS_POST_FIELD_NAME])) {
                    $fieldName = self::$postSettings[self::QUERY_LOOP_SETTINGS_POST_FIELD_NAME];
                }

                self::ValidateValueAndCallBack($value,
                    [
                        'id' => function ($value) use ($db, $fieldName) {
                            $db->OrWhereIn(table()->pickTable(self::PostTable(), [$fieldName ?? 'post_id']), $value);
                        },
                        'slug' => function ($value) use ($db) {
                            $db->OrWhereIn(table()->pickTable(self::PostTable(), ['post_slug']), $value);
                        },
                    ],
                );
            },

            self::QUERY_LOOP_SETTINGS_DATE_CREATED_TIME => function (TonicsQuery $db, $value, $type) {
                $db->WhereEquals(table()->pickTable($type, ['created_at']), helper()->date(datetime: $value));
            },
            self::QUERY_LOOP_SETTINGS_DATE_AFTER_CREATED_TIME => function (TonicsQuery $db, $value, $type) {
                $db->Where(table()->pickTable($type, ['created_at']), '>', helper()->date(datetime: $value));
            },
            self::QUERY_LOOP_SETTINGS_DATE_BEFORE_CREATED_TIME => function (TonicsQuery $db, $value, $type) {
                $db->Where(table()->pickTable($type, ['created_at']), '<', helper()->date(datetime: $value));
            },
            self::QUERY_LOOP_SETTINGS_DATE_UPDATED_TIME => function (TonicsQuery $db, $value, $type) {
                $db->Where(table()->pickTable($type, ['updated_at']), '=', helper()->date(datetime: $value));
            },
            self::QUERY_LOOP_SETTINGS_DATE_AFTER_UPDATED_TIME => function (TonicsQuery $db, $value, $type) {
                $db->Where(table()->pickTable($type, ['updated_at']), '>', helper()->date(datetime: $value));
            },
            self::QUERY_LOOP_SETTINGS_DATE_BEFORE_UPDATED_TIME => function (TonicsQuery $db, $value, $type) {
                $db->Where(table()->pickTable($type, ['updated_at']), '<', helper()->date(datetime: $value));
            },

            self::QUERY_LOOP_SETTINGS_STATUS_TYPE_IN => function (TonicsQuery $db, $value, $type) {
                if ($type === self::CategoryTable()) {
                    $db->WhereIn(table()->pickTable($type, ['cat_status']), $value);
                } else {
                    $db->WhereIn(table()->pickTable(self::PostTable(), ['post_status']), $value);
                }
            },
            self::QUERY_LOOP_SETTINGS_STATUS_TYPE_NOT_IN => function (TonicsQuery $db, $value, $type) {
                if ($type === self::CategoryTable()) {
                    $db->WhereNotIn(table()->pickTable($type, ['cat_status']), $value);
                } else {
                    $db->WhereNotIn(table()->pickTable(self::PostTable(), ['cat_status']), $value);
                }
            },
            self::QUERY_LOOP_SETTINGS_STATUS_OR_STATUS => function (TonicsQuery $db, $value, $type) {
                if ($type === self::CategoryTable()) {
                    $db->OrWhereIn(table()->pickTable($type, ['cat_status']), $value);
                } else {
                    $db->OrWhereIn(table()->pickTable(self::PostTable(), ['post_status']), $value);
                }
            },
            self::QUERY_LOOP_SETTINGS_STATUS_OR_STATUS_NOT_IN => function (TonicsQuery $db, $value, $type) {
                if ($type === self::CategoryTable()) {
                    $db->OrWhereNotIn(table()->pickTable($type, ['cat_status']), $value);
                } else {
                    $db->OrWhereNotIn(table()->pickTable(self::PostTable(), ['post_status']), $value);
                }
            },
            self::QUERY_LOOP_SETTINGS_ORDER_DIRECTION_ASC => function (TonicsQuery $db, $value, $type) {
                $db->OrderByAsc(table()->pickTable($type, [$value]));
            },
            self::QUERY_LOOP_SETTINGS_ORDER_DIRECTION_DESC => function (TonicsQuery $db, $value, $type) {
                $db->OrderByDesc(table()->pickTable($type, [$value]));
            },
        ];
    }

    /**
     * The keys should be in the format:
     *
     * ```
     * [
     *      // if the key id is valid, the callback would fire
     *      'id' => function($value){},
     * ]
     * ```
     *
     * @param string $value
     * @param array $keys
     *
     * @return void
     */
    private static function ValidateValueAndCallBack(string $value, array $keys): void
    {
        $matched = false;
        $attributes = self::ElementsAttribute($value);

        $fireAttributes = function ($attributes) use ($keys, &$matched) {
            foreach ($keys as $key => $callback) {
                if (isset($attributes[$key])) {
                    $matched = true;
                    $callback($attributes[$key]);
                }
            }
        };

        $fireAttributes($attributes);

        if (!$matched && isset($value[0])) {
            $firstChar = $value[0];
            if (is_numeric($firstChar)) {
                $attributes = self::ElementsAttribute("id='$value'");
            } else {
                $attributes = self::ElementsAttribute("slug='$value'");
            }
            $fireAttributes($attributes);
        }
    }

    /**
     * @param string $content
     *
     * @return array
     */
    private static function ElementsAttribute(string $content): array
    {
        $content = trim($content);
        $attributes = [];

        if (!empty($content)) {
            $render = SimpleShortCodeService::GlobalVariableShortCodeCustomRendererForAttributes();
            $shortCode = new TonicsSimpleShortCode([
                'render' => $render,
            ]);

            $shortCode->getView()->addModeHandler('attributes', SimpleShortCodeService::AttributesShortCode()::class);
            $shortCode->getView()->setTemplateLoader(new TonicsTemplateArrayLoader(['template' => "[attributes $content]"]))
                ->render('template');

            $attributes = $render->getArgs();
        }

        // Trim the trailing space and return the result
        return array_map(function ($value) {
            return array_map('trim', explode(',', $value));
        }, $attributes);
    }

    /**
     * Example Usage:
     *
     * // Note, you can swap the id for slug
     *
     * ```
     * [
     *      // FOR CATEGORY
     *      // This will force the category in* to search by the field name
     *      PostService::QUERY_LOOP_SETTINGS_CATEGORY_FIELD_NAME => 'any-category-field, e.g, slug_id'
     *
     *      PostService::QUERY_LOOP_SETTINGS_CATEGORY_IN,
     *      PostService::QUERY_LOOP_SETTINGS_CATEGORY_NOT_IN,
     *      PostService::QUERY_LOOP_SETTINGS_CATEGORY_OR_CATEGORY,
     *      PostService::QUERY_LOOP_SETTINGS_CATEGORY_OR_CATEGORY_NOT_IN => " id='1,2,3' ",
     *
     *      // FOR DATES
     *      PostService::QUERY_LOOP_SETTINGS_DATE_CREATED_TIME,
     *      PostService::QUERY_LOOP_SETTINGS_DATE_AFTER_CREATED_TIME,
     *      PostService::QUERY_LOOP_SETTINGS_DATE_BEFORE_CREATED_TIME,
     *      PostService::QUERY_LOOP_SETTINGS_DATE_UPDATED_TIME,
     *      PostService::QUERY_LOOP_SETTINGS_DATE_AFTER_UPDATED_TIME,
     *      PostService::QUERY_LOOP_SETTINGS_DATE_BEFORE_UPDATED_TIME  => "2024-08-04 04:38:05",
     *
     *      // FOR ORDER BY
     *      PostService::QUERY_LOOP_SETTINGS_ORDER_DIRECTION_ASC => 'post_id or post_title or post_slug or created_at or updated_at',
     *      PostService::QUERY_LOOP_SETTINGS_ORDER_DIRECTION_DESC => 'post_id or post_title or post_slug or created_at or updated_at',
     *
     *      // FOR STATUS
     *      PostService::QUERY_LOOP_SETTINGS_STATUS_TYPE_IN,
     *      PostService::QUERY_LOOP_SETTINGS_STATUS_TYPE_NOT_IN,
     *      PostService::QUERY_LOOP_SETTINGS_STATUS_OR_STATUS,
     *      PostService::QUERY_LOOP_SETTINGS_STATUS_OR_STATUS_NOT_IN => [1], // 1 is published, 0 is draft and -1 is trash
     *
     *      // FOR SEARCH
     *      PostService::QUERY_LOOP_SETTINGS_SEARCH => 'search-keyword',
     *      // Include Children
     *      PostService::QUERY_LOOP_SETTINGS_CHILDREN_NESTED => true | false
     * ]
     * ```
     *
     * @param array $settings
     *
     * @return array|object
     * @throws \Exception
     */
    public static function QueryLoopCategory(array $settings): array|object
    {
        $categoryData = [];
        self::$categorySettings = $settings;

        try {
            db(onGetDB: function (TonicsQuery $db) use ($settings, &$categoryData) {

                $recursive = $settings[self::QUERY_LOOP_SETTINGS_CHILDREN_NESTED] ?? false;
                $recursive = (bool)$recursive;

                $db->With(self::CategoryTable(),
                    db()->Select('cat_id, slug_id, cat_parent_id, cat_name, cat_slug, cat_status, field_settings, created_at, updated_at')
                        ->From(self::CategoryTable())
                        ->when(true, function (TonicsQuery $db) use ($settings) {
                            $orderedSettings = self::OrderSettings($settings);
                            foreach ($orderedSettings as $key => $callBack) {
                                if (is_callable($callBack)) {
                                    $callBack($db, $settings[$key], self::CategoryTable());
                                }
                            }
                        })
                        ->when($recursive, function (TonicsQuery $db) {
                            $db->UnionAll(
                                db()->Select('c.cat_id, c.slug_id, c.cat_parent_id, c.cat_name, c.cat_slug, c.cat_status, c.field_settings, c.created_at, c.updated_at')
                                    ->From('tonics_categories c')
                                    ->Join('category_tree ct', 'c.cat_parent_id', 'ct.cat_id'),
                            );
                        }),
                    $recursive,
                );

                $categoryData = $db->Select('*')
                    ->From(self::CategoryTable());

                $orderedSettings = self::OrderSettings($settings);

                foreach ($orderedSettings as $key => $callBack) {
                    if (is_callable($callBack)) {
                        $callBack($db, $settings[$key], self::CategoryTable());
                    }
                }

                $categoryData = $db->SimplePaginate($settings[self::QUERY_LOOP_SETTINGS_PAGINATION_PER_PAGE] ?? AppConfig::getAppPaginationMax());
                // $categoryData = AbstractDataLayer::CursorPagination($db, ['slug_id'], url()->getParam('cursor'), limit: $settings[self::QUERY_LOOP_SETTINGS_PAGINATION_PER_PAGE] ?? AppConfig::getAppPaginationMax());
            });
        } catch (\Throwable $exception) {
            // Log...
        }

        self::$categorySettings = [];

        return $categoryData ?? [];
    }

    /**
     * @param mixed $data
     * @param bool $onlyFirstValue
     *
     * @return array|mixed
     */
    public static function GrabQueryData(mixed $data, bool $onlyFirstValue = false): mixed
    {
        if (self::QueryHasData($data)) {

            if ($onlyFirstValue) {
                return $data->data[0];
            }

            return $data->data;
        }

        return [];
    }

    /**
     * @param mixed $data
     *
     * @return bool
     */
    public static function QueryHasData(mixed $data): bool
    {
        return !empty($data->data);
    }

}