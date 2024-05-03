<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\NinetySeven\EventHandler\PageTemplates\AudioTonics\ThemeFolder;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use App\Modules\Track\Data\TrackData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsTemplateSystem\TonicsView;
use PDO;

class ThemeFolderViewHandler implements HandlerInterface
{

    const TonicsAudioTonicsKey = 'TonicsBeatsTonics_Theme';

    public function handleEvent(object $event): void
    {
        /** @var $event OnHookIntoTemplate */
        $event->hookInto('tonics_folder_main', function (TonicsView $tonicsView) {
            return $this->handleFolderFragment($tonicsView);
        });

        $event->hookInto('tonics_folder_main_from_api', function (TonicsView $tonicsView) {
            $isFolder = url()->getHeaderByKey('type') === 'isTonicsNavigation';
            if ($isFolder) {
                $data = [
                    'isFolder' => true,
                    'title' => $tonicsView->accessArrayWithSeparator('Data.seo_title'),
                    'fragment' => $this->handleFolderFragment($tonicsView)
                ];
                helper()->onSuccess($data);
            }
            return '';
        });

        $event->hookInto('tonics_folder_search', function (TonicsView $tonicsView) {
            return $this->handleFolderSearchFragment($tonicsView);
        });

        $event->hookInto('tonics_folder_search_from_api', function (TonicsView $tonicsView) {
            $isSearch = url()->getHeaderByKey('type') === 'isSearch';
            if ($isSearch) {
                response()->onSuccess($this->handleFolderSearchFragment($tonicsView));
            }
            return '';
        });

        $event->hookInto('tonics_single_main', function (TonicsView $tonicsView) {
            return $tonicsView->renderABlock('tonics_track_main');
        });

        $event->hookInto('tonics_track_from_api', function (TonicsView $tonicsView) {
            $isGetMarker = url()->getHeaderByKey('type') === 'getMarker';
            $isAPI = url()->getHeaderByKey('isAPI') === 'true';
            $routeParams = url()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams();
            $uniqueSlugID = $routeParams[0] ?? null;
            if ($isGetMarker) {
                $track = null;
                db(onGetDB: function ($db) use ($uniqueSlugID, &$track) {
                    $track = $db->Select('field_settings')->From(TrackData::getTrackTable())
                        ->WhereEquals('slug_id', $uniqueSlugID)->FetchFirst();
                });
                $fieldSettings = null;
                $markerData = [];
                if (isset($track->field_settings)) {
                    $track->field_settings = json_decode($track->field_settings);
                    $fieldSettings = json_decode($track->field_settings?->_fieldDetails);
                }

                if (is_array($fieldSettings)) {
                    $markerCurrent = [];
                    foreach ($fieldSettings as $fieldSetting) {
                        $fieldInputName = $fieldSetting->field_input_name;
                        if ($fieldInputName === 'track_marker_slug_id') {
                            $markerCurrent[$fieldInputName] = json_decode($fieldSetting?->field_options)?->{$fieldInputName};
                        }
                        if ($fieldSetting->field_input_name === 'track_marker_start') {
                            $markerCurrent[$fieldInputName] = json_decode($fieldSetting?->field_options)?->{$fieldInputName};
                        }
                        if ($fieldSetting->field_input_name === 'track_marker_end') {
                            $markerCurrent[$fieldInputName] = json_decode($fieldSetting?->field_options)?->{$fieldInputName};
                        }
                        if ($fieldSetting->field_input_name === 'track_marker_name') {
                            $markerCurrent[$fieldInputName] = json_decode($fieldSetting?->field_options)?->{$fieldInputName};
                            $markerData[] = $markerCurrent;
                            $markerCurrent = [];
                        }
                    }
                }

                if (!empty($markerData)) {
                    $data = [
                        'isMarker' => true,
                        'markers' => $markerData
                    ];
                    response()->onSuccess($data);
                }
            }

            if ($isAPI) {
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
            $fieldSettings = $tonicsView->accessArrayWithSeparator('Data');
            $trackData = TrackData::class;
            $data = null;
            db(onGetDB: function ($db) use ($trackData, &$data){
                $data = $db->Select('0 as is_track, track_cat_name as _name, slug_id, CONCAT_WS("/", "/track_categories", slug_id, track_cat_slug) as _link')
                    ->From($trackData::getTrackCategoryTable())->WhereNull('track_cat_parent_id')
                    ->WhereEquals('track_cat_status', 1)
                    ->Where("{$trackData::getTrackCategoryTable()}.created_at", '<=', helper()->date())
                    ->SimplePaginate(AppConfig::getAppPaginationMax());
            });

            $fieldSettings['ThemeFolder'] = $data;
            $tonicsView->addToVariableData('Data', $fieldSettings);
        } catch (\Exception $exception) {
            // Log..
        }
    }

    /**
     * @param TonicsView $tonicsView
     * @return void
     */
    public function handleTrackCategoryFolderQuery(TonicsView $tonicsView): void
    {
        $trackData = TrackData::class;
        $fieldSettings = $tonicsView->accessArrayWithSeparator('Data');
        try {
            $data = null;
            db(onGetDB: function (TonicsQuery $db) use ($trackData, $fieldSettings, &$data) {

                $db->when($this->isFiltering(), function (TonicsQuery $db) use ($fieldSettings, $trackData) {
                    $db->With('category_tree',
                        db()->Select('track_cat_id')->From($trackData::getTrackCategoryTable())
                            ->WhereEquals('track_cat_id', $fieldSettings['track_cat_id'])
                            ->UnionAll(
                                db()->Select(' c.track_cat_id')->From("{$trackData::getTrackCategoryTable()} c")
                                    ->Join('category_tree ct', 'c.track_cat_parent_id', 'ct.track_cat_id')
                            ),
                        true);
                });

                $db->Select('*')->From(db()->Select("t.track_id as id, t.slug_id, t.track_title as _name, t.track_plays as plays,
        t.track_bpm as bpm, t.image_url, t.audio_url, tl.license_attr, t.track_status as _status,
        t.created_at,
        1 as is_track, CONCAT_WS('/', '/tracks', t.slug_id, t.track_slug) as _link")
                    ->From("{$trackData::getTrackTable()} t")
                    ->Join("{$trackData::getTrackTracksCategoryTable()} ttc", "t.track_id", "ttc.fk_track_id")
                    ->Join("{$trackData::getTrackCategoryTable()} ct", "ttc.fk_track_cat_id", "ct.track_cat_id")
                    // join cte if it is for filtering
                    ->when($this->isFiltering(), function (TonicsQuery $db) use ($fieldSettings) {
                        $db->Join("category_tree ct2", "ct.track_cat_id", "ct2.track_cat_id");
                    })
                    ->Join("{$trackData::getLicenseTable()} tl", "tl.license_id", "t.fk_license_id")
                    ->when($this->isFiltering(), function (TonicsQuery $db) use ($trackData) {
                        // -- join with the filter table
                        $db->LeftJoin("{$trackData::getTrackDefaultFiltersTrackTable()} tdft", "t.track_id", "tdft.fk_track_id")
                            // -- join with the filter values
                            ->LeftJoin("{$trackData::getTrackDefaultFiltersTable()} tdf", "tdft.fk_tdf_id", "tdf.tdf_id");
                        $this->dbWhenForCommonFieldKey($db);
                    })
                    // if it is not for filtering, use the current category
                    // also, we won't be returning track_categories result set
                    // though, if there are tracks in the inner track_categories that satisfies the filter, it would get the track appropriately
                    // just not the track_category data
                    ->when($this->isFiltering() === false, function (TonicsQuery $db) use ($trackData, $fieldSettings) {
                        $db->WhereEquals('ct.track_cat_id', $fieldSettings['track_cat_id'])
                            ->Raw('UNION')
                            ->Select("ct.track_cat_id as id, ct.slug_id, ct.track_cat_name as _name, null as plays, null as bpm, 
                        null as image_url, null as audio_url, null as license_attr, ct.track_cat_status as _status,
                        ct.created_at as created_at,0 as is_track, CONCAT_WS('/', '/track_categories', ct.slug_id, ct.track_cat_slug) as _link")
                            ->From("{$trackData::getTrackCategoryTable()} ct")
                            // join cte if it is for filtering, else, we use only the current category
                            ->when($this->isFiltering(), function (TonicsQuery $db) use ($fieldSettings) {
                                $db->Join("category_tree ct2", "ct.track_cat_id", "ct2.track_cat_id")
                                    // we do not need to include the main category in the result set
                                    ->WhereNotEquals('ct.track_cat_id', $fieldSettings['track_cat_id']);
                            }, function (TonicsQuery $db) use ($fieldSettings) {
                                $db->WhereEquals('ct.track_cat_parent_id', $fieldSettings['track_cat_id']);
                            });
                    })
                ) // End Sub query
                ->As('track_results')
                    ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                        $db->WhereLike('_name', url()->getParam('query'));
                    });

                $data = $db
                    ->WhereEquals('_status', 1)
                    ->Where('created_at', '<=', helper()->date())
                    ->GroupBy("slug_id")
                    ->OrderByAsc("is_track")
                    ->OrderByDesc("created_at")
                    ->SimplePaginate(AppConfig::getAppPaginationMax());
            });

            $fieldSettings['ThemeFolder'] = $data;
            $tonicsView->addToVariableData('Data', $fieldSettings);
        } catch (\Exception $exception) {
            // Log...
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isFiltering(): bool
    {
        if (!empty(url()->getParams())){
            return true;
        }

        if (url()->hasParamAndValue('query')){
            return true;
        }

        return false;
    }

    /**
     * @throws \Exception
     */
    public static function handleTrackSingleFragment(): mixed
    {
        /** @var TrackData $trackData */
        $trackData = container()->get(TrackData::class);

        try {
            $track = null;
            db(onGetDB: function (TonicsQuery $db) use ($trackData, &$track){
                $routeParams = url()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams();
                $uniqueSlugID = $routeParams[0] ?? null;

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
                    ->WhereEquals("t.slug_id", $uniqueSlugID)
                    ->GroupBy("t.track_id")->setPdoFetchType(PDO::FETCH_ASSOC)->FetchFirst();
            });

            if (!is_array($track)) {
                return [];
            } else {
                if (isset($track['fk_cat_id'])){
                    $categories = explode(',', $track['fk_cat_id']);
                    $categories = array_combine($categories, $categories);
                    $categories = array_values($categories);
                    foreach ($categories as $category){
                        $reverseCategory = array_reverse(self::getTrackCategoryParents($category));
                        $track['categories'][] = $reverseCategory;
                    }
                }
            }

            $trackData->unwrapForTrack($track);
            return $track;
        } catch (\Exception $exception) {
            // Log..
        }
        return [];
    }

    /**
     * @param string|int $idSlug
     * @return mixed|null
     * @throws \Exception
     */
    public static function getTrackCategoryParents(string|int $idSlug): mixed
    {
        $result = null;
        db(onGetDB: function ($db) use ($idSlug, &$result){
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
     * @param TonicsView $tonicsView
     * @return string
     * @throws \Exception
     */
    public function handleFolderFragment(TonicsView $tonicsView): string
    {
        $root = $tonicsView->accessArrayWithSeparator('Data.ThemeFolderHome');
        if ($root) {
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
        if (isset($fieldSettings['track_cat_content']) && $fieldSettings['track_cat_content'] === '<p><br></p>') {
            $fieldSettings['track_cat_content'] = '';
            $tonicsView->addToVariableData('Data', $fieldSettings);
        }
        $root = $tonicsView->accessArrayWithSeparator('Data.ThemeFolderHome');
        if ($root) {
            return '';
        } else {
            # Get Filters of a Certain Category and Its Sub Category
            $this->handleFilterFromFieldSettingsKeyForCategorySubCategory($fieldSettings, $fieldSettings);
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
        foreach ($keys as $param => $filterType) {
            $db->when(url()->hasParam($param), function (TonicsQuery $db) use ($filterType, $param) {
                $keyValues = url()->getParam($param);
                $db->OrWhereEquals('tdf.tdf_type', $filterType)->WhereIn('tdf.tdf_name', $keyValues);
            });
        }
        return $db;
    }

    /**
     * @param $mainTrackData
     * @param $fieldSettings
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function handleFilterFromFieldSettingsKeyForCategorySubCategory($mainTrackData, &$fieldSettings): void
    {
        $trackCatID = $mainTrackData['track_cat_id'];
        $trackData = TrackData::class;

        $filters = [
            'bpm',
            'key',
            'genre',
            'artist',
            'mood',
            'instrument',
            'samplePackType',
            'acapellaGender',
            'acapellaVocalStyle',
            'acapellaEmotion',
            'acapellaScale',
            'acapellaEffects',
        ];
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

        $filterOptions = null;
        db(onGetDB: function (TonicsQuery $db) use ($filterType, $trackCatID, $trackData, &$filterOptions){
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
        });

        $newFilterOptions = [];
        foreach ($filterOptions as $filterOption) {
            $decode = json_decode($filterOption->filter_values, flags: JSON_INVALID_UTF8_IGNORE);
            if ($decode !== null){
                $values = array_unique($decode);
                $newFilterOptions[$filterOption->tdf_type] = $values;
            }
        }

        if (!empty($newFilterOptions)) {
            $filterOptions = $newFilterOptions;
            # FOR AUDIO KEY
            $trackKeysFrag = '';
            if (is_array($filterOptions['key'])) {
                $trackKeysFrag = <<<TRACK_KEY
<label for="track_key">Choose Key
                        <select class="default-selector border-width:default border:white color:black" name="track_key" id="track_key">
                        <option value=''>Any Key</option>
TRACK_KEY;
                foreach ($filterOptions['key'] as $filter_key) {
                    $select = (url()->getParam('track_key') === $filter_key) ? 'selected' : '';
                    $trackKeysFrag .= " <option $select value='$filter_key'>$filter_key</option>";
                }
                $trackKeysFrag .= '</select></label>';
            }

            # FOR AUDIO ARTIST
            $artists = $filterOptions['artist'] ?? [];
            if (is_array($artists) && !empty($artists)) {
                $trackArtistsFrag = <<<TRACK_KEY
<label for="track_key">Choose Artist
                        <select class="default-selector border-width:default border:white color:black" name="track_artist" id="track_artist">
                        <option value=''>Any Artist</option>
TRACK_KEY;
                foreach ($artists as $artist) {
                    $select = (url()->getParam('track_artist') === $artist) ? 'selected' : '';
                    $artistName = ucwords(str_replace("-", " ", $artist));
                    $trackArtistsFrag .= " <option $select value='$artist'>$artistName</option>";
                }
                $trackArtistsFrag .= '</select></label>';

                $fieldSettings['ThemeFolder_FilterOption_TrackArtists'] = $trackArtistsFrag;
            }

            # FOR AUDIO GENRES
            $trackGenresFrag = '';
            $genres = $filterOptions['genre'] ?? [];
            if (is_array($genres) && !empty($genres)) {
                $trackGenresFrag = <<<TRACK_KEY
<ul class="menu-box-radiobox-items list:style:none">
TRACK_KEY;
                foreach ($genres as $genre) {
                    $checked = '';
                    if (is_array(url()->getParam('track_genres'))) {
                        $genreParam = array_combine(url()->getParam('track_genres'), url()->getParam('track_genres'));
                        if (key_exists($genre, $genreParam)) {
                            $checked = 'checked';
                        }
                    }
                    $genreName = ucwords(str_replace("-", " ", $genre));
                    $trackGenresFrag .= <<<LI
<li class="menu-item">
    <input type="checkbox" $checked id="track_genre_$genre" name="track_genres[]" value="$genre">
     <label for="track_genre_$genre">$genreName</label>
 </li>
LI;
                }
                $trackGenresFrag .= '</ul>';
            }

            $fieldSettings['ThemeFolder_FilterOption_TrackGenres'] = $trackGenresFrag;

            $fieldSettings['ThemeFolder_FilterOption_TrackKey'] = $trackKeysFrag;
            $fieldSettings['ThemeFolderTrackDefaultImage'] = "https://via.placeholder.com/200/FFFFFF/000000?text=Featured+Image+Is+Empty";
            $fieldSettings['ThemeFolder_FilterOption_TrackBPM'] = $this->createCheckboxFilterFragmentFromFieldSettings("bpm", $filterOptions);

            if (isset($mainTrackData['filter_type'])) {
                $filterType = $mainTrackData['filter_type'];
                $fieldSettings['ThemeFolder_FilterOption_More']['track_default_filter_mood'] = [
                    'label' => 'Choose Mood',
                    'frag' => $this->createCheckboxFilterFragmentFromFieldSettings("mood", $filterOptions),
                ];
                $fieldSettings['ThemeFolder_FilterOption_More']['track_default_filter_instruments'] = [
                    'label' => 'Choose Instrument',
                    'frag' => $this->createCheckboxFilterFragmentFromFieldSettings("instrument", $filterOptions),
                ];
                if ($filterType === 'track-default-filter-sample-packs') {
                    $fieldSettings['ThemeFolder_FilterOption_More']['track_default_filter_samplePacks_Type'] = [
                        'label' => 'Choose Sample Type',
                        'frag' => $this->createCheckboxFilterFragmentFromFieldSettings("samplePackType", $filterOptions),
                    ];
                }
                if ($filterType === 'track-default-filter-acapella') {
                    $fieldSettings['ThemeFolder_FilterOption_More']['track_default_filter_acapella_gender'] = [
                        'label' => 'Choose Gender',
                        'frag' => $this->createCheckboxFilterFragmentFromFieldSettings("acapellaGender", $filterOptions),
                    ];

                    $fieldSettings['ThemeFolder_FilterOption_More']['track_default_filter_acapella_vocalStyle'] = [
                        'label' => 'Choose Vocal Style',
                        'frag' => $this->createCheckboxFilterFragmentFromFieldSettings("acapellaVocalStyle", $filterOptions),
                    ];

                    $fieldSettings['ThemeFolder_FilterOption_More']['track_default_filter_acapella_emotion'] = [
                        'label' => 'Choose Emotion',
                        'frag' => $this->createCheckboxFilterFragmentFromFieldSettings("acapellaEmotion", $filterOptions),
                    ];

                    $fieldSettings['ThemeFolder_FilterOption_More']['track_default_filter_acapella_scale'] = [
                        'label' => 'Choose Scale',
                        'frag' => $this->createCheckboxFilterFragmentFromFieldSettings("acapellaScale", $filterOptions),
                    ];

                    $fieldSettings['ThemeFolder_FilterOption_More']['track_default_filter_acapella_effects'] = [
                        'label' => 'Choose Effects',
                        'frag' => $this->createCheckboxFilterFragmentFromFieldSettings("acapellaEffects", $filterOptions),
                    ];
                }
            }
        }
    }

    /**
     * @param string $param
     * @param $filterOptions
     * @return string
     * @throws \Exception|\Throwable
     */
    public function createCheckboxFilterFragmentFromFieldSettings(string $param, $filterOptions): string
    {
        $frag = '';
        $filterOptionsToLoop = [];
        if (isset($filterOptions->{$param}) && is_array($filterOptions->{$param})) {
            $filterOptionsToLoop = $filterOptions->{$param};
        } elseif (isset($filterOptions[$param]) && is_array($filterOptions[$param])) {
            $filterOptionsToLoop = $filterOptions[$param];
        }

        if (!empty($filterOptionsToLoop)) {
            $frag = <<<TRACK_KEY
<ul class="menu-box-radiobox-items list:style:none">
TRACK_KEY;
            foreach ($filterOptionsToLoop as $filterValue) {
                $checked = '';
                if (is_array(url()->getParam($param))) {
                    $bpmParam = array_combine(url()->getParam($param), url()->getParam($param));
                    if (key_exists($filterValue, $bpmParam)) {
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