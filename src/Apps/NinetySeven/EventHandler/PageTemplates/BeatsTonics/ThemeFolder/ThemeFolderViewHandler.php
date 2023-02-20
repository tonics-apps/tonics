<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\NinetySeven\EventHandler\PageTemplates\BeatsTonics\ThemeFolder;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Field\Events\OnFieldFormHelper;
use App\Modules\Track\Data\TrackData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsRouterSystem\Exceptions\URLNotFound;
use Devsrealm\TonicsTemplateSystem\TonicsView;
use PDO;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class ThemeFolderViewHandler implements HandlerInterface
{

    const TonicsBeatsTonicsKey = 'TonicsBeatsTonics_Theme';

    public function handleEvent(object $event): void
    {
        /** @var $event OnHookIntoTemplate */
        $event->hookInto('tonics_folder_main', function (TonicsView $tonicsView){
            return $this->handleFolderFragment($tonicsView);
        });

        $event->hookInto('tonics_folder_main_from_api', function (TonicsView $tonicsView){
            $isFolder = url()->getHeaderByKey('type') === 'isTonicsNavigation';
            if ($isFolder){
                $data = [
                    'isFolder' => true,
                    'title' => $tonicsView->accessArrayWithSeparator('Data.seo_title'),
                    'fragment' => $this->handleFolderFragment($tonicsView)
                ];
                helper()->onSuccess($data);
            }
            return '';
        });

        $event->hookInto('tonics_folder_search', function (TonicsView $tonicsView){
            return $this->handleFolderSearchFragment($tonicsView);
        });

        $event->hookInto('tonics_folder_search_from_api', function (TonicsView $tonicsView){
            $isSearch = url()->getHeaderByKey('type') === 'isSearch';
            if ($isSearch){
                response()->onSuccess($this->handleFolderSearchFragment($tonicsView));
            }
            return '';
        });

        $event->hookInto('tonics_single_main', function (TonicsView $tonicsView){
            return $tonicsView->renderABlock('tonics_track_main');
        });

        $event->hookInto('tonics_track_from_api', function (TonicsView $tonicsView){
            $isGetMarker = url()->getHeaderByKey('type') === 'getMarker';
            $isAPI = url()->getHeaderByKey('isAPI') === 'true';
            $routeParams = url()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams();
            $uniqueSlugID = $routeParams[0] ?? null;
            if ($isGetMarker){
                $track = null;
                db(onGetDB: function ($db) use ($uniqueSlugID, &$track) {
                    $track = $db->Select('field_settings')->From(TrackData::getTrackTable())
                        ->WhereEquals('slug_id', $uniqueSlugID)->FetchFirst();
                });
                $fieldSettings = null;
                $markerData = [];
                if (isset($track->field_settings)){
                    $track->field_settings = json_decode($track->field_settings);
                    $fieldSettings = json_decode($track->field_settings?->_fieldDetails);
                }

                if (is_array($fieldSettings)){
                    $markerCurrent = [];
                    foreach ($fieldSettings as $fieldSetting){
                        $fieldInputName = $fieldSetting->field_input_name;
                        if ($fieldInputName === 'track_marker_slug_id'){
                            $markerCurrent[$fieldInputName] = json_decode($fieldSetting?->field_options)?->{$fieldInputName};
                        }
                        if ($fieldSetting->field_input_name === 'track_marker_start'){
                            $markerCurrent[$fieldInputName] = json_decode($fieldSetting?->field_options)?->{$fieldInputName};
                        }
                        if ($fieldSetting->field_input_name === 'track_marker_end'){
                            $markerCurrent[$fieldInputName] = json_decode($fieldSetting?->field_options)?->{$fieldInputName};
                        }
                        if ($fieldSetting->field_input_name === 'track_marker_name'){
                            $markerCurrent[$fieldInputName] = json_decode($fieldSetting?->field_options)?->{$fieldInputName};
                            $markerData[] = $markerCurrent; $markerCurrent = [];
                        }
                    }
                }

                if (!empty($markerData)){
                    $data = [
                        'isMarker' => true,
                        'markers' => $markerData
                    ];
                    response()->onSuccess($data);
                }
            }

            if ($isAPI){
                $data = [
                    'isTrack' => true,
                    'fragment' => $tonicsView->renderABlock('tonics_track_main'),
                    'title' => $tonicsView->accessArrayWithSeparator('Data._themeFolderData.seo_title'),
                ];
                helper()->onSuccess($data);
            }

            return '';
        });
    }

    /**
     * @param TonicsView $tonicsView
     * @return void
     * @throws \Exception
     */
    public function handleTrackCategoryForRootQuery(TonicsView $tonicsView): void
    {
        try {
            $db = db();
            $fieldSettings = $tonicsView->accessArrayWithSeparator('Data');
            $trackData = TrackData::class;
            $data = $db->Select('0 as is_track, track_cat_name as _name, slug_id, CONCAT_WS("/", "/track_categories", slug_id, track_cat_slug) as _link')
                ->From($trackData::getTrackCategoryTable())->WhereNull('track_cat_parent_id')
                ->WhereEquals('track_cat_status', 1)
                ->Where("{$trackData::getTrackCategoryTable()}.created_at", '<=', helper()->date())
                ->SimplePaginate(AppConfig::getAppPaginationMax());

            $fieldSettings['ThemeFolder'] = $data;
            $tonicsView->addToVariableData('Data', $fieldSettings);
        } catch (\Exception $exception){
            // Log..
        }
    }

    public function handleTrackCategoryFolderQuery(TonicsView $tonicsView)
    {
        $trackData = TrackData::class;
        $fieldSettings = $tonicsView->accessArrayWithSeparator('Data');
        try {
            $db = db();
            $db->when(!empty(url()->getParams()), function (TonicsQuery $db) use ($fieldSettings, $trackData) {
                $db->With('category_tree',
                    $db->Q()->Select('track_cat_id')->From($trackData::getTrackCategoryTable())
                        ->WhereEquals('track_cat_id', $fieldSettings['track_cat_id'])
                        ->UnionAll(
                            $db->Q()->Select(' c.track_cat_id')->From("{$trackData::getTrackCategoryTable()} c")
                                ->Join('category_tree ct', 'c.track_cat_parent_id', 'ct.track_cat_id')
                        ),
                    true);
            });

            $db->Select('*')->From(db()->Select("t.track_id as id, t.slug_id, t.track_title as _name, null as num_tracks, t.track_plays as plays,
        t.track_bpm as bpm, t.image_url, t.audio_url, tl.license_attr, t.field_settings, t.track_status as _status,
        ta.artist_name as artist_name, ta.artist_slug as artist_slug, g.genre_slug as genre_slug,
        t.created_at,
        1 as is_track, CONCAT_WS('/', '/tracks', t.slug_id, t.track_slug) as _link")
                ->From("{$trackData::getTrackTable()} t")
                ->Join("{$trackData::getTrackToGenreTable()} tg", "tg.fk_track_id", "t.track_id")
                ->Join("{$trackData::getGenreTable()} g", "g.genre_id", "tg.fk_genre_id")

                ->Join("{$trackData::getTrackTracksCategoryTable()} ttc", "t.track_id", "ttc.fk_track_id")
                ->Join("{$trackData::getTrackCategoryTable()} ct", "ttc.fk_track_cat_id", "ct.track_cat_id")
                // join cte if it is for filtering
                ->when(!empty(url()->getParams()), function (TonicsQuery $db) use ($fieldSettings) {
                    $db ->Join("category_tree ct2", "ct.track_cat_id", "ct2.track_cat_id");
                 })
                ->Join("{$trackData::getLicenseTable()} tl", "tl.license_id", "t.fk_license_id")
                ->Join("{$trackData::getArtistTable()} ta", "ta.artist_id", "t.fk_artist_id")
                // if it is not for filtering, use the current category
                ->when(empty(url()->getParams()), function (TonicsQuery $db) use ($fieldSettings) {
                    $db->WhereEquals('ct.track_cat_id', $fieldSettings['track_cat_id']);
                })
                ->Raw('UNION')
                ->Select("ct.track_cat_id as id, ct.slug_id, ct.track_cat_name as _name,
                -- the num_tracks calculate both the sub-folder and tracks in a folder
        (SELECT COUNT(*) + 
           (SELECT COUNT(*) FROM {$trackData::getTrackTracksCategoryTable()} ttc
            INNER JOIN {$trackData::getTrackTable()} t ON ttc.fk_track_id = t.track_id
            WHERE ttc.fk_track_cat_id = ct.track_cat_id AND t.track_status = 1) 
        FROM {$trackData::getTrackCategoryTable()} WHERE track_cat_parent_id = ct.track_cat_id) as num_tracks, 
        null as plays,
        null as bpm, null as image_url, null as audio_url, null as license_attr, ct.field_settings, ct.track_cat_status as _status,
        null as artist_name, null as artist_slug, null as genre_slug,
        ct.created_at as created_at,
        0 as is_track, CONCAT_WS('/', '/track_categories', ct.slug_id, ct.track_cat_slug) as _link")
                ->From("{$trackData::getTrackCategoryTable()} ct")
                // join cte if it is for filtering, else, we use only the current category
                ->when(!empty(url()->getParams()), function (TonicsQuery $db) use ($fieldSettings) {
                    $db->Join("category_tree ct2", "ct.track_cat_id", "ct2.track_cat_id")
                        // we do not need to include the main category in the result set
                        ->WhereNotEquals('ct.track_cat_id', $fieldSettings['track_cat_id']);
                }, function (TonicsQuery $db) use ($fieldSettings) {
                    $db->WhereEquals('ct.track_cat_parent_id', $fieldSettings['track_cat_id']);
                })
            ) // End Sub query
                ->As('track_results')
                ->when(is_array(url()->getParam('track_bpm')), function (TonicsQuery $db){
                    $db->WhereIn('track_results.bpm', url()->getParam('track_bpm'));
                })->when(is_array(url()->getParam('track_genres')), function (TonicsQuery $db){
                    $db->WhereIn('track_results.genre_slug', url()->getParam('track_genres'));
                })->when(url()->hasParamAndValue('track_artist'), function (TonicsQuery $db){
                    $db->WhereEquals('track_results.artist_slug', url()->getParam('track_artist'));
                })->when(url()->hasParamAndValue('track_key'), function (TonicsQuery $db) {
                    $trackKey = '"' . url()->getParam('track_key') . '"';
                    $db->WhereJsonContains('track_results.field_settings', 'track_default_filter_keys', $trackKey);
                })->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                    $db->WhereLike('_name', url()->getParam('query'));
                });

            $data =  $this->dbWhenForCommonFieldKey($db)
                ->WhereEquals('_status', 1)
                ->Where('created_at', '<=', helper()->date())
                ->GroupBy("slug_id")
                ->OrderByAsc("is_track")
                ->OrderByDesc("created_at")
                ->SimplePaginate(AppConfig::getAppPaginationMax());

            $fieldSettings['ThemeFolder'] = $data;
            $tonicsView->addToVariableData('Data', $fieldSettings);
        } catch (\Exception $exception){
            // Log...
        }
    }

    /**
     * @throws \Exception
     */
    public static function handleTrackSingleFragment(): mixed
    {
        /** @var TrackData $trackData */
        $trackData = container()->get(TrackData::class);
        $routeParams = url()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams();
        $uniqueSlugID = $routeParams[0] ?? null;

        try {
            $track = db()->Select("t.track_id as id, t.slug_id, t.track_title as _name, null as num_tracks, t.track_plays as plays,
        t.track_bpm as bpm, t.image_url, t.audio_url, tl.license_attr, t.field_settings,
        ta.artist_name as artist_name, ta.artist_slug as artist_slug, g.genre_slug as genre_slug,
        t.created_at,
        1 as is_track, CONCAT_WS('/', '/tracks', t.slug_id, t.track_slug) as _link")->From("{$trackData::getTrackTable()} t")
                ->Join("{$trackData::getTrackToGenreTable()} tg", "tg.fk_track_id", "t.track_id")
                ->Join("{$trackData::getGenreTable()} g", "g.genre_id", "tg.fk_genre_id")
                ->Join("{$trackData::getTrackTracksCategoryTable()} ttc", "t.track_id", "ttc.fk_track_id")
                ->Join("{$trackData::getTrackCategoryTable()} ct", "ttc.fk_track_cat_id", "ct.track_cat_id")
                ->Join("{$trackData::getLicenseTable()} tl", "tl.license_id", "t.fk_license_id")
                ->Join("{$trackData::getArtistTable()} ta", "ta.artist_id", "t.fk_artist_id")
                ->Where('t.created_at', '<=', helper()->date())
                ->WhereEquals('t.track_status', 1)
                ->WhereEquals("t.slug_id", $uniqueSlugID)
                ->GroupBy("t.track_id")->setPdoFetchType(PDO::FETCH_ASSOC)->FetchFirst();

            if (!is_array($track)){
                return [];
            }

            $trackData->unwrapForTrack($track);
            return $track;
        } catch (\Exception $exception){
            // Log..
        }
        return [];
    }

    /**
     * @param TonicsView $tonicsView
     * @return string
     * @throws \Exception
     */
    public function handleFolderFragment(TonicsView $tonicsView): string
    {
        $root = $tonicsView->accessArrayWithSeparator('Data.ThemeFolderHome');
        if ($root){
            $this->handleTrackCategoryForRootQuery($tonicsView);
        } else {
            $this->handleTrackCategoryFolderQuery($tonicsView);
        }
        return $tonicsView->renderABlock('tonics_folder_main');
    }

    /**
     * @param TonicsView $tonicsView
     * @return string
     * @throws \Exception
     */
    public function handleFolderSearchFragment(TonicsView $tonicsView): string
    {
        $fieldSettings = $tonicsView->accessArrayWithSeparator('Data');
        if (isset($fieldSettings['track_cat_content']) && $fieldSettings['track_cat_content'] === '<p><br></p>'){
            $fieldSettings['track_cat_content'] = '';
            $tonicsView->addToVariableData('Data', $fieldSettings);
        }
        $root = $tonicsView->accessArrayWithSeparator('Data.ThemeFolderHome');
        if ($root){
            return '';
        } else {
            # Get Filters of a Certain Category and Its Sub Category
            $this->handleFilterFromFieldSettingsKeyForCategorySubCategory($fieldSettings, $fieldSettings);
            $this->handleFilterTrackArtistKeyForCategorySubCategory($fieldSettings, $fieldSettings);
            $this->handleFilterTrackGenreKeyForCategorySubCategory($fieldSettings, $fieldSettings);
            $tonicsView->addToVariableData('Data', $fieldSettings);
        }

        return $tonicsView->renderABlock('tonics_folder_content') . $tonicsView->renderABlock('tonics_folder_search');
    }

    /**
     * @param TonicsQuery $db
     * @return TonicsQuery
     * @throws \Exception
     */
    public function dbWhenForCommonFieldKey(TonicsQuery $db): TonicsQuery
    {
        $keys = [
            'track_default_filter_mood',
            'track_default_filter_instruments',
            'track_default_filter_samplePacks_Type',
            'track_default_filter_acapella_gender',
            'track_default_filter_acapella_vocalStyle',
            'track_default_filter_acapella_emotion',
            'track_default_filter_acapella_scale',
            'track_default_filter_acapella_effects'
        ];
        foreach ($keys as $key){
            $db->when(is_array(url()->getParam($key)), function (TonicsQuery $db) use ($key) {
                $keyValues = url()->getParam($key);
                foreach ($keyValues as $value){
                    $value = '"' . $value . '"';
                    $db->WhereJsonContains('track_results.field_settings', $key, $value, ifWhereUse: 'OR');
                }
            });
        }

        return $db;
    }

    /**
     * @param $mainTrackData
     * @param $fieldSettings
     * @return void
     * @throws \Exception
     */
    public function handleFilterFromFieldSettingsKeyForCategorySubCategory($mainTrackData, &$fieldSettings): void
    {
        $trackCatID = $mainTrackData['track_cat_id'];
        $trackData = TrackData::class;

        $filters = [
            'track_bpm',
            'track_default_filter_keys',
            'track_default_filter_mood',
            'track_default_filter_instruments',
            'track_default_filter_samplePacks_Type',
            'track_default_filter_acapella_gender',
            'track_default_filter_acapella_vocalStyle',
            'track_default_filter_acapella_emotion',
            'track_default_filter_acapella_scale',
            'track_default_filter_acapella_effects',
        ];
        $filterSQLFRAG = '';
        $last = array_key_last($filters);
        foreach ($filters as $filterKey => $filter){
            $filterSQLFRAG .= <<<SQL
  '$filter',
  JSON_ARRAYAGG(DISTINCT CASE WHEN JSON_EXTRACT(t.field_settings, '$.$filter') <> '' THEN JSON_EXTRACT(t.field_settings, '$.$filter') END)
SQL;
            if ($filterKey !== $last){
                $filterSQLFRAG .= ',' . "\n";
            }
        }

        $filterOptions = db()->row(<<<FILTER_OPTION
WITH RECURSIVE category_tree AS (
  SELECT ct.track_cat_id, ct.track_cat_parent_id, ct.field_settings
  FROM {$trackData::getTrackCategoryTable()} ct
  WHERE ct.track_cat_id = ?
  UNION
  SELECT ct.track_cat_id, ct.track_cat_parent_id, ct.field_settings
  FROM {$trackData::getTrackCategoryTable()} ct
  JOIN category_tree ON ct.track_cat_parent_id = category_tree.track_cat_id
)
SELECT JSON_OBJECT(
  $filterSQLFRAG
) AS filters
FROM {$trackData::getTrackTable()} t
JOIN {$trackData::getTrackTracksCategoryTable()} ttc ON t.track_id = ttc.fk_track_id
JOIN category_tree ct ON ttc.fk_track_cat_id = ct.track_cat_id
LIMIT 1;
FILTER_OPTION, $trackCatID);


        if (isset($filterOptions->filters) && helper()->isJSON($filterOptions->filters)){
            $fieldSettings['ThemeFolder_FilterOption_More'] = [];
            $filterOptions = json_decode($filterOptions->filters);

            # Check if Some Key Values are Multidimensional, if so, flatten it
            foreach ($filterOptions as $filterKey => $filterValue){
                if (is_array($filterValue) && helper()->array_depth($filterValue) > 1) {
                    $filterValue = iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($filterValue)), false);
                    $filterValue = array_unique($filterValue);
                    $filterOptions->{$filterKey} = $filterValue;
                }
            }

            $trackKeysFrag ='';
            if (is_array($filterOptions->track_default_filter_keys)){
                $trackKeysFrag = <<<TRACK_KEY
<label for="track_key">Choose Key
                        <select class="default-selector border-width:default border:white color:black" name="track_key" id="track_key">
                        <option value=''>Any Key</option>
TRACK_KEY;
                foreach ($filterOptions->track_default_filter_keys as $filter_key){
                    $select = (url()->getParam('track_key') === $filter_key) ? 'selected' : '';
                    $trackKeysFrag .= " <option $select value='$filter_key'>$filter_key</option>";
                }
                $trackKeysFrag .= '</select></label>';
            }


            $fieldSettings['ThemeFolder_FilterOption_TrackKey'] = $trackKeysFrag;
            $fieldSettings['ThemeFolderTrackDefaultImage'] = "https://via.placeholder.com/200/FFFFFF/000000?text=Featured+Image+Is+Empty";
            $fieldSettings['ThemeFolder_FilterOption_TrackBPM'] = $this->createCheckboxFilterFragmentFromFieldSettings("track_bpm", $filterOptions);

            if (isset($mainTrackData['filter_type'])){
                $filterType = $mainTrackData['filter_type'];
                $fieldSettings['ThemeFolder_FilterOption_More']['track_default_filter_mood'] = [
                    'label' => 'Choose Mood',
                    'frag' => $this->createCheckboxFilterFragmentFromFieldSettings("track_default_filter_mood", $filterOptions),
                ];

                $fieldSettings['ThemeFolder_FilterOption_More']['track_default_filter_instruments'] = [
                    'label' => 'Choose Instrument',
                    'frag' => $this->createCheckboxFilterFragmentFromFieldSettings("track_default_filter_instruments", $filterOptions),
                ];

                if ($filterType === 'track-default-filter-sample-packs'){
                    $fieldSettings['ThemeFolder_FilterOption_More']['track_default_filter_samplePacks_Type'] = [
                        'label' => 'Choose Sample Type',
                        'frag' => $this->createCheckboxFilterFragmentFromFieldSettings("track_default_filter_samplePacks_Type", $filterOptions),
                    ];
                }

                if ($filterType === 'track-default-filter-acapella'){
                    $fieldSettings['ThemeFolder_FilterOption_More']['track_default_filter_acapella_gender'] = [
                        'label' => 'Choose Gender',
                        'frag' => $this->createCheckboxFilterFragmentFromFieldSettings("track_default_filter_acapella_gender", $filterOptions),
                    ];

                    $fieldSettings['ThemeFolder_FilterOption_More']['track_default_filter_acapella_vocalStyle'] = [
                        'label' => 'Choose Vocal Style',
                        'frag' => $this->createCheckboxFilterFragmentFromFieldSettings("track_default_filter_acapella_vocalStyle", $filterOptions),
                    ];

                    $fieldSettings['ThemeFolder_FilterOption_More']['track_default_filter_acapella_emotion'] = [
                        'label' => 'Choose Emotion',
                        'frag' => $this->createCheckboxFilterFragmentFromFieldSettings("track_default_filter_acapella_emotion", $filterOptions),
                    ];

                    $fieldSettings['ThemeFolder_FilterOption_More']['track_default_filter_acapella_scale'] = [
                        'label' => 'Choose Scale',
                        'frag' => $this->createCheckboxFilterFragmentFromFieldSettings("track_default_filter_acapella_scale", $filterOptions),
                    ];

                    $fieldSettings['ThemeFolder_FilterOption_More']['track_default_filter_acapella_effects'] = [
                        'label' => 'Choose Effects',
                        'frag' => $this->createCheckboxFilterFragmentFromFieldSettings("track_default_filter_acapella_effects", $filterOptions),
                    ];
                }
            }
        }
    }

    /**
     * @param $mainTrackData
     * @param $fieldSettings
     * @return void
     * @throws \Exception
     */
    public function handleFilterTrackArtistKeyForCategorySubCategory($mainTrackData, &$fieldSettings): void
    {
        $trackCatID = $mainTrackData['track_cat_id'];
        $trackData = TrackData::class;
        $artists = db()->run(<<<SQL
-- This would get the artist that has track in them within the category and its sub-categories using RECURSIVE CTE
WITH RECURSIVE category_tree AS (
SELECT track_cat_id, track_cat_parent_id, slug_id, track_cat_name, track_cat_status, field_settings, 0 as level
FROM {$trackData::getTrackCategoryTable()}
WHERE track_cat_id = ?
UNION ALL
SELECT c.track_cat_id, c.track_cat_parent_id, c.slug_id, c.track_cat_name, c.track_cat_status, c.field_settings, level + 1
FROM {$trackData::getTrackCategoryTable()} c
INNER JOIN category_tree ct ON c.track_cat_parent_id = ct.track_cat_id
)
SELECT a.artist_id, a.artist_name, a.artist_slug, COUNT(t.track_id) as num_tracks
FROM tonics_artists a
INNER JOIN {$trackData::getTrackTable()} t ON a.artist_id = t.fk_artist_id
INNER JOIN {$trackData::getTrackTracksCategoryTable()} ttc ON t.track_id = ttc.fk_track_id
INNER JOIN category_tree ct ON ttc.fk_track_cat_id = ct.track_cat_id
GROUP BY a.artist_id, a.artist_name
HAVING COUNT(t.track_id) > 0
ORDER BY a.artist_name;
SQL, $trackCatID);

        $trackArtistsFrag ='';
        if (is_array($artists) && !empty($artists)){
            $trackArtistsFrag = <<<TRACK_KEY
<label for="track_key">Choose Artist
                        <select class="default-selector border-width:default border:white color:black" name="track_artist" id="track_artist">
                        <option value=''>Any Artist</option>
TRACK_KEY;
            foreach ($artists as $artist){
                $select = (url()->getParam('track_artist') === $artist->artist_slug) ? 'selected' : '';
                $trackArtistsFrag .= " <option $select value='$artist->artist_slug'>$artist->artist_name</option>";
            }
            $trackArtistsFrag .= '</select></label>';

            $fieldSettings['ThemeFolder_FilterOption_TrackArtists'] = $trackArtistsFrag;
        }
    }

    /**
     * @param $mainTrackData
     * @param $fieldSettings
     * @return void
     * @throws \Exception
     */
    public function handleFilterTrackGenreKeyForCategorySubCategory($mainTrackData, &$fieldSettings): void
    {
        $trackCatID = $mainTrackData['track_cat_id'];
        $trackData = TrackData::class;
        $genres = db()->run(<<<SQL
-- This would get the genre that has track in them within the category and its sub-categories using RECURSIVE CTE
WITH RECURSIVE category_tree AS (
SELECT track_cat_id, track_cat_parent_id, slug_id, track_cat_name, track_cat_status, field_settings, 0 as level
FROM {$trackData::getTrackCategoryTable()}
WHERE track_cat_id = ?
UNION ALL
SELECT c.track_cat_id, c.track_cat_parent_id, c.slug_id, c.track_cat_name, c.track_cat_status, c.field_settings, level + 1
FROM {$trackData::getTrackCategoryTable()} c
INNER JOIN category_tree ct ON c.track_cat_parent_id = ct.track_cat_id
)
SELECT g.genre_id, g.genre_name, g.genre_slug, COUNT(t.track_id) as num_tracks
FROM tonics_genres g
INNER JOIN {$trackData::getTrackToGenreTable()} tg ON g.genre_id = tg.fk_genre_id
INNER JOIN {$trackData::getTrackTable()} t ON tg.fk_track_id = t.track_id
INNER JOIN {$trackData::getTrackTracksCategoryTable()} ttc ON t.track_id = ttc.fk_track_id
INNER JOIN category_tree ct ON ttc.fk_track_cat_id = ct.track_cat_id
GROUP BY g.genre_id, g.genre_name
HAVING COUNT(t.track_id) > 0
ORDER BY num_tracks DESC;
SQL, $trackCatID);

        $trackGenresFrag ='';
        if (is_array($genres) && !empty($genres)){
            $trackGenresFrag = <<<TRACK_KEY
<ul class="menu-box-radiobox-items list:style:none">
TRACK_KEY;
            foreach ($genres as $genre){
                $checked = '';
                if (is_array(url()->getParam('track_genres'))){
                    $genreParam = array_combine(url()->getParam('track_genres'), url()->getParam('track_genres'));
                    if (key_exists($genre->genre_slug, $genreParam)){
                        $checked = 'checked';
                    }
                }
                $trackGenresFrag .= <<<LI
<li class="menu-item">
    <input type="checkbox" $checked id="track_genre_$genre->genre_slug" name="track_genres[]" value="$genre->genre_slug">
     <label for="track_genre_$genre->genre_slug">$genre->genre_name ($genre->num_tracks)</label>
 </li>
LI;
            }
            $trackGenresFrag .= '</ul>';
        }

        $fieldSettings['ThemeFolder_FilterOption_TrackGenres'] = $trackGenresFrag;
    }

    /**
     * @param string $param
     * @param $filterOptions
     * @return string
     * @throws \Exception
     */
    public function createCheckboxFilterFragmentFromFieldSettings(string $param, $filterOptions): string
    {
        $frag = '';
        if (is_array($filterOptions->{$param})){
            $frag = <<<TRACK_KEY
<ul class="menu-box-radiobox-items list:style:none">
TRACK_KEY;
            foreach ($filterOptions->{$param} as $filterValue){
                $checked = '';
                if (is_array(url()->getParam($param))){
                    $bpmParam = array_combine(url()->getParam($param), url()->getParam($param));
                    if (key_exists($filterValue, $bpmParam)){
                        $checked = 'checked';
                    }
                }
                $frag .= <<<LI
<li class="menu-item">
    <input type="checkbox" $checked id="{$param}_$filterValue" name="{$param}[]" value="$filterValue">
     <label for="{$param}_$filterValue">$filterValue</label>
 </li>
LI;
            }
            $frag .= '</ul>';
        }
        return $frag;
    }
}