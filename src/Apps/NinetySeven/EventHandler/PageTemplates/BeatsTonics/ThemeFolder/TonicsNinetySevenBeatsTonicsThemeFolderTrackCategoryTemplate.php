<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\NinetySeven\EventHandler\PageTemplates\BeatsTonics\ThemeFolder;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Helper\FieldHelpers;
use App\Modules\Page\Events\AbstractClasses\PageTemplateInterface;
use App\Modules\Page\Events\OnPageTemplate;
use App\Modules\Track\Data\TrackData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class TonicsNinetySevenBeatsTonicsThemeFolderTrackCategoryTemplate implements PageTemplateInterface, HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var OnPageTemplate $event */
        $event->addTemplate($this);
    }

    public function name(): string
    {
        return 'TonicsNinetySeven_BeatsTonics_ThemeFolder_TrackCategory_Template';
    }

    /**
     * @throws \Exception
     */
    public function handleTemplate(OnPageTemplate $pageTemplate): void
    {
        # For Tracks Category
        $routeParams = url()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams();
        $uniqueSlugID = $routeParams[0] ?? null;
        $catSlug = $routeParams[1] ?? null;

        $mainTrackData = db()->Select('*')->From(Tables::getTable(Tables::TRACK_CATEGORIES))
            ->WhereEquals('slug_id', $uniqueSlugID)->FetchFirst();

        if (isset($mainTrackData->slug_id)) {
            $isAPI = url()->getHeaderByKey('type') === 'track_category';
            $fieldSettings = $pageTemplate->getFieldSettings();

            $pageTemplate->setViewName('Apps::NinetySeven/Views/Track/BeatsTonics/ThemeFolder/root');

            $trackTable = Tables::getTable(Tables::TRACKS);
            $trackCategoriesTable = Tables::getTable(Tables::TRACK_CATEGORIES);
            $trackTracksCategoriesTable = Tables::getTable(Tables::TRACK_TRACK_CATEGORIES);
            $genreTable = Tables::getTable(Tables::GENRES);
            $trackGenreTable = Tables::getTable(Tables::TRACK_GENRES);
            $licenseTable = Tables::getTable(Tables::LICENSES);
            $artistTable = Tables::getTable(Tables::ARTISTS);

            $data = db()->Select('*')->From(db()->Select("t.track_id as id, t.slug_id, t.track_title as _name, null as num_tracks, t.track_plays as plays,
        t.track_bpm as bpm, t.image_url, t.audio_url, tl.license_attr, t.field_settings,
        ta.artist_name as artist_name, t.created_at,
        1 as is_track, CONCAT_WS('/', '/tracks', t.slug_id, t.track_slug) as _link")
                ->From("$trackTable t")
                ->Join("$trackTracksCategoriesTable ttc", "t.track_id", "ttc.fk_track_id")
                ->Join("$trackCategoriesTable ct", "ttc.fk_track_cat_id", "ct.track_cat_id")
                ->Join("$licenseTable tl", "tl.license_id", "t.fk_license_id")
                ->Join("$artistTable ta", "ta.artist_id", "t.fk_artist_id")
                ->WhereEquals('ct.track_cat_id', $mainTrackData->track_cat_id)
                ->Raw('UNION')
                ->Select("ct.track_cat_id as id, ct.slug_id, ct.track_cat_name as _name,
        (SELECT COUNT(*) FROM $trackTracksCategoriesTable ttc
        INNER JOIN $trackTable t ON ttc.fk_track_id = t.track_id
        WHERE ttc.fk_track_cat_id = ct.track_cat_id) as num_tracks, null as plays,
        null as bpm, null as image_url, null as audio_url, null as license_attr, ct.field_settings, null as artist_name,
        null as created_at,
        0 as is_track, CONCAT_WS('/', '/track_categories', ct.slug_id, ct.track_cat_slug) as _link")
                ->From("$trackCategoriesTable ct")
                ->WhereEquals('ct.track_cat_parent_id', $mainTrackData->track_cat_id))
                ->As('track_results')
                ->OrderByAsc("is_track")
                ->OrderByDesc("track_results.created_at")
                ->SimplePaginate(AppConfig::getAppPaginationMax());

            $fieldSettingsForMainTrackData = json_decode($mainTrackData->field_settings, true);
            $pageTemplate->getFieldData()->unwrapFieldContent($fieldSettingsForMainTrackData, contentKey: 'track_cat_content');
            $mainTrackData = [...$fieldSettingsForMainTrackData, ...(array)$mainTrackData];

            # Get Filters of a Certain Category and Its Sub Category
            $this->handleFilterTrackKeyForCategorySubCategory($mainTrackData, $fieldSettings);
            $this->handleFilterTrackArtistKeyForCategorySubCategory($mainTrackData, $fieldSettings);
            $this->handleFilterTrackGenreKeyForCategorySubCategory($mainTrackData, $fieldSettings);


           // dd($filterOptions, $mainTrackData);
            $fieldSettings['ThemeFolder'] = $data;
            $fieldSettings['MainTrackData'] = $mainTrackData;
            // dd($fieldSettings);
            $pageTemplate->setFieldSettings($fieldSettings);

        }
    }


    /**
     * @throws \Exception
     */
    public function handleFilterTrackKeyForCategorySubCategory($mainTrackData, &$fieldSettings)
    {
        $trackCatID = $mainTrackData['track_cat_id'];
        $filterOptions = db()->row(<<<FILTER_OPTION
SELECT
JSON_OBJECT(
'track_bpm',
(SELECT JSON_ARRAYAGG(DISTINCT val)
FROM (SELECT JSON_EXTRACT(field_settings, '$.track_bpm') as val FROM {$this->getTrackTable()} WHERE JSON_EXTRACT(field_settings, '$.track_bpm') <> '') as subquery),
'track_default_filter_keys',
(SELECT JSON_ARRAYAGG(DISTINCT val)
FROM (SELECT JSON_EXTRACT(field_settings, '$.track_default_filter_keys') as val FROM {$this->getTrackTable()} WHERE JSON_EXTRACT(field_settings, '$.track_default_filter_keys') <> '') as subquery),
'track_default_filter_mood',
(SELECT JSON_ARRAYAGG(DISTINCT val)
FROM (SELECT JSON_EXTRACT(field_settings, '$.track_default_filter_mood') as val FROM {$this->getTrackTable()} WHERE JSON_EXTRACT(field_settings, '$.track_default_filter_mood') <> '') as subquery),
'track_default_filter_instruments',
(SELECT JSON_ARRAYAGG(DISTINCT val)
FROM (SELECT JSON_EXTRACT(field_settings, '$.track_default_filter_instruments') as val FROM {$this->getTrackTable()} WHERE JSON_EXTRACT(field_settings, '$.track_default_filter_instruments') <> '') as subquery),
'track_default_filter_samplePacks_Type',
(SELECT JSON_ARRAYAGG(DISTINCT val)
FROM (SELECT JSON_EXTRACT(field_settings, '$.track_default_filter_samplePacks_Type') as val FROM {$this->getTrackTable()} WHERE JSON_EXTRACT(field_settings, '$.track_default_filter_samplePacks_Type') <> '') as subquery),
'track_default_filter_acapella_gender',
(SELECT JSON_ARRAYAGG(DISTINCT val)
FROM (SELECT JSON_EXTRACT(field_settings, '$.track_default_filter_acapella_gender') as val FROM {$this->getTrackTable()} WHERE JSON_EXTRACT(field_settings, '$.track_default_filter_acapella_gender') <> '') as subquery),
'track_default_filter_acapella_vocalStyle',
(SELECT JSON_ARRAYAGG(DISTINCT val)
FROM (SELECT JSON_EXTRACT(field_settings, '$.track_default_filter_acapella_vocalStyle') as val FROM {$this->getTrackTable()} WHERE JSON_EXTRACT(field_settings, '$.track_default_filter_acapella_vocalStyle') <> '') as subquery),
'track_default_filter_acapella_emotion',
(SELECT JSON_ARRAYAGG(DISTINCT val)
FROM (SELECT JSON_EXTRACT(field_settings, '$.track_default_filter_acapella_emotion') as val FROM {$this->getTrackTable()} WHERE JSON_EXTRACT(field_settings, '$.track_default_filter_acapella_emotion') <> '') as subquery),
'track_default_filter_acapella_scale',
(SELECT JSON_ARRAYAGG(DISTINCT val)
FROM (SELECT JSON_EXTRACT(field_settings, '$.track_default_filter_acapella_scale') as val FROM {$this->getTrackTable()} WHERE JSON_EXTRACT(field_settings, '$.track_default_filter_acapella_scale') <> '') as subquery),
'track_default_filter_acapella_effects',
(SELECT JSON_ARRAYAGG(DISTINCT val)
FROM (SELECT JSON_EXTRACT(field_settings, '$.track_default_filter_acapella_effects') as val FROM {$this->getTrackTable()} WHERE JSON_EXTRACT(field_settings, '$.track_default_filter_acapella_effects') <> '') as subquery)
) as filters
FROM (
SELECT t.field_settings
FROM tonics_tracks t
INNER JOIN {$this->getTrackTracksCategoriesTable()} ttc ON t.track_id = ttc.fk_track_id
INNER JOIN {$this->getTrackCategoriesTable()} ct ON ttc.fk_track_cat_id = ct.track_cat_id
WHERE ct.track_cat_id = ?
UNION
SELECT ct.field_settings
FROM {$this->getTrackCategoriesTable()} ct
WHERE ct.track_cat_parent_id = ?
) AS track_results
LIMIT 1;
FILTER_OPTION, $trackCatID, $trackCatID);
        if (isset($filterOptions->filters) && helper()->isJSON($filterOptions->filters)){
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
        }
    }

    /**
     * @throws \Exception
     */
    public function handleFilterTrackArtistKeyForCategorySubCategory($mainTrackData, &$fieldSettings)
    {
        $trackCatID = $mainTrackData['track_cat_id'];
        $artists = db()->run(<<<SQL
-- This would get the artist that has track in them within the category and its sub-categories using RECURSIVE CTE
WITH RECURSIVE category_tree AS (
SELECT track_cat_id, track_cat_parent_id, slug_id, track_cat_name, track_cat_status, field_settings, 0 as level
FROM {$this->getTrackCategoriesTable()}
WHERE track_cat_id = ?
UNION ALL
SELECT c.track_cat_id, c.track_cat_parent_id, c.slug_id, c.track_cat_name, c.track_cat_status, c.field_settings, level + 1
FROM {$this->getTrackCategoriesTable()} c
INNER JOIN category_tree ct ON c.track_cat_parent_id = ct.track_cat_id
)
SELECT a.artist_id, a.artist_name, a.artist_slug, COUNT(t.track_id) as num_tracks
FROM tonics_artists a
INNER JOIN {$this->getTrackTable()} t ON a.artist_id = t.fk_artist_id
INNER JOIN {$this->getTrackTracksCategoriesTable()} ttc ON t.track_id = ttc.fk_track_id
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
        }

        $fieldSettings['ThemeFolder_FilterOption_TrackArtists'] = $trackArtistsFrag;
    }

    public function handleFilterTrackGenreKeyForCategorySubCategory($mainTrackData, &$fieldSettings)
    {
        $trackCatID = $mainTrackData['track_cat_id'];
        $genres = db()->run(<<<SQL
-- This would get the genre that has track in them within the category and its sub-categories using RECURSIVE CTE
WITH RECURSIVE category_tree AS (
SELECT track_cat_id, track_cat_parent_id, slug_id, track_cat_name, track_cat_status, field_settings, 0 as level
FROM {$this->getTrackCategoriesTable()}
WHERE track_cat_id = ?
UNION ALL
SELECT c.track_cat_id, c.track_cat_parent_id, c.slug_id, c.track_cat_name, c.track_cat_status, c.field_settings, level + 1
FROM {$this->getTrackCategoriesTable()} c
INNER JOIN category_tree ct ON c.track_cat_parent_id = ct.track_cat_id
)
SELECT g.genre_id, g.genre_name, g.genre_slug, COUNT(t.track_id) as num_tracks
FROM tonics_genres g
INNER JOIN {$this->getTrackGenreTable()} tg ON g.genre_id = tg.fk_genre_id
INNER JOIN {$this->getTrackTable()} t ON tg.fk_track_id = t.track_id
INNER JOIN {$this->getTrackTracksCategoriesTable()} ttc ON t.track_id = ttc.fk_track_id
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

    private function getTrackTable(): string
    {
        return Tables::getTable(Tables::TRACKS);
    }

    private function getTrackCategoriesTable(): string
    {
        return Tables::getTable(Tables::TRACK_CATEGORIES);
    }

    private function getTrackTracksCategoriesTable(): string
    {
        return Tables::getTable(Tables::TRACK_TRACK_CATEGORIES);
    }

    private function getGenreTable(): string
    {
        return Tables::getTable(Tables::GENRES);
    }

    private function getTrackGenreTable(): string
    {
        return Tables::getTable(Tables::TRACK_GENRES);
    }

    private function getLicenseTable(): string
    {
        return Tables::getTable(Tables::LICENSES);
    }

    private function getArtistTable(): string
    {
        return Tables::getTable(Tables::ARTISTS);
    }
}