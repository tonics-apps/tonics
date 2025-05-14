<?php
/*
 *     Copyright (c) 2025. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Track\Services;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\View\CustomTokenizerState\SimpleShortCode\TonicsSimpleShortCode;
use App\Modules\Core\Services\SimpleShortCodeService;
use App\Modules\Track\Data\TrackData;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsTemplateSystem\Loader\TonicsTemplateArrayLoader;
use stdClass;

class TrackService
{
    const QUERY_LOOP_SETTINGS_TRACK_CAT_ID = 'track_cat_id';
    const QUERY_LOOP_SETTINGS_TRACK_CAT_SLUG_ID = 'track_cat_slug_id';
    const QUERY_LOOP_SETTINGS_IS_FILTERING = 'isFiltering';
    const QUERY_LOOP_SETTINGS_PAGINATION_PER_PAGE = 'perPage';
    const QUERY_LOOP_SETTING_SEARCH_TERM = 'query';
    const QUERY_LOOP_SETTINGS_TRACK_FIELD_NAME = 'TrackFieldName';
    const QUERY_LOOP_SETTINGS_TRACK_IN = 'Track';
    const QUERY_LOOP_SETTINGS_TRACK_OR_TRACK = 'OrTrack';

    private static array $trackSettings = [];
    private static array $categorySettings = [];

    /**
     * Example Usage:
     *
     * ```
     * [
     *      // FOR TRACK
     *      TrackService::QUERY_LOOP_SETTINGS_TRACK_FIELD_NAME => 'track_id', // or slug_id
     *      TrackService::QUERY_LOOP_SETTINGS_TRACK_IN => "id='1,2,3'", // or slug='slug1,slug2'
     *
     *      // FOR CATEGORY
     *      TrackService::QUERY_LOOP_SETTINGS_TRACK_CAT_ID => 1,
     *
     *      // For Pagination
     *      TrackService::QUERY_LOOP_SETTINGS_PAGINATION_PER_PAGE => 10,
     *
     *      // For Search
     *      TrackService::QUERY_LOOP_SETTING_QUERY => 'Search Query',
     *
     *      // FOR FILTERING
     *      TrackService::QUERY_LOOP_SETTINGS_IS_FILTERING => [
     *          'bpm' => [120, 130],
     *          'track_key' => ['C', 'D'],
     *          'track_genres' => ['Rock', 'Jazz'],
     *          'track_artist' => ['Artist1', 'Artist2'],
     *          'mood' => ['Happy', 'Sad'],
     *          'instrument' => ['Guitar', 'Piano'],
     *          'samplePackType' => ['Type1', 'Type2'],
     *          'acapellaGender' => ['Male', 'Female'],
     *          'acapellaVocalStyle' => ['Style1', 'Style2'],
     *          'acapellaEmotion' => ['Emotion1', 'Emotion2'],
     *          'acapellaScale' => ['Scale1', 'Scale2'],
     *          'acapellaEffects' => ['Effect1', 'Effect2'],
     *      ],
     * ]
     * ```
     */
    public static function QueryLoop(array $settings = []): ?object
    {
        $trackData = TrackData::class;
        self::$trackSettings = $settings;

        try {
            $data = null;
            $trackCatId = $settings[self::QUERY_LOOP_SETTINGS_TRACK_CAT_ID] ?? null;
            db(onGetDB: function (TonicsQuery $db) use ($trackCatId, $trackData, &$data) {
                $settings = self::$trackSettings;
                $isFiltering = $settings[self::QUERY_LOOP_SETTINGS_IS_FILTERING] ?? [];

                $data = $db->Select("t.track_id as id, t.slug_id, t.track_title as _name, t.track_plays as plays,
                                   t.track_bpm as bpm, t.image_url, t.audio_url, tl.license_attr, t.track_status as _status,
                                   t.created_at, CONCAT_WS('/', '/tracks', t.slug_id, t.track_slug) as _link,
                                   ct.track_cat_id as _cat_id, ct.track_cat_name as _category")
                    ->From("{$trackData::getTrackTable()} t")
                    ->Join("{$trackData::getTrackTracksCategoryTable()} ttc", "t.track_id", "ttc.fk_track_id")
                    ->Join("{$trackData::getTrackCategoryTable()} ct", "ttc.fk_track_cat_id", "ct.track_cat_id")
                    ->Join("{$trackData::getLicenseTable()} tl", "tl.license_id", "t.fk_license_id")
                    ->when(!empty($isFiltering), function (TonicsQuery $db) use ($trackData, $isFiltering) {
                        $db->LeftJoin("{$trackData::getTrackDefaultFiltersTrackTable()} tdft", "t.track_id", "tdft.fk_track_id")
                            ->LeftJoin("{$trackData::getTrackDefaultFiltersTable()} tdf", "tdft.fk_tdf_id", "tdf.tdf_id");
                        self::applyFiltering($db, $isFiltering);
                    })
                    ->when($trackCatId, function (TonicsQuery $db) use ($trackData, $trackCatId) {
                        // First get the category and all its children
                        $categoryHierarchy = $db->Q()->run("
                        WITH RECURSIVE category_tree AS (
                            -- Base case: get the root category
                            SELECT track_cat_id, track_cat_parent_id
                            FROM {$trackData::getTrackCategoryTable()}
                            WHERE track_cat_id = ?

                            UNION ALL

                            -- Recursive case: get all children
                            SELECT c.track_cat_id, c.track_cat_parent_id
                            FROM {$trackData::getTrackCategoryTable()} c
                            INNER JOIN category_tree ct ON ct.track_cat_id = c.track_cat_parent_id
                        )
                        SELECT track_cat_id FROM category_tree;
                    ", $trackCatId);

                        // Extract all category IDs
                        $categoryIDS = array_column($categoryHierarchy, 'track_cat_id');
                        // Use these IDs in the WHERE clause
                        if (!empty($categoryIDS)) {
                            $db->WhereIn('ct.track_cat_id', $categoryIDS);
                        } else {
                            $db->WhereEquals('ct.track_cat_id', $trackCatId);
                        }
                    })
                    ->when(isset($settings[self::QUERY_LOOP_SETTINGS_TRACK_IN]), function (TonicsQuery $db) use ($settings) {
                        self::ValidateValueAndCallBack($settings[self::QUERY_LOOP_SETTINGS_TRACK_IN],
                            [
                                'id' => function ($value) use ($db) {
                                    $fieldName = self::$trackSettings[self::QUERY_LOOP_SETTINGS_TRACK_FIELD_NAME] ?? 'track_id';
                                    $db->WhereIn("t.$fieldName", $value);
                                },
                                'slug' => function ($value) use ($db) {
                                    $db->WhereIn("t.slug_id", $value);
                                },
                            ]
                        );
                    })
                    ->when(isset($settings[self::QUERY_LOOP_SETTINGS_TRACK_OR_TRACK]), function (TonicsQuery $db) use ($settings) {
                        self::ValidateValueAndCallBack($settings[self::QUERY_LOOP_SETTINGS_TRACK_OR_TRACK],
                            [
                                'id' => function ($value) use ($db) {
                                    $fieldName = self::$trackSettings[self::QUERY_LOOP_SETTINGS_TRACK_FIELD_NAME] ?? 'track_id';
                                    $db->OrWhereIn("t.$fieldName", $value);
                                },
                                'slug' => function ($value) use ($db) {
                                    $db->OrWhereIn("t.slug_id", $value);
                                },
                            ]
                        );
                    })
                    ->when(isset($settings[self::QUERY_LOOP_SETTING_SEARCH_TERM]), function (TonicsQuery $db) use ($settings) {
                        $db->WhereLike('t.track_title', $settings[self::QUERY_LOOP_SETTING_SEARCH_TERM]);
                    })
                    ->WhereEquals('t.track_status', 1)
                    ->Where('t.created_at', '<=', helper()->date())
                    ->GroupBy("t.slug_id")
                    ->OrderByDesc("t.track_plays")
                    ->SimplePaginate($settings[self::QUERY_LOOP_SETTINGS_PAGINATION_PER_PAGE] ?? AppConfig::getAppPaginationMax());
            });

            if (isset($data->data[0])) {
                $data->filters = self::QueryCategoryFilters($trackCatId);
            }

            return $data;
        } catch (\Exception $exception) {
            // Log...
        }

        return new stdClass();
    }

    /**
     * @param TonicsQuery $db
     * @param array $isFiltering
     *
     * @return TonicsQuery
     * @throws \Exception
     * @throws \Throwable
     */
    public static function applyFiltering(TonicsQuery $db, array $isFiltering): TonicsQuery
    {
        $keys = self::getFilterKeys();
        foreach ($keys as $param => $filterType) {
            if (isset($isFiltering[$param])) {
                $keyValues = $isFiltering[$param];
                $db->OrWhereEquals('tdf.tdf_type', $filterType)->WhereIn('tdf.tdf_name', $keyValues);
            }
        }
        return $db;
    }

    public static function getFilterKeys()
    {
        return [
            'bpm' => 'bpm',
            'track_key' => 'key',
            'track_genres' => 'genre',
            'track_artist' => 'artist',
            'mood' => 'mood',
            'instrument' => 'instrument',
            'samplePackType' => 'samplePackType',
            'acapellaGender' => 'acapellaGender',
            'acapellaVocalStyle' => 'acapellaVocalStyle',
            'acapellaEmotion' => 'acapellaEmotion',
            'acapellaScale' => 'acapellaScale',
            'acapellaEffects' => 'acapellaEffects',
        ];
    }

    /**
     * Helper method to validate and execute callbacks for track lookups
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
     * @param $trackCatID
     * @return array|null
     * @throws \Exception
     */
    public static function QueryCategoryFilters($trackCatID): ?array
    {
        $trackData = TrackData::class;
        $filters = array_values(self::getFilterKeys());
        $filterType = '';
        $last = array_key_last($filters);
        foreach ($filters as $filterKey => $filter) {
            $filterType .= <<<SQL
'$filter'
SQL;
            if ($filterKey !== $last) {
                $filterType .= ',';
            }
        }

        $filterOptions = new stdClass();
        db(onGetDB: function (TonicsQuery $db) use ($filterType, $trackCatID, $trackData, &$filterOptions) {
            if ($trackCatID === null) {
                // Fetch all filter options without category filtering
                $filterOptions = $db->run(<<<FILTER_OPTION
SELECT tdf_type, JSON_ARRAYAGG(DISTINCT tdf.tdf_name) as filter_values
FROM {$trackData::getTrackDefaultFiltersTable()} tdf
JOIN {$trackData::getTrackDefaultFiltersTrackTable()} tdft ON tdf.tdf_id = tdft.fk_tdf_id
JOIN {$trackData::getTrackTable()} t ON tdft.fk_track_id = t.track_id
WHERE t.track_status = 1 AND tdf.tdf_type IN ($filterType)
GROUP BY tdf_type;
FILTER_OPTION
                );
            } else {
                // Existing logic for category-specific filtering
                $filterOptions = $db->run(<<<FILTER_OPTION
SELECT tdf_type, JSON_ARRAYAGG(DISTINCT tdf.tdf_name) as filter_values
FROM {$trackData::getTrackDefaultFiltersTable()} tdf
JOIN {$trackData::getTrackDefaultFiltersTrackTable()} tdft ON tdf.tdf_id = tdft.fk_tdf_id
JOIN {$trackData::getTrackTable()} t ON tdft.fk_track_id = t.track_id
JOIN {$trackData::getTrackTracksCategoryTable()} ttc ON t.track_id = ttc.fk_track_id
JOIN (
  SELECT track_cat_id
  FROM {$trackData::getTrackCategoryTable()}
  WHERE track_cat_id = ? OR track_cat_parent_id = ?
) category_tree ON ttc.fk_track_cat_id = category_tree.track_cat_id
WHERE t.track_status = 1 AND tdf.tdf_type IN ($filterType)
GROUP BY tdf_type;
FILTER_OPTION, $trackCatID, $trackCatID);
            }
        });

        return $filterOptions;
    }

    /**
     * @param string $slugID
     * @return array|false
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function TrackPageLayout(string $slugID): array|false
    {
        $track = TrackService::TrackBySlugID($slugID);
        if (isset($track['field_settings'])) {
            return FieldConfig::quickProcessLogicFieldDetails($track, 'track_content');
        } else {
            return false;
        }
    }

    /**
     * Retrieves a single track by its unique slug ID.
     *
     * @param string $trackUniqueSlugID
     *
     * @return array|null
     * @throws \Exception|\Throwable
     */
    public static function TrackBySlugID(string $trackUniqueSlugID): ?array
    {
        /** @var TrackData $trackData */
        $trackData = container()->get(TrackData::class);

        try {
            $track = null;
            db(onGetDB: function (TonicsQuery $db) use ($trackData, $trackUniqueSlugID, &$track) {
                $track = $db->Select("t.track_id as id, t.slug_id, t.track_title as _name, null as num_tracks, t.track_plays as plays,
        t.track_bpm as bpm, t.image_url, t.audio_url, tl.license_attr, t.field_settings,
        ta.artist_name as artist_name, ta.artist_slug as artist_slug, g.genre_slug as genre_slug,
        t.created_at, 
        GROUP_CONCAT(CONCAT(track_cat_id) ) as fk_cat_id,
        1 as is_track, CONCAT_WS('/', '/tracks', t.slug_id, t.track_slug) as _link")
                    ->From("{$trackData::getTrackTable()} t")
                    ->Join("{$trackData::getTrackToGenreTable()} tg", "tg.fk_track_id", "t.track_id")
                    ->Join("{$trackData::getGenreTable()} g", "g.genre_id", "tg.fk_genre_id")
                    ->Join("{$trackData::getTrackTracksCategoryTable()} ttc", "t.track_id", "ttc.fk_track_id")
                    ->Join("{$trackData::getTrackCategoryTable()} ct", "ttc.fk_track_cat_id", "ct.track_cat_id")
                    ->Join("{$trackData::getLicenseTable()} tl", "tl.license_id", "t.fk_license_id")
                    ->Join("{$trackData::getArtistTable()} ta", "ta.artist_id", "t.fk_artist_id")
                    ->Where('t.created_at', '<=', helper()->date())
                    ->WhereEquals('t.track_status', 1)
                    ->WhereEquals("t.slug_id", $trackUniqueSlugID)
                    ->setPdoFetchType(\PDO::FETCH_ASSOC)
                    ->FetchFirst();
            });

            if (!is_array($track)) {
                return [];
            } else {
                if (isset($track['fk_cat_id'])) {
                    $categories = explode(',', $track['fk_cat_id']);
                    $categories = array_combine($categories, $categories);
                    $categories = array_values($categories);
                    foreach ($categories as $category) {
                        $reverseCategory = array_reverse(self::getTrackCategoryParents($category));
                        $track['categories'][] = $reverseCategory;
                    }
                }
            }

            return $track;
        } catch (\Exception $exception) {
            // Log the exception...
            return null;
        }
    }

    /**
     * @param string|int $idSlug
     *
     * @return mixed|null
     * @throws \Exception
     */
    public static function getTrackCategoryParents(string|int $idSlug): mixed
    {
        $result = null;
        db(onGetDB: function ($db) use ($idSlug, &$result) {
            $categoryTable = TrackData::getTrackCategoryTable();

            $where = "track_cat_slug = ?";
            if (is_numeric($idSlug)) {
                $where = "track_cat_id = ?";
            }
            $result = $db->run("
        WITH RECURSIVE child_to_parent AS 
	( SELECT track_cat_id, track_cat_parent_id, slug_id, track_cat_slug, track_cat_status, track_cat_name, CAST(track_cat_slug AS VARCHAR (255))
            AS path
      FROM $categoryTable WHERE $where
      UNION ALL
      SELECT fr.track_cat_id, fr.track_cat_parent_id, fr.slug_id, fr.track_cat_slug, fr.track_cat_status, fr.track_cat_name, CONCAT(fr.track_cat_slug, '/', path)
      FROM $categoryTable as fr INNER JOIN child_to_parent as cp ON fr.track_cat_id = cp.track_cat_parent_id
      ) 
     SELECT *, track_cat_name as _name, CONCAT_WS('/', '/track_categories', slug_id, track_cat_slug) as _link FROM child_to_parent;
        ", $idSlug);
        });

        return $result;
    }

    /**
     * @param string $slugID
     * @return array|false
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function TrackCategoryPageLayout(string $slugID): array|false
    {
        $trackCategory = TrackService::QueryLoopCategory([
            TrackService::QUERY_LOOP_SETTINGS_TRACK_CAT_SLUG_ID => $slugID,
        ]);

        if (isset($trackCategory->data[0])) {
            $trackCategory = $trackCategory->data[0];
            if (isset($trackCategory->field_settings)) {
                return FieldConfig::quickProcessLogicFieldDetails($trackCategory, 'track_cat_content');
            }
        }

        return false;
    }

    /**
     * Example Usage:
     *
     * ```
     * [
     *      // FOR CATEGORY
     *      TrackService::QUERY_LOOP_SETTINGS_TRACK_CAT_ID => 1,
     *
     *      // FOR CATEGORY SLUG_ID
     *      TrackService::QUERY_LOOP_SETTINGS_TRACK_CAT_SLUG_ID => '336bf73625c49e83',
     *
     *      // For Pagination
     *      TrackService::QUERY_LOOP_SETTINGS_PAGINATION_PER_PAGE => 10,
     *
     *      // For Search
     *      TrackService::QUERY_LOOP_SETTING_SEARCH_TERM => 'Search Query',
     * ]
     * ```
     *
     * @param array $settings
     *
     * @return object|null
     * @throws \Exception|\Throwable
     */
    public static function QueryLoopCategory(array $settings = []): ?object
    {
        $trackData = TrackData::class;
        self::$categorySettings = $settings;

        try {
            $data = null;
            db(onGetDB: function (TonicsQuery $db) use ($trackData, &$data) {
                $settings = self::$categorySettings;
                $trackCatId = $settings[self::QUERY_LOOP_SETTINGS_TRACK_CAT_ID] ?? null;
                $trackCatSlugId = $settings[self::QUERY_LOOP_SETTINGS_TRACK_CAT_SLUG_ID] ?? null;

                $data = $db->Select("ct.track_cat_id as id, ct.slug_id, ct.field_settings, ct.track_cat_parent_id as parent_id, 
                ct.track_cat_name as _name, ct.track_cat_slug as slug, ct.track_cat_status as _status,
                CONCAT_WS('/', '/track_categories', ct.slug_id, ct.track_cat_slug) as _link,
                             ct.created_at, ct.updated_at")
                    ->From("{$trackData::getTrackCategoryTable()} ct")
                    ->when($trackCatId, function (TonicsQuery $db) use ($trackCatId) {
                        $db->WhereEquals('ct.track_cat_id', $trackCatId);
                    })->when($trackCatSlugId, function (TonicsQuery $db) use ($trackCatSlugId) {
                        $db->WhereEquals('ct.slug_id', $trackCatSlugId);
                    })
                    ->when(isset($settings[self::QUERY_LOOP_SETTING_SEARCH_TERM]), function (TonicsQuery $db) use ($settings) {
                        $db->WhereLike('ct.track_cat_name', $settings[self::QUERY_LOOP_SETTING_SEARCH_TERM]);
                    })
                    ->WhereEquals('ct.track_cat_status', 1)
                    ->OrderByDesc("ct.created_at")
                    ->SimplePaginate($settings[self::QUERY_LOOP_SETTINGS_PAGINATION_PER_PAGE] ?? AppConfig::getAppPaginationMax());
            });

            return $data;
        } catch (\Exception $exception) {
            // Log...
        }

        return new stdClass();
    }
}