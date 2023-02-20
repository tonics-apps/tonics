<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\Data;

use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Modules\Post\Events\OnPostCategoryCreate;
use App\Modules\Track\Events\OnTrackCategoryCreate;
use App\Modules\Track\Events\OnTrackCategoryDefaultField;
use App\Modules\Track\Events\OnTrackCreate;
use App\Modules\Track\Events\OnTrackDefaultField;

class TrackData extends AbstractDataLayer
{

    private ?OnTrackDefaultField $onTrackDefaultField;
    private ?FieldData $fieldData;
    private ?OnTrackCategoryDefaultField $onTrackCategoryDefaultField;

    public function __construct(OnTrackDefaultField $onTrackDefaultField = null, OnTrackCategoryDefaultField $onTrackCategoryDefaultField = null, FieldData $fieldData = null)
    {
        $this->onTrackDefaultField = $onTrackDefaultField;
        $this->onTrackCategoryDefaultField = $onTrackCategoryDefaultField;
        $this->fieldData = $fieldData;
    }

    use UniqueSlug;

    public static function getArtistTable(): string
    {
        return Tables::getTable(Tables::ARTISTS);
    }

    public static function getGenreTable(): string
    {
        return Tables::getTable(Tables::GENRES);
    }

    public static function getTrackTable(): string
    {
        return Tables::getTable(Tables::TRACKS);
    }

    public static function getLicenseTable(): string
    {
        return Tables::getTable(Tables::LICENSES);
    }

    public static function getTrackCategoryTable(): string
    {
        return Tables::getTable(Tables::TRACK_CATEGORIES);
    }

    public static function getTrackTracksCategoryTable(): string
    {
        return Tables::getTable(Tables::TRACK_TRACK_CATEGORIES);
    }

    public static function getTrackDefaultFiltersTrackTable(): string
    {
        return Tables::getTable(Tables::TRACK_DEFAULT_FILTERS_TRACKS);
    }

    public static function getTrackDefaultFiltersTable(): string
    {
        return Tables::getTable(Tables::TRACK_DEFAULT_FILTERS);
    }

    public static function getTrackToGenreTable(): string
    {
        return Tables::getTable(Tables::TRACK_GENRES);
    }

    public function getTrackCategoryColumns(): array
    {
        return Tables::$TABLES[Tables::TRACK_CATEGORIES];
    }


    public function getLicenseColumns(): array
    {
        return Tables::$TABLES[Tables::LICENSES];
    }

    public function getArtistColumns(): array
    {
        return Tables::$TABLES[Tables::ARTISTS];
    }

    public function getGenreColumns(): array
    {
        return Tables::$TABLES[Tables::GENRES];
    }

    public function getTrackColumns(): array
    {
        return Tables::$TABLES[Tables::TRACKS];
    }

    /**
     * @return string
     */
    public function getTrackPaginationColumns(): string
    {
        return '`track_id`, `slug_id`, `track_slug`, `track_title`, `track_status`,
        CONCAT( "/", "tracks", slug_id, track_slug ) AS `_link`, `track_title` AS `_name`, `track_id` AS `_id`';
    }

    public function getGenrePaginationColumn(): string
    {
        return '`genre_id`, `genre_name`, `genre_slug`, `genre_description`, `created_at`, `updated_at`,
        CONCAT( "", "/genre/", genre_slug ) AS `_link`, `genre_name` AS `_name`, `genre_id` AS `_id`';
    }

    public function getLicenseURLDownloadListing($licenses, $licenseAttrIDLink = null): string
    {
        $htmlFrag = '';
        foreach ($licenses as $license){

            if ($licenseAttrIDLink !== null && isset($licenseAttrIDLink->{$license->unique_id})){
                $downloadLink = $licenseAttrIDLink->{$license->unique_id};
                $htmlFrag .=<<<HTML
<div class="form-group position:relative">
<label class="menu-settings-handle-name screen-reader-text" for="$license->unique_id">$license->name Download Link</label>
<input type="url" class="input-license-download-url form-control input-checkout bg:white-one color:black border-width:default border:black" id="$license->unique_id" 
name="url_download[]" placeholder="Upload $license->name Download Link" value="$downloadLink">
<input type="hidden" class="form-control input-checkout bg:white-one color:black border-width:default border:black" name="unique_id[]" value="$license->unique_id">
<button aria-pressed="false" type="button" class="upload-license-download-url text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:default
                        margin-top:0 cursor:pointer">Upload Link</button>
</div>
HTML;
            } else {
                $htmlFrag .=<<<HTML
<div class="form-group position:relative">
<label class="menu-settings-handle-name screen-reader-text" for="$license->unique_id">$license->name Download Link</label>
<input type="url" class="input-license-download-url form-control input-checkout bg:white-one color:black border-width:default border:black" id="$license->unique_id" 
name="url_download[]" placeholder="Upload $license->name Download Link" value="">
<input type="hidden" class="form-control input-checkout bg:white-one color:black border-width:default border:black" name="unique_id[]" value="$license->unique_id">
<button aria-pressed="false" type="button" class="upload-license-download-url text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:default
                        margin-top:0 cursor:pointer">Upload Link</button>
</div>
HTML;
            }

        }

        return $htmlFrag;
    }

    /**
     * @param int|null $currentArtistSelectorID
     * @return string
     * @throws \Exception
     */
    public function artistSelectListing(int $currentArtistSelectorID = null): string
    {
        $table = self::getArtistTable();
        $artists = db()->Select('*')->From($table)->FetchResult();
        $htmlFrag = '';
        foreach ($artists as $artist){
            if ($currentArtistSelectorID === $artist->artist_id){
                $htmlFrag .=<<<HTML
<option value='$artist->artist_id' selected>$artist->artist_name</option>
HTML;
            } else {
                $htmlFrag .=<<<HTML
<option value='$artist->artist_id'>$artist->artist_name</option>
HTML;
            }

        }
        return $htmlFrag;
    }

    /**
     * @param array $settings
     * @return string
     * @throws \Exception
     */
    public function genreListing(array $settings): string
    {
        # Collate Settings
        $genres = $settings['genres'] ?? [];
        $showSearch = $settings['showSearch'] ?? true;
        $selected = $settings['selected'] ?? [];
        $inputName = $settings['inputName'] ?? 'fk_genre_id';
        $type = $settings['type'] ?? 'radio';

        $htmlFrag = ''; $htmlMoreFrag = '';

        #
        # RADIO-BOX
        # 
        if(isset($genres->data) && is_array($genres->data) && !empty($genres->data)){

            if ($type === 'checkbox' || $type === 'radio'){
                if ($showSearch){
                    $htmlFrag =<<<HTML
<input id="genre-search" style="margin-bottom: 1em;"
 data-action ="search" 
 data-query="{$genres->path}&input_name=$inputName&type=$type&genre_query="
 data-menuboxname = "genre"
 data-searchvalue =""
 data-type ="$type"
 class="menu-box-item-search position:sticky top:0" type="search" aria-label="Search Genre and Hit Enter" placeholder="Search Genre &amp; Hit Enter">
HTML;
                }

                if (!empty($selected)){
                    $selectedGenres = db()->Select('*')->From(Tables::getTable(Tables::GENRES))->WhereIn('genre_id', $selected)->FetchResult();
                    $selected = array_combine($selected, $selected);
                    foreach ($selectedGenres as $genre){
                        $id = 'genre'. $genre->genre_id . '_' . $genre->genre_slug;
                        $htmlFrag .= <<<HTML
<li class="menu-item">
    <input type="$type"
    id="$id" checked="checked" name="$inputName" value="$genre->genre_id">
    <label for="$id">$genre->genre_name</label>
</li>
HTML;
                    }
                }

                #
                # BUILD FRAG
                #
                foreach ($genres->data as $genre){
                    $id = 'genre'. $genre->genre_id . '_' . $genre->genre_slug;

                    if (key_exists($genre->genre_id, $selected)) {
                        continue;
                    }

                    $htmlFrag .= <<<HTML
<li class="menu-item">
    <input type="$type"
    id="$id" name="$inputName" value="$genre->genre_id">
    <label for="$id">$genre->genre_name</label>
</li>
HTML;
                }
            }

            # MORE BUTTON
            if(isset($genres->has_more) && $genres->has_more){
                $htmlMoreFrag = <<<HTML
 <button 
 type="button"
 data-morepageUrl="$genres->next_page_url&type=$type&input_name=$inputName" 
 data-menuboxname = "genre"
 data-nextpageid="$genres->next_page"
 data-action = "more"
 data-type="$type"
 class="border:none bg:white-one border-width:default border:black padding:gentle margin-top:0 cursor:pointer act-like-button more-button">More →</button>
HTML;
            }
        }

        return $htmlFrag . $htmlMoreFrag;
    }

    /**
     * @param int|null $currentLicenseID
     * @return string
     * @throws \Exception
     */
    public function licenseSelectListing(int $currentLicenseID = null): string
    {
        $htmlFrag = '';
        $table = self::getLicenseTable();
        $licenses = db()->run("SELECT * FROM $table");
        foreach ($licenses as $license){
            if ($currentLicenseID === $license->license_id){
                $htmlFrag .=<<<HTML
<option class="license-selector-value" data-action="license" value='$license->license_id' selected>$license->license_name</option>
HTML;
            } else {
                $htmlFrag .=<<<HTML
<option class="license-selector-value" data-action="license" value='$license->license_id'>$license->license_name</option>
HTML;
            }

        }
        return $htmlFrag;
    }

    public function getLicenseItemsListing($licenses): string
    {
        $frag = '';
        foreach ($licenses as $license){
            $uniqueID = (isset($license->unique_id)) ? $license->unique_id : '';
            $name = (isset($license->name)) ? $license->name : '';
            $price = (isset($license->price)) ? $license->price : '';
            $isEnabled = (isset($license->is_enabled)) ? $license->is_enabled : '';
            $contract = (isset($license->licence_contract)) ? $license->licence_contract : '';

            if ($isEnabled === '0' || $isEnabled === false){
                $isEnabledSelect = "<option value='1'>True</option> <option value='0' selected>False</option>";
            } else {
                $isEnabledSelect = "<option value='1' selected>True</option> <option value='0'>False</option>";
            }

            $frag .= <<<HTML
<li tabIndex="0"
               class="width:100% draggable menu-arranger-li cursor:move">
        <fieldset
            class="width:100% padding:default d:flex justify-content:center pointer-events:none">
            <legend class="bg:pure-black color:white padding:default pointer-events:none d:flex flex-gap:small align-items:center">
                <span class="menu-arranger-text-head">$name</span>
                <button class="dropdown-toggle bg:transparent border:none pointer-events:all cursor:pointer"
                        aria-expanded="false" aria-label="Expand child menu">
                    <svg class="icon:admin tonics-arrow-down color:white">
                        <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
                    </svg>
                </button>
            </legend>
            <form class="widgetSettings d:none flex-d:column license-widget-information pointer-events:all owl width:100%">
                <div class="form-group">
                    <label class="menu-settings-handle-name" for="license-name">License Name
                        <input id="license-name" name="name" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray" 
                        value="$name" placeholder="Overwrite the license name">
                        <input name="unique_id" type="hidden" value="$uniqueID" placeholder="Overwrite the license name">
                    </label>
                </div>
                <div class="form-group">
                    <label class="menu-settings-handle-name" for="license-price">Price
                        <input id="license-price" name="price" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray" 
                        value="$price">
                    </label>
                </div>        
                <div class="form-group position:relative">
                    <label class="menu-settings-handle-name screen-reader-text" for="license-contract">Licence Contract</label>
                        <input type="url" class="form-control input-checkout bg:white-one color:black border-width:default border:black license-contract" id="license-contract" 
                        name="licence_contract" placeholder="Upload Licence Contract, Can Be Empty" value="$contract">
                    <button aria-pressed="false" type="button" class="license-contract-button act-like-button text show-password bg:pure-black color:white cursor:pointer">Upload Contract</button>
                </div>
                <div class="form-group">
                    <label class="menu-settings-handle-name" for="is_enabled">Enable License
                         <select name="is_enabled" class="default-selector" id="is_enabled">
                                    $isEnabledSelect
                          </select>
                    </label>
                </div>
                <div class="form-group">
                    <button name="delete" class="delete-license-button listing-button border:none bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cursor:pointer act-like-button">
                        Delete License Item
                    </button>
                </div>
            </form>
        </fieldset>
    </li>
HTML;

        }

        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function createLicense(array $ignore = []): array
    {
        $slug = $this->generateUniqueSlug(self::getLicenseTable(),
            'license_slug', helper()->slug(input()->fromPost()->retrieve('license_slug')));

        $license = []; $postColumns = array_flip($this->getLicenseColumns());
        foreach (input()->fromPost()->all() as $inputKey => $inputValue){
            if (key_exists($inputKey, $postColumns) && input()->fromPost()->has($inputKey)){

                if($inputKey === 'created_at'){
                    $license[$inputKey] = helper()->date(datetime: $inputValue);
                    continue;
                }
                if ($inputKey === 'license_slug'){
                    $license[$inputKey] = $slug;
                    continue;
                }
                $license[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $license);
        if (!empty($ignores)){
            foreach ($ignores as $v){
                unset($license[$v]);
            }
        }

        return $license;
    }

    /**
     * @throws \Exception
     */
    public function createTrack(array $ignore = [], bool $prepareFieldSettings = true): array
    {
        $slug = $this->generateUniqueSlug(self::getTrackTable(),
            'track_slug', helper()->slug(input()->fromPost()->retrieve('track_slug')));

        $track = []; $postColumns = array_flip($this->getTrackColumns());
        foreach (input()->fromPost()->all() as $inputKey => $inputValue){
            if (key_exists($inputKey, $postColumns) && input()->fromPost()->has($inputKey)){

                if($inputKey === 'created_at'){
                    $track[$inputKey] = helper()->date(datetime: $inputValue);
                    continue;
                }
                if ($inputKey === 'track_slug'){
                    $track[$inputKey] = $slug;
                    continue;
                }
                $track[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $track);
        if (!empty($ignores)){
            foreach ($ignores as $v){
                unset($track[$v]);
            }
        }

        if ($prepareFieldSettings){
            return $this->getFieldData()->prepareFieldSettingsDataForCreateOrUpdate($track, 'track_title', 'track_content');
        }

        return $track;
    }

    /**
     * @throws \Exception
     */
    public function createArtist(array $ignore = []): array
    {
        $slug = $this->generateUniqueSlug(self::getArtistTable(),
            'artist_slug', helper()->slug(input()->fromPost()->retrieve('artist_slug')));

        $artist = []; $postColumns = array_flip($this->getArtistColumns());
        foreach (input()->fromPost()->all() as $inputKey => $inputValue){
            if (key_exists($inputKey, $postColumns) && input()->fromPost()->has($inputKey)){
                if ($inputKey === 'artist_slug'){
                    $artist[$inputKey] = $slug;
                    continue;
                }
                $artist[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $artist);
        if (!empty($ignores)){
            foreach ($ignores as $v){
                unset($artist[$v]);
            }
        }

        return $artist;
    }

    /**
     * @throws \Exception
     */
    public function createGenre(array $ignore = [])
    {
        $slug = $this->generateUniqueSlug(self::getGenreTable(),
            'genre_slug', helper()->slug(input()->fromPost()->retrieve('genre_slug')));

        $genre = []; $postColumns = array_flip($this->getGenreColumns());
        foreach (input()->fromPost()->all() as $inputKey => $inputValue){
            if (key_exists($inputKey, $postColumns) && input()->fromPost()->has($inputKey)){
                if ($inputKey === 'genre_slug'){
                    $genre[$inputKey] = $slug;
                    continue;
                }
                $genre[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $genre);
        if (!empty($ignores)){
            foreach ($ignores as $v){
                unset($genre[$v]);
            }
        }

        return $genre;
    }

    /**
     * @param string $slug
     * @return mixed
     * @throws \Exception
     */
    public function getLicenseID(string $slug): mixed
    {
        $table = self::getLicenseTable();
        return db()->row("SELECT `license_id` FROM $table WHERE `license_slug` = ?", $slug)->license_id ?? null;
    }

    /**
     * @throws \Exception
     */
    public function getGenrePaginationData(): ?object
    {
        $settings = [
            'query_name' => 'genre_query',
            'page_name' => 'genre_page',
            'per_page_name' => 'genre_per_page',
        ];
       return $this->generatePaginationData(
            $this->getGenrePaginationColumn(),
            'genre_name',
            self::getGenreTable(), 200, $settings);
    }

    /**
     * @throws \Exception
     */
    public function genreMetaBox($genre, string $inputName='fk_genre_id', $type = 'radio', $selected = [])
    {
        $type = url()->getParam('type', $type);
        $inputName = url()->getParam('input_name', $inputName);

        if (url()->getHeaderByKey('menuboxname') === 'genre') {
            if (url()->getHeaderByKey('action') === 'more' || url()->getHeaderByKey('action') === 'search') {
                $genreSettings = ['genres' => $genre, 'showSearch' => false, 'selected' => $selected, 'type' => $type, 'inputName' => $inputName];
                $frag = $this->genreListing($genreSettings);
                helper()->onSuccess($frag);
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function licenseMetaBox(OnTrackCreate $onTrackCreate = null){
        if (url()->getHeaderByKey('action') === 'license'){
            $licenseID = (int)url()->getHeaderByKey('licenseID');
            $licenseAttrIDLink = null;

            if ($onTrackCreate !== null && $onTrackCreate->getTrackFKLicenseID() === $licenseID){
                $licenseAttr = $onTrackCreate->getTrackLicenseAttr();
                $licenseAttrIDLink = (empty($onTrackCreate->getTrackLicenseAttrToIDLink())) ? null : $onTrackCreate->getTrackLicenseAttrToIDLink();

            } else {
                $licenseAttr = $this->selectWithCondition(self::getLicenseTable(), ['license_attr'], 'license_id = ?', [$licenseID]);
                $licenseAttr = json_decode($licenseAttr->license_attr);
            }

            if (is_array($licenseAttr)){
                helper()->onSuccess($this->getLicenseURLDownloadListing($licenseAttr, $licenseAttrIDLink));
            }
        }
    }
    
    /**
     * @throws \Exception
     */
    public function setDefaultTrackCategoryIfNotSet()
    {
        if (input()->fromPost()->hasValue('fk_track_cat_id') === false && input()->fromPost()->hasValue('fk_track_cat_id[]')){
            $_POST['fk_track_cat_id'] = input()->fromPost()->retrieve('fk_track_cat_id[]');
        }

        if (input()->fromPost()->hasValue('fk_track_cat_id') === false) {
            $findDefault = $this->findDefaultTrackCategory();

            if (isset($findDefault->track_cat_id)) {
                $_POST['fk_track_cat_id'] = [$findDefault->track_cat_id];
                return;
            }

            $defaultCategory = [
                'track_cat_name' => 'Default Track Category',
                'track_cat_slug' => 'default-track-category',
                'track_cat_status' => 1,
            ];

            $returning = db()->insertReturning(self::getTrackCategoryTable(), $defaultCategory, $this->getTrackCategoryColumns(), 'track_cat_id');
            $_POST['fk_track_cat_id'] = [$returning->track_cat_id];
            $onTrackCategoryCreate = new OnTrackCategoryCreate($returning, $this);
            event()->dispatch($onTrackCategoryCreate);
        }
    }

    /**
     * @throws \Exception
     */
    public function findDefaultTrackCategory()
    {
        return db()->Select(table()->pickTable(self::getTrackCategoryTable(), ['track_cat_slug', 'track_cat_id']))
            ->From(self::getTrackCategoryTable())->WhereEquals('track_cat_slug', 'default-track-category')
            ->FetchFirst();
    }
    

    /**
     * @throws \Exception
     */
    public function createCategory(array $ignore = [], bool $prepareFieldSettings = true): array
    {
        $slug = $this->generateUniqueSlug(self::getTrackCategoryTable(),
            'track_cat_slug',
            helper()->slug(input()->fromPost()->retrieve('track_cat_slug')));

        $category = [];
        $categoryCols = array_flip($this->getTrackCategoryColumns());
        if (input()->fromPost()->hasValue('track_cat_parent_id')) {
            $category['track_cat_parent_id'] = input()->fromPost()->retrieve('track_cat_parent_id');
        }

        foreach (input()->fromPost()->all() as $inputKey => $inputValue) {
            if (key_exists($inputKey, $categoryCols) && input()->fromPost()->has($inputKey)) {
                if ($inputKey === 'track_cat_parent_id' && empty($inputValue)) {
                    $category[$inputKey] = null;
                    continue;
                }

                if ($inputKey === 'created_at') {
                    $category[$inputKey] = helper()->date(datetime: $inputValue);
                    continue;
                }
                if ($inputKey === 'track_cat_slug') {
                    $category[$inputKey] = $slug;
                    continue;
                }
                $category[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $category);
        if (!empty($ignores)) {
            foreach ($ignores as $v) {
                unset($category[$v]);
            }
        }

        if ($prepareFieldSettings){
            return $this->getFieldData()->prepareFieldSettingsDataForCreateOrUpdate($category, 'track_cat_name', 'track_cat_content');
        }

        return $category;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getCategory(): mixed
    {
        $categoryTable = $this->getTrackCategoryTable();
        return db()->run("
        WITH RECURSIVE track_cat_recursive AS 
	( SELECT track_cat_id, track_cat_parent_id, track_cat_slug, track_cat_name, CAST(track_cat_slug AS VARCHAR (255))
            AS path
      FROM $categoryTable WHERE track_cat_parent_id IS NULL
      UNION ALL
      SELECT tcs.track_cat_id, tcs.track_cat_parent_id, tcs.track_cat_slug, tcs.track_cat_name, CONCAT(path, '/' , tcs.track_cat_slug)
      FROM track_cat_recursive as fr JOIN $categoryTable as tcs ON fr.track_cat_id = tcs.track_cat_parent_id
      ) 
     SELECT * FROM track_cat_recursive;
        ");
    }

    /**
     * @param null $currentCatData
     * @return string
     * @throws \Exception
     */
    public function getCategoryHTMLSelect($currentCatData = null): string
    {
        $categories = helper()->generateTree(['parent_id' => 'track_cat_parent_id', 'id' => 'track_cat_id'], $this->getCategory());
        $catSelectFrag = '';
        if (count($categories) > 0) {
            foreach ($categories as $category) {
                $catSelectFrag .= $this->getCategoryHTMLSelectFragments($category, $currentCatData);
            }
        }

        return $catSelectFrag;
    }

    /**
     * @param $category
     * @param null $currentCatIDS
     * @return string
     * @throws \Exception
     */
    private function getCategoryHTMLSelectFragments($category, $currentCatIDS = null): string
    {
        $currentCatIDS = (is_object($currentCatIDS) && property_exists($currentCatIDS, 'track_cat_parent_id')) ? $currentCatIDS->track_cat_parent_id : $currentCatIDS;

        if (!is_array($currentCatIDS)){
            $currentCatIDS = [$currentCatIDS];
        }

        $catSelectFrag = '';
        $catID = $category->track_cat_id;
        if ($category->depth === 0) {
            $catSelectFrag .= <<<CAT
    <option data-is-parent="yes" data-depth="$category->depth"
            data-slug="$category->track_cat_slug" data-path="/$category->path/" value="$catID"
CAT;
            foreach ($currentCatIDS as $currentCatID){
                if ($currentCatID == $category->track_cat_id) {
                    $catSelectFrag .= 'selected';
                }
            }

            $catSelectFrag .= ">" . $category->track_cat_name;
        } else {
            $catSelectFrag .= <<<CAT
    <option data-slug="$category->track_cat_slug" data-depth="$category->depth" data-path="/$category->path/"
            value="$catID"
CAT;
            foreach ($currentCatIDS as $currentCatID){
                if ($currentCatID == $category->track_cat_id) {
                    $catSelectFrag .= 'selected';
                }
            }

            $catSelectFrag .= ">" . str_repeat("&nbsp;&nbsp;&nbsp;", $category->depth + 1);
            $catSelectFrag .= $category->track_cat_name;
        }
        $catSelectFrag .= "</option>";

        if (isset($category->_children)) {
            foreach ($category->_children as $catChildren) {
                $catSelectFrag .= $this->getCategoryHTMLSelectFragments($catChildren, $currentCatIDS);
            }
        }

        return $catSelectFrag;

    }

    /**
     * @param $track
     * @param string $fieldSettingsKey
     * @return void
     * @throws \Exception
     */
    public  function unwrapForTrack(&$track, string $fieldSettingsKey = 'field_settings'): void
    {
        $fieldSettings = json_decode($track[$fieldSettingsKey], true);
        $this->getFieldData()->unwrapFieldContent($fieldSettings, contentKey: 'track_content');
        $track = [...$fieldSettings, ...$track];
    }

    public static function defaultFilterData(): array
    {
        return [
            ['tdf_name' => '1', 'tdf_type' => 'bpm'],
            ['tdf_name' => '2', 'tdf_type' => 'bpm'],
            ['tdf_name' => '3', 'tdf_type' => 'bpm'],
            ['tdf_name' => '4', 'tdf_type' => 'bpm'],
            ['tdf_name' => '5', 'tdf_type' => 'bpm'],
            ['tdf_name' => '6', 'tdf_type' => 'bpm'],
            ['tdf_name' => '7', 'tdf_type' => 'bpm'],
            ['tdf_name' => '8', 'tdf_type' => 'bpm'],
            ['tdf_name' => '9', 'tdf_type' => 'bpm'],
            ['tdf_name' => '10', 'tdf_type' => 'bpm'],
            ['tdf_name' => '11', 'tdf_type' => 'bpm'],
            ['tdf_name' => '12', 'tdf_type' => 'bpm'],
            ['tdf_name' => '13', 'tdf_type' => 'bpm'],
            ['tdf_name' => '14', 'tdf_type' => 'bpm'],
            ['tdf_name' => '15', 'tdf_type' => 'bpm'],
            ['tdf_name' => '16', 'tdf_type' => 'bpm'],
            ['tdf_name' => '17', 'tdf_type' => 'bpm'],
            ['tdf_name' => '18', 'tdf_type' => 'bpm'],
            ['tdf_name' => '19', 'tdf_type' => 'bpm'],
            ['tdf_name' => '20', 'tdf_type' => 'bpm'],
            ['tdf_name' => '21', 'tdf_type' => 'bpm'],
            ['tdf_name' => '22', 'tdf_type' => 'bpm'],
            ['tdf_name' => '23', 'tdf_type' => 'bpm'],
            ['tdf_name' => '24', 'tdf_type' => 'bpm'],
            ['tdf_name' => '25', 'tdf_type' => 'bpm'],
            ['tdf_name' => '26', 'tdf_type' => 'bpm'],
            ['tdf_name' => '27', 'tdf_type' => 'bpm'],
            ['tdf_name' => '28', 'tdf_type' => 'bpm'],
            ['tdf_name' => '29', 'tdf_type' => 'bpm'],
            ['tdf_name' => '30', 'tdf_type' => 'bpm'],
            ['tdf_name' => '31', 'tdf_type' => 'bpm'],
            ['tdf_name' => '32', 'tdf_type' => 'bpm'],
            ['tdf_name' => '33', 'tdf_type' => 'bpm'],
            ['tdf_name' => '34', 'tdf_type' => 'bpm'],
            ['tdf_name' => '35', 'tdf_type' => 'bpm'],
            ['tdf_name' => '36', 'tdf_type' => 'bpm'],
            ['tdf_name' => '37', 'tdf_type' => 'bpm'],
            ['tdf_name' => '38', 'tdf_type' => 'bpm'],
            ['tdf_name' => '39', 'tdf_type' => 'bpm'],
            ['tdf_name' => '40', 'tdf_type' => 'bpm'],
            ['tdf_name' => '41', 'tdf_type' => 'bpm'],
            ['tdf_name' => '42', 'tdf_type' => 'bpm'],
            ['tdf_name' => '43', 'tdf_type' => 'bpm'],
            ['tdf_name' => '44', 'tdf_type' => 'bpm'],
            ['tdf_name' => '45', 'tdf_type' => 'bpm'],
            ['tdf_name' => '46', 'tdf_type' => 'bpm'],
            ['tdf_name' => '47', 'tdf_type' => 'bpm'],
            ['tdf_name' => '48', 'tdf_type' => 'bpm'],
            ['tdf_name' => '49', 'tdf_type' => 'bpm'],
            ['tdf_name' => '50', 'tdf_type' => 'bpm'],
            ['tdf_name' => '51', 'tdf_type' => 'bpm'],
            ['tdf_name' => '52', 'tdf_type' => 'bpm'],
            ['tdf_name' => '53', 'tdf_type' => 'bpm'],
            ['tdf_name' => '54', 'tdf_type' => 'bpm'],
            ['tdf_name' => '55', 'tdf_type' => 'bpm'],
            ['tdf_name' => '56', 'tdf_type' => 'bpm'],
            ['tdf_name' => '57', 'tdf_type' => 'bpm'],
            ['tdf_name' => '58', 'tdf_type' => 'bpm'],
            ['tdf_name' => '59', 'tdf_type' => 'bpm'],
            ['tdf_name' => '60', 'tdf_type' => 'bpm'],
            ['tdf_name' => '61', 'tdf_type' => 'bpm'],
            ['tdf_name' => '62', 'tdf_type' => 'bpm'],
            ['tdf_name' => '63', 'tdf_type' => 'bpm'],
            ['tdf_name' => '64', 'tdf_type' => 'bpm'],
            ['tdf_name' => '65', 'tdf_type' => 'bpm'],
            ['tdf_name' => '66', 'tdf_type' => 'bpm'],
            ['tdf_name' => '67', 'tdf_type' => 'bpm'],
            ['tdf_name' => '68', 'tdf_type' => 'bpm'],
            ['tdf_name' => '69', 'tdf_type' => 'bpm'],
            ['tdf_name' => '70', 'tdf_type' => 'bpm'],
            ['tdf_name' => '71', 'tdf_type' => 'bpm'],
            ['tdf_name' => '72', 'tdf_type' => 'bpm'],
            ['tdf_name' => '73', 'tdf_type' => 'bpm'],
            ['tdf_name' => '74', 'tdf_type' => 'bpm'],
            ['tdf_name' => '75', 'tdf_type' => 'bpm'],
            ['tdf_name' => '76', 'tdf_type' => 'bpm'],
            ['tdf_name' => '77', 'tdf_type' => 'bpm'],
            ['tdf_name' => '78', 'tdf_type' => 'bpm'],
            ['tdf_name' => '79', 'tdf_type' => 'bpm'],
            ['tdf_name' => '80', 'tdf_type' => 'bpm'],
            ['tdf_name' => '81', 'tdf_type' => 'bpm'],
            ['tdf_name' => '82', 'tdf_type' => 'bpm'],
            ['tdf_name' => '83', 'tdf_type' => 'bpm'],
            ['tdf_name' => '84', 'tdf_type' => 'bpm'],
            ['tdf_name' => '85', 'tdf_type' => 'bpm'],
            ['tdf_name' => '86', 'tdf_type' => 'bpm'],
            ['tdf_name' => '87', 'tdf_type' => 'bpm'],
            ['tdf_name' => '88', 'tdf_type' => 'bpm'],
            ['tdf_name' => '89', 'tdf_type' => 'bpm'],
            ['tdf_name' => '90', 'tdf_type' => 'bpm'],
            ['tdf_name' => '91', 'tdf_type' => 'bpm'],
            ['tdf_name' => '92', 'tdf_type' => 'bpm'],
            ['tdf_name' => '93', 'tdf_type' => 'bpm'],
            ['tdf_name' => '94', 'tdf_type' => 'bpm'],
            ['tdf_name' => '95', 'tdf_type' => 'bpm'],
            ['tdf_name' => '96', 'tdf_type' => 'bpm'],
            ['tdf_name' => '97', 'tdf_type' => 'bpm'],
            ['tdf_name' => '98', 'tdf_type' => 'bpm'],
            ['tdf_name' => '99', 'tdf_type' => 'bpm'],
            ['tdf_name' => '100', 'tdf_type' => 'bpm'],
            ['tdf_name' => '101', 'tdf_type' => 'bpm'],
            ['tdf_name' => '102', 'tdf_type' => 'bpm'],
            ['tdf_name' => '103', 'tdf_type' => 'bpm'],
            ['tdf_name' => '104', 'tdf_type' => 'bpm'],
            ['tdf_name' => '105', 'tdf_type' => 'bpm'],
            ['tdf_name' => '106', 'tdf_type' => 'bpm'],
            ['tdf_name' => '107', 'tdf_type' => 'bpm'],
            ['tdf_name' => '108', 'tdf_type' => 'bpm'],
            ['tdf_name' => '109', 'tdf_type' => 'bpm'],
            ['tdf_name' => '110', 'tdf_type' => 'bpm'],
            ['tdf_name' => '111', 'tdf_type' => 'bpm'],
            ['tdf_name' => '112', 'tdf_type' => 'bpm'],
            ['tdf_name' => '113', 'tdf_type' => 'bpm'],
            ['tdf_name' => '114', 'tdf_type' => 'bpm'],
            ['tdf_name' => '115', 'tdf_type' => 'bpm'],
            ['tdf_name' => '116', 'tdf_type' => 'bpm'],
            ['tdf_name' => '117', 'tdf_type' => 'bpm'],
            ['tdf_name' => '118', 'tdf_type' => 'bpm'],
            ['tdf_name' => '119', 'tdf_type' => 'bpm'],
            ['tdf_name' => '120', 'tdf_type' => 'bpm'],
            ['tdf_name' => '121', 'tdf_type' => 'bpm'],
            ['tdf_name' => '122', 'tdf_type' => 'bpm'],
            ['tdf_name' => '123', 'tdf_type' => 'bpm'],
            ['tdf_name' => '124', 'tdf_type' => 'bpm'],
            ['tdf_name' => '125', 'tdf_type' => 'bpm'],
            ['tdf_name' => '126', 'tdf_type' => 'bpm'],
            ['tdf_name' => '127', 'tdf_type' => 'bpm'],
            ['tdf_name' => '128', 'tdf_type' => 'bpm'],
            ['tdf_name' => '129', 'tdf_type' => 'bpm'],
            ['tdf_name' => '130', 'tdf_type' => 'bpm'],
            ['tdf_name' => '131', 'tdf_type' => 'bpm'],
            ['tdf_name' => '132', 'tdf_type' => 'bpm'],
            ['tdf_name' => '133', 'tdf_type' => 'bpm'],
            ['tdf_name' => '134', 'tdf_type' => 'bpm'],
            ['tdf_name' => '135', 'tdf_type' => 'bpm'],
            ['tdf_name' => '136', 'tdf_type' => 'bpm'],
            ['tdf_name' => '137', 'tdf_type' => 'bpm'],
            ['tdf_name' => '138', 'tdf_type' => 'bpm'],
            ['tdf_name' => '139', 'tdf_type' => 'bpm'],
            ['tdf_name' => '140', 'tdf_type' => 'bpm'],
            ['tdf_name' => '141', 'tdf_type' => 'bpm'],
            ['tdf_name' => '142', 'tdf_type' => 'bpm'],
            ['tdf_name' => '143', 'tdf_type' => 'bpm'],
            ['tdf_name' => '144', 'tdf_type' => 'bpm'],
            ['tdf_name' => '145', 'tdf_type' => 'bpm'],
            ['tdf_name' => '146', 'tdf_type' => 'bpm'],
            ['tdf_name' => '147', 'tdf_type' => 'bpm'],
            ['tdf_name' => '148', 'tdf_type' => 'bpm'],
            ['tdf_name' => '149', 'tdf_type' => 'bpm'],
            ['tdf_name' => '150', 'tdf_type' => 'bpm'],
            ['tdf_name' => '151', 'tdf_type' => 'bpm'],
            ['tdf_name' => '152', 'tdf_type' => 'bpm'],
            ['tdf_name' => '153', 'tdf_type' => 'bpm'],
            ['tdf_name' => '154', 'tdf_type' => 'bpm'],
            ['tdf_name' => '155', 'tdf_type' => 'bpm'],
            ['tdf_name' => '156', 'tdf_type' => 'bpm'],
            ['tdf_name' => '157', 'tdf_type' => 'bpm'],
            ['tdf_name' => '158', 'tdf_type' => 'bpm'],
            ['tdf_name' => '159', 'tdf_type' => 'bpm'],
            ['tdf_name' => '160', 'tdf_type' => 'bpm'],
            ['tdf_name' => '161', 'tdf_type' => 'bpm'],
            ['tdf_name' => '162', 'tdf_type' => 'bpm'],
            ['tdf_name' => '163', 'tdf_type' => 'bpm'],
            ['tdf_name' => '164', 'tdf_type' => 'bpm'],
            ['tdf_name' => '165', 'tdf_type' => 'bpm'],
            ['tdf_name' => '166', 'tdf_type' => 'bpm'],
            ['tdf_name' => '167', 'tdf_type' => 'bpm'],
            ['tdf_name' => '168', 'tdf_type' => 'bpm'],
            ['tdf_name' => '169', 'tdf_type' => 'bpm'],
            ['tdf_name' => '170', 'tdf_type' => 'bpm'],
            ['tdf_name' => '171', 'tdf_type' => 'bpm'],
            ['tdf_name' => '172', 'tdf_type' => 'bpm'],
            ['tdf_name' => '173', 'tdf_type' => 'bpm'],
            ['tdf_name' => '174', 'tdf_type' => 'bpm'],
            ['tdf_name' => '175', 'tdf_type' => 'bpm'],
            ['tdf_name' => '176', 'tdf_type' => 'bpm'],
            ['tdf_name' => '177', 'tdf_type' => 'bpm'],
            ['tdf_name' => '178', 'tdf_type' => 'bpm'],
            ['tdf_name' => '179', 'tdf_type' => 'bpm'],
            ['tdf_name' => '180', 'tdf_type' => 'bpm'],
            ['tdf_name' => '181', 'tdf_type' => 'bpm'],
            ['tdf_name' => '182', 'tdf_type' => 'bpm'],
            ['tdf_name' => '183', 'tdf_type' => 'bpm'],
            ['tdf_name' => '184', 'tdf_type' => 'bpm'],
            ['tdf_name' => '185', 'tdf_type' => 'bpm'],
            ['tdf_name' => '186', 'tdf_type' => 'bpm'],
            ['tdf_name' => '187', 'tdf_type' => 'bpm'],
            ['tdf_name' => '188', 'tdf_type' => 'bpm'],
            ['tdf_name' => '189', 'tdf_type' => 'bpm'],
            ['tdf_name' => '190', 'tdf_type' => 'bpm'],
            ['tdf_name' => '191', 'tdf_type' => 'bpm'],
            ['tdf_name' => '192', 'tdf_type' => 'bpm'],
            ['tdf_name' => '193', 'tdf_type' => 'bpm'],
            ['tdf_name' => '194', 'tdf_type' => 'bpm'],
            ['tdf_name' => '195', 'tdf_type' => 'bpm'],
            ['tdf_name' => '196', 'tdf_type' => 'bpm'],
            ['tdf_name' => '197', 'tdf_type' => 'bpm'],
            ['tdf_name' => '198', 'tdf_type' => 'bpm'],
            ['tdf_name' => '199', 'tdf_type' => 'bpm'],
            ['tdf_name' => '200', 'tdf_type' => 'bpm'],
            ['tdf_name' => '201', 'tdf_type' => 'bpm'],
            ['tdf_name' => '202', 'tdf_type' => 'bpm'],
            ['tdf_name' => '203', 'tdf_type' => 'bpm'],
            ['tdf_name' => '204', 'tdf_type' => 'bpm'],
            ['tdf_name' => '205', 'tdf_type' => 'bpm'],
            ['tdf_name' => '206', 'tdf_type' => 'bpm'],
            ['tdf_name' => '207', 'tdf_type' => 'bpm'],
            ['tdf_name' => '208', 'tdf_type' => 'bpm'],
            ['tdf_name' => '209', 'tdf_type' => 'bpm'],
            ['tdf_name' => '210', 'tdf_type' => 'bpm'],
            ['tdf_name' => '211', 'tdf_type' => 'bpm'],
            ['tdf_name' => '212', 'tdf_type' => 'bpm'],
            ['tdf_name' => '213', 'tdf_type' => 'bpm'],
            ['tdf_name' => '214', 'tdf_type' => 'bpm'],
            ['tdf_name' => '215', 'tdf_type' => 'bpm'],
            ['tdf_name' => '216', 'tdf_type' => 'bpm'],
            ['tdf_name' => '217', 'tdf_type' => 'bpm'],
            ['tdf_name' => '218', 'tdf_type' => 'bpm'],
            ['tdf_name' => '219', 'tdf_type' => 'bpm'],
            ['tdf_name' => '220', 'tdf_type' => 'bpm'],
            ['tdf_name' => '221', 'tdf_type' => 'bpm'],
            ['tdf_name' => '222', 'tdf_type' => 'bpm'],
            ['tdf_name' => '223', 'tdf_type' => 'bpm'],
            ['tdf_name' => '224', 'tdf_type' => 'bpm'],
            ['tdf_name' => '225', 'tdf_type' => 'bpm'],
            ['tdf_name' => '226', 'tdf_type' => 'bpm'],
            ['tdf_name' => '227', 'tdf_type' => 'bpm'],
            ['tdf_name' => '228', 'tdf_type' => 'bpm'],
            ['tdf_name' => '229', 'tdf_type' => 'bpm'],
            ['tdf_name' => '230', 'tdf_type' => 'bpm'],
            ['tdf_name' => '231', 'tdf_type' => 'bpm'],
            ['tdf_name' => '232', 'tdf_type' => 'bpm'],
            ['tdf_name' => '233', 'tdf_type' => 'bpm'],
            ['tdf_name' => '234', 'tdf_type' => 'bpm'],
            ['tdf_name' => '235', 'tdf_type' => 'bpm'],
            ['tdf_name' => '236', 'tdf_type' => 'bpm'],
            ['tdf_name' => '237', 'tdf_type' => 'bpm'],
            ['tdf_name' => '238', 'tdf_type' => 'bpm'],
            ['tdf_name' => '239', 'tdf_type' => 'bpm'],
            ['tdf_name' => '240', 'tdf_type' => 'bpm'],
            ['tdf_name' => '241', 'tdf_type' => 'bpm'],
            ['tdf_name' => '242', 'tdf_type' => 'bpm'],
            ['tdf_name' => '243', 'tdf_type' => 'bpm'],
            ['tdf_name' => '244', 'tdf_type' => 'bpm'],
            ['tdf_name' => '245', 'tdf_type' => 'bpm'],
            ['tdf_name' => '246', 'tdf_type' => 'bpm'],
            ['tdf_name' => '247', 'tdf_type' => 'bpm'],
            ['tdf_name' => '248', 'tdf_type' => 'bpm'],
            ['tdf_name' => '249', 'tdf_type' => 'bpm'],
            ['tdf_name' => '250', 'tdf_type' => 'bpm'],
            ['tdf_name' => '251', 'tdf_type' => 'bpm'],
            ['tdf_name' => '252', 'tdf_type' => 'bpm'],
            ['tdf_name' => '253', 'tdf_type' => 'bpm'],
            ['tdf_name' => '254', 'tdf_type' => 'bpm'],
            ['tdf_name' => '255', 'tdf_type' => 'bpm'],
            ['tdf_name' => '256', 'tdf_type' => 'bpm'],
            ['tdf_name' => '257', 'tdf_type' => 'bpm'],
            ['tdf_name' => '258', 'tdf_type' => 'bpm'],
            ['tdf_name' => '259', 'tdf_type' => 'bpm'],
            ['tdf_name' => '260', 'tdf_type' => 'bpm'],
            ['tdf_name' => '261', 'tdf_type' => 'bpm'],
            ['tdf_name' => '262', 'tdf_type' => 'bpm'],
            ['tdf_name' => '263', 'tdf_type' => 'bpm'],
            ['tdf_name' => '264', 'tdf_type' => 'bpm'],
            ['tdf_name' => '265', 'tdf_type' => 'bpm'],
            ['tdf_name' => '266', 'tdf_type' => 'bpm'],
            ['tdf_name' => '267', 'tdf_type' => 'bpm'],
            ['tdf_name' => '268', 'tdf_type' => 'bpm'],
            ['tdf_name' => '269', 'tdf_type' => 'bpm'],
            ['tdf_name' => '270', 'tdf_type' => 'bpm'],
            ['tdf_name' => '271', 'tdf_type' => 'bpm'],
            ['tdf_name' => '272', 'tdf_type' => 'bpm'],
            ['tdf_name' => '273', 'tdf_type' => 'bpm'],
            ['tdf_name' => '274', 'tdf_type' => 'bpm'],
            ['tdf_name' => '275', 'tdf_type' => 'bpm'],
            ['tdf_name' => '276', 'tdf_type' => 'bpm'],
            ['tdf_name' => '277', 'tdf_type' => 'bpm'],
            ['tdf_name' => '278', 'tdf_type' => 'bpm'],
            ['tdf_name' => '279', 'tdf_type' => 'bpm'],
            ['tdf_name' => '280', 'tdf_type' => 'bpm'],
            ['tdf_name' => '281', 'tdf_type' => 'bpm'],
            ['tdf_name' => '282', 'tdf_type' => 'bpm'],
            ['tdf_name' => '283', 'tdf_type' => 'bpm'],
            ['tdf_name' => '284', 'tdf_type' => 'bpm'],
            ['tdf_name' => '285', 'tdf_type' => 'bpm'],
            ['tdf_name' => '286', 'tdf_type' => 'bpm'],
            ['tdf_name' => '287', 'tdf_type' => 'bpm'],
            ['tdf_name' => '288', 'tdf_type' => 'bpm'],
            ['tdf_name' => '289', 'tdf_type' => 'bpm'],
            ['tdf_name' => '290', 'tdf_type' => 'bpm'],
            ['tdf_name' => '291', 'tdf_type' => 'bpm'],
            ['tdf_name' => '292', 'tdf_type' => 'bpm'],
            ['tdf_name' => '293', 'tdf_type' => 'bpm'],
            ['tdf_name' => '294', 'tdf_type' => 'bpm'],
            ['tdf_name' => '295', 'tdf_type' => 'bpm'],
            ['tdf_name' => '296', 'tdf_type' => 'bpm'],
            ['tdf_name' => '297', 'tdf_type' => 'bpm'],
            ['tdf_name' => '298', 'tdf_type' => 'bpm'],
            ['tdf_name' => '299', 'tdf_type' => 'bpm'],
            ['tdf_name' => '300', 'tdf_type' => 'bpm'],
            ['tdf_name' => '301', 'tdf_type' => 'bpm'],
            ['tdf_name' => '302', 'tdf_type' => 'bpm'],
            ['tdf_name' => '303', 'tdf_type' => 'bpm'],
            ['tdf_name' => '304', 'tdf_type' => 'bpm'],
            ['tdf_name' => '305', 'tdf_type' => 'bpm'],
            ['tdf_name' => '306', 'tdf_type' => 'bpm'],
            ['tdf_name' => '307', 'tdf_type' => 'bpm'],
            ['tdf_name' => '308', 'tdf_type' => 'bpm'],
            ['tdf_name' => '309', 'tdf_type' => 'bpm'],
            ['tdf_name' => '310', 'tdf_type' => 'bpm'],
            ['tdf_name' => '311', 'tdf_type' => 'bpm'],
            ['tdf_name' => '312', 'tdf_type' => 'bpm'],
            ['tdf_name' => '313', 'tdf_type' => 'bpm'],
            ['tdf_name' => '314', 'tdf_type' => 'bpm'],
            ['tdf_name' => '315', 'tdf_type' => 'bpm'],
            ['tdf_name' => '316', 'tdf_type' => 'bpm'],
            ['tdf_name' => '317', 'tdf_type' => 'bpm'],
            ['tdf_name' => '318', 'tdf_type' => 'bpm'],
            ['tdf_name' => '319', 'tdf_type' => 'bpm'],
            ['tdf_name' => '320', 'tdf_type' => 'bpm'],
            ['tdf_name' => '321', 'tdf_type' => 'bpm'],
            ['tdf_name' => '322', 'tdf_type' => 'bpm'],
            ['tdf_name' => '323', 'tdf_type' => 'bpm'],
            ['tdf_name' => '324', 'tdf_type' => 'bpm'],
            ['tdf_name' => '325', 'tdf_type' => 'bpm'],
            ['tdf_name' => '326', 'tdf_type' => 'bpm'],
            ['tdf_name' => '327', 'tdf_type' => 'bpm'],
            ['tdf_name' => '328', 'tdf_type' => 'bpm'],
            ['tdf_name' => '329', 'tdf_type' => 'bpm'],
            ['tdf_name' => '330', 'tdf_type' => 'bpm'],
            ['tdf_name' => '331', 'tdf_type' => 'bpm'],
            ['tdf_name' => '332', 'tdf_type' => 'bpm'],
            ['tdf_name' => '333', 'tdf_type' => 'bpm'],
            ['tdf_name' => '334', 'tdf_type' => 'bpm'],
            ['tdf_name' => '335', 'tdf_type' => 'bpm'],
            ['tdf_name' => '336', 'tdf_type' => 'bpm'],
            ['tdf_name' => '337', 'tdf_type' => 'bpm'],
            ['tdf_name' => '338', 'tdf_type' => 'bpm'],
            ['tdf_name' => '339', 'tdf_type' => 'bpm'],
            ['tdf_name' => '340', 'tdf_type' => 'bpm'],
            ['tdf_name' => '341', 'tdf_type' => 'bpm'],
            ['tdf_name' => '342', 'tdf_type' => 'bpm'],
            ['tdf_name' => '343', 'tdf_type' => 'bpm'],
            ['tdf_name' => '344', 'tdf_type' => 'bpm'],
            ['tdf_name' => '345', 'tdf_type' => 'bpm'],
            ['tdf_name' => '346', 'tdf_type' => 'bpm'],
            ['tdf_name' => '347', 'tdf_type' => 'bpm'],
            ['tdf_name' => '348', 'tdf_type' => 'bpm'],
            ['tdf_name' => '349', 'tdf_type' => 'bpm'],
            ['tdf_name' => '350', 'tdf_type' => 'bpm'],
            ['tdf_name' => '351', 'tdf_type' => 'bpm'],
            ['tdf_name' => '352', 'tdf_type' => 'bpm'],
            ['tdf_name' => '353', 'tdf_type' => 'bpm'],
            ['tdf_name' => '354', 'tdf_type' => 'bpm'],
            ['tdf_name' => '355', 'tdf_type' => 'bpm'],
            ['tdf_name' => '356', 'tdf_type' => 'bpm'],
            ['tdf_name' => '357', 'tdf_type' => 'bpm'],
            ['tdf_name' => '358', 'tdf_type' => 'bpm'],
            ['tdf_name' => '359', 'tdf_type' => 'bpm'],
            ['tdf_name' => '360', 'tdf_type' => 'bpm'],
            ['tdf_name' => '361', 'tdf_type' => 'bpm'],
            ['tdf_name' => '362', 'tdf_type' => 'bpm'],
            ['tdf_name' => '363', 'tdf_type' => 'bpm'],
            ['tdf_name' => '364', 'tdf_type' => 'bpm'],
            ['tdf_name' => '365', 'tdf_type' => 'bpm'],
            ['tdf_name' => '366', 'tdf_type' => 'bpm'],
            ['tdf_name' => '367', 'tdf_type' => 'bpm'],
            ['tdf_name' => '368', 'tdf_type' => 'bpm'],
            ['tdf_name' => '369', 'tdf_type' => 'bpm'],
            ['tdf_name' => '370', 'tdf_type' => 'bpm'],
            ['tdf_name' => '371', 'tdf_type' => 'bpm'],
            ['tdf_name' => '372', 'tdf_type' => 'bpm'],
            ['tdf_name' => '373', 'tdf_type' => 'bpm'],
            ['tdf_name' => '374', 'tdf_type' => 'bpm'],
            ['tdf_name' => '375', 'tdf_type' => 'bpm'],
            ['tdf_name' => '376', 'tdf_type' => 'bpm'],
            ['tdf_name' => '377', 'tdf_type' => 'bpm'],
            ['tdf_name' => '378', 'tdf_type' => 'bpm'],
            ['tdf_name' => '379', 'tdf_type' => 'bpm'],
            ['tdf_name' => '380', 'tdf_type' => 'bpm'],
            ['tdf_name' => '381', 'tdf_type' => 'bpm'],
            ['tdf_name' => '382', 'tdf_type' => 'bpm'],
            ['tdf_name' => '383', 'tdf_type' => 'bpm'],
            ['tdf_name' => '384', 'tdf_type' => 'bpm'],
            ['tdf_name' => '385', 'tdf_type' => 'bpm'],
            ['tdf_name' => '386', 'tdf_type' => 'bpm'],
            ['tdf_name' => '387', 'tdf_type' => 'bpm'],
            ['tdf_name' => '388', 'tdf_type' => 'bpm'],
            ['tdf_name' => '389', 'tdf_type' => 'bpm'],
            ['tdf_name' => '390', 'tdf_type' => 'bpm'],
            ['tdf_name' => '391', 'tdf_type' => 'bpm'],
            ['tdf_name' => '392', 'tdf_type' => 'bpm'],
            ['tdf_name' => '393', 'tdf_type' => 'bpm'],
            ['tdf_name' => '394', 'tdf_type' => 'bpm'],
            ['tdf_name' => '395', 'tdf_type' => 'bpm'],
            ['tdf_name' => '396', 'tdf_type' => 'bpm'],
            ['tdf_name' => '397', 'tdf_type' => 'bpm'],
            ['tdf_name' => '398', 'tdf_type' => 'bpm'],
            ['tdf_name' => '399', 'tdf_type' => 'bpm'],
            ['tdf_name' => '400', 'tdf_type' => 'bpm'],
            ['tdf_name' => '401', 'tdf_type' => 'bpm'],
            ['tdf_name' => '402', 'tdf_type' => 'bpm'],
            ['tdf_name' => '403', 'tdf_type' => 'bpm'],
            ['tdf_name' => '404', 'tdf_type' => 'bpm'],
            ['tdf_name' => '405', 'tdf_type' => 'bpm'],
            ['tdf_name' => '406', 'tdf_type' => 'bpm'],
            ['tdf_name' => '407', 'tdf_type' => 'bpm'],
            ['tdf_name' => '408', 'tdf_type' => 'bpm'],
            ['tdf_name' => '409', 'tdf_type' => 'bpm'],
            ['tdf_name' => '410', 'tdf_type' => 'bpm'],
            ['tdf_name' => '411', 'tdf_type' => 'bpm'],
            ['tdf_name' => '412', 'tdf_type' => 'bpm'],
            ['tdf_name' => '413', 'tdf_type' => 'bpm'],
            ['tdf_name' => '414', 'tdf_type' => 'bpm'],
            ['tdf_name' => '415', 'tdf_type' => 'bpm'],
            ['tdf_name' => '416', 'tdf_type' => 'bpm'],
            ['tdf_name' => '417', 'tdf_type' => 'bpm'],
            ['tdf_name' => '418', 'tdf_type' => 'bpm'],
            ['tdf_name' => '419', 'tdf_type' => 'bpm'],
            ['tdf_name' => '420', 'tdf_type' => 'bpm'],
            ['tdf_name' => '421', 'tdf_type' => 'bpm'],
            ['tdf_name' => '422', 'tdf_type' => 'bpm'],
            ['tdf_name' => '423', 'tdf_type' => 'bpm'],
            ['tdf_name' => '424', 'tdf_type' => 'bpm'],
            ['tdf_name' => '425', 'tdf_type' => 'bpm'],
            ['tdf_name' => '426', 'tdf_type' => 'bpm'],
            ['tdf_name' => '427', 'tdf_type' => 'bpm'],
            ['tdf_name' => '428', 'tdf_type' => 'bpm'],
            ['tdf_name' => '429', 'tdf_type' => 'bpm'],
            ['tdf_name' => '430', 'tdf_type' => 'bpm'],
            ['tdf_name' => '431', 'tdf_type' => 'bpm'],
            ['tdf_name' => '432', 'tdf_type' => 'bpm'],
            ['tdf_name' => '433', 'tdf_type' => 'bpm'],
            ['tdf_name' => '434', 'tdf_type' => 'bpm'],
            ['tdf_name' => '435', 'tdf_type' => 'bpm'],
            ['tdf_name' => '436', 'tdf_type' => 'bpm'],
            ['tdf_name' => '437', 'tdf_type' => 'bpm'],
            ['tdf_name' => '438', 'tdf_type' => 'bpm'],
            ['tdf_name' => '439', 'tdf_type' => 'bpm'],
            ['tdf_name' => '440', 'tdf_type' => 'bpm'],
            ['tdf_name' => '441', 'tdf_type' => 'bpm'],
            ['tdf_name' => '442', 'tdf_type' => 'bpm'],
            ['tdf_name' => '443', 'tdf_type' => 'bpm'],
            ['tdf_name' => '444', 'tdf_type' => 'bpm'],
            ['tdf_name' => '445', 'tdf_type' => 'bpm'],
            ['tdf_name' => '446', 'tdf_type' => 'bpm'],
            ['tdf_name' => '447', 'tdf_type' => 'bpm'],
            ['tdf_name' => '448', 'tdf_type' => 'bpm'],
            ['tdf_name' => '449', 'tdf_type' => 'bpm'],
            ['tdf_name' => '450', 'tdf_type' => 'bpm'],
            ['tdf_name' => '451', 'tdf_type' => 'bpm'],
            ['tdf_name' => '452', 'tdf_type' => 'bpm'],
            ['tdf_name' => '453', 'tdf_type' => 'bpm'],
            ['tdf_name' => '454', 'tdf_type' => 'bpm'],
            ['tdf_name' => '455', 'tdf_type' => 'bpm'],
            ['tdf_name' => '456', 'tdf_type' => 'bpm'],
            ['tdf_name' => '457', 'tdf_type' => 'bpm'],
            ['tdf_name' => '458', 'tdf_type' => 'bpm'],
            ['tdf_name' => '459', 'tdf_type' => 'bpm'],
            ['tdf_name' => '460', 'tdf_type' => 'bpm'],
            ['tdf_name' => '461', 'tdf_type' => 'bpm'],
            ['tdf_name' => '462', 'tdf_type' => 'bpm'],
            ['tdf_name' => '463', 'tdf_type' => 'bpm'],
            ['tdf_name' => '464', 'tdf_type' => 'bpm'],
            ['tdf_name' => '465', 'tdf_type' => 'bpm'],
            ['tdf_name' => '466', 'tdf_type' => 'bpm'],
            ['tdf_name' => '467', 'tdf_type' => 'bpm'],
            ['tdf_name' => '468', 'tdf_type' => 'bpm'],
            ['tdf_name' => '469', 'tdf_type' => 'bpm'],
            ['tdf_name' => '470', 'tdf_type' => 'bpm'],
            ['tdf_name' => '471', 'tdf_type' => 'bpm'],
            ['tdf_name' => '472', 'tdf_type' => 'bpm'],
            ['tdf_name' => '473', 'tdf_type' => 'bpm'],
            ['tdf_name' => '474', 'tdf_type' => 'bpm'],
            ['tdf_name' => '475', 'tdf_type' => 'bpm'],
            ['tdf_name' => '476', 'tdf_type' => 'bpm'],
            ['tdf_name' => '477', 'tdf_type' => 'bpm'],
            ['tdf_name' => '478', 'tdf_type' => 'bpm'],
            ['tdf_name' => '479', 'tdf_type' => 'bpm'],
            ['tdf_name' => '480', 'tdf_type' => 'bpm'],
            ['tdf_name' => '481', 'tdf_type' => 'bpm'],
            ['tdf_name' => '482', 'tdf_type' => 'bpm'],
            ['tdf_name' => '483', 'tdf_type' => 'bpm'],
            ['tdf_name' => '484', 'tdf_type' => 'bpm'],
            ['tdf_name' => '485', 'tdf_type' => 'bpm'],
            ['tdf_name' => '486', 'tdf_type' => 'bpm'],
            ['tdf_name' => '487', 'tdf_type' => 'bpm'],
            ['tdf_name' => '488', 'tdf_type' => 'bpm'],
            ['tdf_name' => '489', 'tdf_type' => 'bpm'],
            ['tdf_name' => '490', 'tdf_type' => 'bpm'],
            ['tdf_name' => '491', 'tdf_type' => 'bpm'],
            ['tdf_name' => '492', 'tdf_type' => 'bpm'],
            ['tdf_name' => '493', 'tdf_type' => 'bpm'],
            ['tdf_name' => '494', 'tdf_type' => 'bpm'],
            ['tdf_name' => '495', 'tdf_type' => 'bpm'],
            ['tdf_name' => '496', 'tdf_type' => 'bpm'],
            ['tdf_name' => '497', 'tdf_type' => 'bpm'],
            ['tdf_name' => '498', 'tdf_type' => 'bpm'],
            ['tdf_name' => '499', 'tdf_type' => 'bpm'],
            ['tdf_name' => '500', 'tdf_type' => 'bpm'],
            ['tdf_name' => '501', 'tdf_type' => 'bpm'],
            ['tdf_name' => '502', 'tdf_type' => 'bpm'],
            ['tdf_name' => '503', 'tdf_type' => 'bpm'],
            ['tdf_name' => '504', 'tdf_type' => 'bpm'],
            ['tdf_name' => '505', 'tdf_type' => 'bpm'],
            ['tdf_name' => '506', 'tdf_type' => 'bpm'],
            ['tdf_name' => '507', 'tdf_type' => 'bpm'],
            ['tdf_name' => '508', 'tdf_type' => 'bpm'],
            ['tdf_name' => '509', 'tdf_type' => 'bpm'],
            ['tdf_name' => '510', 'tdf_type' => 'bpm'],
            ['tdf_name' => '511', 'tdf_type' => 'bpm'],
            ['tdf_name' => '512', 'tdf_type' => 'bpm'],
            ['tdf_name' => '513', 'tdf_type' => 'bpm'],
            ['tdf_name' => '514', 'tdf_type' => 'bpm'],
            ['tdf_name' => '515', 'tdf_type' => 'bpm'],
            ['tdf_name' => '516', 'tdf_type' => 'bpm'],
            ['tdf_name' => '517', 'tdf_type' => 'bpm'],
            ['tdf_name' => '518', 'tdf_type' => 'bpm'],
            ['tdf_name' => '519', 'tdf_type' => 'bpm'],
            ['tdf_name' => '520', 'tdf_type' => 'bpm'],
            ['tdf_name' => '521', 'tdf_type' => 'bpm'],
            ['tdf_name' => '522', 'tdf_type' => 'bpm'],
            ['tdf_name' => '523', 'tdf_type' => 'bpm'],
            ['tdf_name' => '524', 'tdf_type' => 'bpm'],
            ['tdf_name' => '525', 'tdf_type' => 'bpm'],
            ['tdf_name' => '526', 'tdf_type' => 'bpm'],
            ['tdf_name' => '527', 'tdf_type' => 'bpm'],
            ['tdf_name' => '528', 'tdf_type' => 'bpm'],
            ['tdf_name' => '529', 'tdf_type' => 'bpm'],
            ['tdf_name' => '530', 'tdf_type' => 'bpm'],
            ['tdf_name' => '531', 'tdf_type' => 'bpm'],
            ['tdf_name' => '532', 'tdf_type' => 'bpm'],
            ['tdf_name' => '533', 'tdf_type' => 'bpm'],
            ['tdf_name' => '534', 'tdf_type' => 'bpm'],
            ['tdf_name' => '535', 'tdf_type' => 'bpm'],
            ['tdf_name' => '536', 'tdf_type' => 'bpm'],
            ['tdf_name' => '537', 'tdf_type' => 'bpm'],
            ['tdf_name' => '538', 'tdf_type' => 'bpm'],
            ['tdf_name' => '539', 'tdf_type' => 'bpm'],
            ['tdf_name' => '540', 'tdf_type' => 'bpm'],
            ['tdf_name' => '541', 'tdf_type' => 'bpm'],
            ['tdf_name' => '542', 'tdf_type' => 'bpm'],
            ['tdf_name' => '543', 'tdf_type' => 'bpm'],
            ['tdf_name' => '544', 'tdf_type' => 'bpm'],
            ['tdf_name' => '545', 'tdf_type' => 'bpm'],
            ['tdf_name' => '546', 'tdf_type' => 'bpm'],
            ['tdf_name' => '547', 'tdf_type' => 'bpm'],
            ['tdf_name' => '548', 'tdf_type' => 'bpm'],
            ['tdf_name' => '549', 'tdf_type' => 'bpm'],
            ['tdf_name' => '550', 'tdf_type' => 'bpm'],
            ['tdf_name' => '551', 'tdf_type' => 'bpm'],
            ['tdf_name' => '552', 'tdf_type' => 'bpm'],
            ['tdf_name' => '553', 'tdf_type' => 'bpm'],
            ['tdf_name' => '554', 'tdf_type' => 'bpm'],
            ['tdf_name' => '555', 'tdf_type' => 'bpm'],
            ['tdf_name' => '556', 'tdf_type' => 'bpm'],
            ['tdf_name' => '557', 'tdf_type' => 'bpm'],
            ['tdf_name' => '558', 'tdf_type' => 'bpm'],
            ['tdf_name' => '559', 'tdf_type' => 'bpm'],
            ['tdf_name' => '560', 'tdf_type' => 'bpm'],
            ['tdf_name' => '561', 'tdf_type' => 'bpm'],
            ['tdf_name' => '562', 'tdf_type' => 'bpm'],
            ['tdf_name' => '563', 'tdf_type' => 'bpm'],
            ['tdf_name' => '564', 'tdf_type' => 'bpm'],
            ['tdf_name' => '565', 'tdf_type' => 'bpm'],
            ['tdf_name' => '566', 'tdf_type' => 'bpm'],
            ['tdf_name' => '567', 'tdf_type' => 'bpm'],
            ['tdf_name' => '568', 'tdf_type' => 'bpm'],
            ['tdf_name' => '569', 'tdf_type' => 'bpm'],
            ['tdf_name' => '570', 'tdf_type' => 'bpm'],
            ['tdf_name' => '571', 'tdf_type' => 'bpm'],
            ['tdf_name' => '572', 'tdf_type' => 'bpm'],
            ['tdf_name' => '573', 'tdf_type' => 'bpm'],
            ['tdf_name' => '574', 'tdf_type' => 'bpm'],
            ['tdf_name' => '575', 'tdf_type' => 'bpm'],
            ['tdf_name' => '576', 'tdf_type' => 'bpm'],
            ['tdf_name' => '577', 'tdf_type' => 'bpm'],
            ['tdf_name' => '578', 'tdf_type' => 'bpm'],
            ['tdf_name' => '579', 'tdf_type' => 'bpm'],
            ['tdf_name' => '580', 'tdf_type' => 'bpm'],
            ['tdf_name' => '581', 'tdf_type' => 'bpm'],
            ['tdf_name' => '582', 'tdf_type' => 'bpm'],
            ['tdf_name' => '583', 'tdf_type' => 'bpm'],
            ['tdf_name' => '584', 'tdf_type' => 'bpm'],
            ['tdf_name' => '585', 'tdf_type' => 'bpm'],
            ['tdf_name' => '586', 'tdf_type' => 'bpm'],
            ['tdf_name' => '587', 'tdf_type' => 'bpm'],
            ['tdf_name' => '588', 'tdf_type' => 'bpm'],
            ['tdf_name' => '589', 'tdf_type' => 'bpm'],
            ['tdf_name' => '590', 'tdf_type' => 'bpm'],
            ['tdf_name' => '591', 'tdf_type' => 'bpm'],
            ['tdf_name' => '592', 'tdf_type' => 'bpm'],
            ['tdf_name' => '593', 'tdf_type' => 'bpm'],
            ['tdf_name' => '594', 'tdf_type' => 'bpm'],
            ['tdf_name' => '595', 'tdf_type' => 'bpm'],
            ['tdf_name' => '596', 'tdf_type' => 'bpm'],
            ['tdf_name' => '597', 'tdf_type' => 'bpm'],
            ['tdf_name' => '598', 'tdf_type' => 'bpm'],
            ['tdf_name' => '599', 'tdf_type' => 'bpm'],
            ['tdf_name' => '600', 'tdf_type' => 'bpm'],
            ['tdf_name' => '601', 'tdf_type' => 'bpm'],
            ['tdf_name' => '602', 'tdf_type' => 'bpm'],
            ['tdf_name' => '603', 'tdf_type' => 'bpm'],
            ['tdf_name' => '604', 'tdf_type' => 'bpm'],
            ['tdf_name' => '605', 'tdf_type' => 'bpm'],
            ['tdf_name' => '606', 'tdf_type' => 'bpm'],
            ['tdf_name' => '607', 'tdf_type' => 'bpm'],
            ['tdf_name' => '608', 'tdf_type' => 'bpm'],
            ['tdf_name' => '609', 'tdf_type' => 'bpm'],
            ['tdf_name' => '610', 'tdf_type' => 'bpm'],
            ['tdf_name' => '611', 'tdf_type' => 'bpm'],
            ['tdf_name' => '612', 'tdf_type' => 'bpm'],
            ['tdf_name' => '613', 'tdf_type' => 'bpm'],
            ['tdf_name' => '614', 'tdf_type' => 'bpm'],
            ['tdf_name' => '615', 'tdf_type' => 'bpm'],
            ['tdf_name' => '616', 'tdf_type' => 'bpm'],
            ['tdf_name' => '617', 'tdf_type' => 'bpm'],
            ['tdf_name' => '618', 'tdf_type' => 'bpm'],
            ['tdf_name' => '619', 'tdf_type' => 'bpm'],
            ['tdf_name' => '620', 'tdf_type' => 'bpm'],
            ['tdf_name' => '621', 'tdf_type' => 'bpm'],
            ['tdf_name' => '622', 'tdf_type' => 'bpm'],
            ['tdf_name' => '623', 'tdf_type' => 'bpm'],
            ['tdf_name' => '624', 'tdf_type' => 'bpm'],
            ['tdf_name' => '625', 'tdf_type' => 'bpm'],
            ['tdf_name' => '626', 'tdf_type' => 'bpm'],
            ['tdf_name' => '627', 'tdf_type' => 'bpm'],
            ['tdf_name' => '628', 'tdf_type' => 'bpm'],
            ['tdf_name' => '629', 'tdf_type' => 'bpm'],
            ['tdf_name' => '630', 'tdf_type' => 'bpm'],
            ['tdf_name' => '631', 'tdf_type' => 'bpm'],
            ['tdf_name' => '632', 'tdf_type' => 'bpm'],
            ['tdf_name' => '633', 'tdf_type' => 'bpm'],
            ['tdf_name' => '634', 'tdf_type' => 'bpm'],
            ['tdf_name' => '635', 'tdf_type' => 'bpm'],
            ['tdf_name' => '636', 'tdf_type' => 'bpm'],
            ['tdf_name' => '637', 'tdf_type' => 'bpm'],
            ['tdf_name' => '638', 'tdf_type' => 'bpm'],
            ['tdf_name' => '639', 'tdf_type' => 'bpm'],
            ['tdf_name' => '640', 'tdf_type' => 'bpm'],
            ['tdf_name' => '641', 'tdf_type' => 'bpm'],
            ['tdf_name' => '642', 'tdf_type' => 'bpm'],
            ['tdf_name' => '643', 'tdf_type' => 'bpm'],
            ['tdf_name' => '644', 'tdf_type' => 'bpm'],
            ['tdf_name' => '645', 'tdf_type' => 'bpm'],
            ['tdf_name' => '646', 'tdf_type' => 'bpm'],
            ['tdf_name' => '647', 'tdf_type' => 'bpm'],
            ['tdf_name' => '648', 'tdf_type' => 'bpm'],
            ['tdf_name' => '649', 'tdf_type' => 'bpm'],
            ['tdf_name' => '650', 'tdf_type' => 'bpm'],
            ['tdf_name' => '651', 'tdf_type' => 'bpm'],
            ['tdf_name' => '652', 'tdf_type' => 'bpm'],
            ['tdf_name' => '653', 'tdf_type' => 'bpm'],
            ['tdf_name' => '654', 'tdf_type' => 'bpm'],
            ['tdf_name' => '655', 'tdf_type' => 'bpm'],
            ['tdf_name' => '656', 'tdf_type' => 'bpm'],
            ['tdf_name' => '657', 'tdf_type' => 'bpm'],
            ['tdf_name' => '658', 'tdf_type' => 'bpm'],
            ['tdf_name' => '659', 'tdf_type' => 'bpm'],
            ['tdf_name' => '660', 'tdf_type' => 'bpm'],
            ['tdf_name' => '661', 'tdf_type' => 'bpm'],
            ['tdf_name' => '662', 'tdf_type' => 'bpm'],
            ['tdf_name' => '663', 'tdf_type' => 'bpm'],
            ['tdf_name' => '664', 'tdf_type' => 'bpm'],
            ['tdf_name' => '665', 'tdf_type' => 'bpm'],
            ['tdf_name' => '666', 'tdf_type' => 'bpm'],
            ['tdf_name' => '667', 'tdf_type' => 'bpm'],
            ['tdf_name' => '668', 'tdf_type' => 'bpm'],
            ['tdf_name' => '669', 'tdf_type' => 'bpm'],
            ['tdf_name' => '670', 'tdf_type' => 'bpm'],
            ['tdf_name' => '671', 'tdf_type' => 'bpm'],
            ['tdf_name' => '672', 'tdf_type' => 'bpm'],
            ['tdf_name' => '673', 'tdf_type' => 'bpm'],
            ['tdf_name' => '674', 'tdf_type' => 'bpm'],
            ['tdf_name' => '675', 'tdf_type' => 'bpm'],
            ['tdf_name' => '676', 'tdf_type' => 'bpm'],
            ['tdf_name' => '677', 'tdf_type' => 'bpm'],
            ['tdf_name' => '678', 'tdf_type' => 'bpm'],
            ['tdf_name' => '679', 'tdf_type' => 'bpm'],
            ['tdf_name' => '680', 'tdf_type' => 'bpm'],
            ['tdf_name' => '681', 'tdf_type' => 'bpm'],
            ['tdf_name' => '682', 'tdf_type' => 'bpm'],
            ['tdf_name' => '683', 'tdf_type' => 'bpm'],
            ['tdf_name' => '684', 'tdf_type' => 'bpm'],
            ['tdf_name' => '685', 'tdf_type' => 'bpm'],
            ['tdf_name' => '686', 'tdf_type' => 'bpm'],
            ['tdf_name' => '687', 'tdf_type' => 'bpm'],
            ['tdf_name' => '688', 'tdf_type' => 'bpm'],
            ['tdf_name' => '689', 'tdf_type' => 'bpm'],
            ['tdf_name' => '690', 'tdf_type' => 'bpm'],
            ['tdf_name' => '691', 'tdf_type' => 'bpm'],
            ['tdf_name' => '692', 'tdf_type' => 'bpm'],
            ['tdf_name' => '693', 'tdf_type' => 'bpm'],
            ['tdf_name' => '694', 'tdf_type' => 'bpm'],
            ['tdf_name' => '695', 'tdf_type' => 'bpm'],
            ['tdf_name' => '696', 'tdf_type' => 'bpm'],
            ['tdf_name' => '697', 'tdf_type' => 'bpm'],
            ['tdf_name' => '698', 'tdf_type' => 'bpm'],
            ['tdf_name' => '699', 'tdf_type' => 'bpm'],
            ['tdf_name' => '700', 'tdf_type' => 'bpm'],
            ['tdf_name' => '701', 'tdf_type' => 'bpm'],
            ['tdf_name' => '702', 'tdf_type' => 'bpm'],
            ['tdf_name' => '703', 'tdf_type' => 'bpm'],
            ['tdf_name' => '704', 'tdf_type' => 'bpm'],
            ['tdf_name' => '705', 'tdf_type' => 'bpm'],
            ['tdf_name' => '706', 'tdf_type' => 'bpm'],
            ['tdf_name' => '707', 'tdf_type' => 'bpm'],
            ['tdf_name' => '708', 'tdf_type' => 'bpm'],
            ['tdf_name' => '709', 'tdf_type' => 'bpm'],
            ['tdf_name' => '710', 'tdf_type' => 'bpm'],
            ['tdf_name' => '711', 'tdf_type' => 'bpm'],
            ['tdf_name' => '712', 'tdf_type' => 'bpm'],
            ['tdf_name' => '713', 'tdf_type' => 'bpm'],
            ['tdf_name' => '714', 'tdf_type' => 'bpm'],
            ['tdf_name' => '715', 'tdf_type' => 'bpm'],
            ['tdf_name' => '716', 'tdf_type' => 'bpm'],
            ['tdf_name' => '717', 'tdf_type' => 'bpm'],
            ['tdf_name' => '718', 'tdf_type' => 'bpm'],
            ['tdf_name' => '719', 'tdf_type' => 'bpm'],
            ['tdf_name' => '720', 'tdf_type' => 'bpm'],
            ['tdf_name' => '721', 'tdf_type' => 'bpm'],
            ['tdf_name' => '722', 'tdf_type' => 'bpm'],
            ['tdf_name' => '723', 'tdf_type' => 'bpm'],
            ['tdf_name' => '724', 'tdf_type' => 'bpm'],
            ['tdf_name' => '725', 'tdf_type' => 'bpm'],
            ['tdf_name' => '726', 'tdf_type' => 'bpm'],
            ['tdf_name' => '727', 'tdf_type' => 'bpm'],
            ['tdf_name' => '728', 'tdf_type' => 'bpm'],
            ['tdf_name' => '729', 'tdf_type' => 'bpm'],
            ['tdf_name' => '730', 'tdf_type' => 'bpm'],
            ['tdf_name' => '731', 'tdf_type' => 'bpm'],
            ['tdf_name' => '732', 'tdf_type' => 'bpm'],
            ['tdf_name' => '733', 'tdf_type' => 'bpm'],
            ['tdf_name' => '734', 'tdf_type' => 'bpm'],
            ['tdf_name' => '735', 'tdf_type' => 'bpm'],
            ['tdf_name' => '736', 'tdf_type' => 'bpm'],
            ['tdf_name' => '737', 'tdf_type' => 'bpm'],
            ['tdf_name' => '738', 'tdf_type' => 'bpm'],
            ['tdf_name' => '739', 'tdf_type' => 'bpm'],
            ['tdf_name' => '740', 'tdf_type' => 'bpm'],
            ['tdf_name' => '741', 'tdf_type' => 'bpm'],
            ['tdf_name' => '742', 'tdf_type' => 'bpm'],
            ['tdf_name' => '743', 'tdf_type' => 'bpm'],
            ['tdf_name' => '744', 'tdf_type' => 'bpm'],
            ['tdf_name' => '745', 'tdf_type' => 'bpm'],
            ['tdf_name' => '746', 'tdf_type' => 'bpm'],
            ['tdf_name' => '747', 'tdf_type' => 'bpm'],
            ['tdf_name' => '748', 'tdf_type' => 'bpm'],
            ['tdf_name' => '749', 'tdf_type' => 'bpm'],
            ['tdf_name' => '750', 'tdf_type' => 'bpm'],
            ['tdf_name' => '751', 'tdf_type' => 'bpm'],
            ['tdf_name' => '752', 'tdf_type' => 'bpm'],
            ['tdf_name' => '753', 'tdf_type' => 'bpm'],
            ['tdf_name' => '754', 'tdf_type' => 'bpm'],
            ['tdf_name' => '755', 'tdf_type' => 'bpm'],
            ['tdf_name' => '756', 'tdf_type' => 'bpm'],
            ['tdf_name' => '757', 'tdf_type' => 'bpm'],
            ['tdf_name' => '758', 'tdf_type' => 'bpm'],
            ['tdf_name' => '759', 'tdf_type' => 'bpm'],
            ['tdf_name' => '760', 'tdf_type' => 'bpm'],
            ['tdf_name' => '761', 'tdf_type' => 'bpm'],
            ['tdf_name' => '762', 'tdf_type' => 'bpm'],
            ['tdf_name' => '763', 'tdf_type' => 'bpm'],
            ['tdf_name' => '764', 'tdf_type' => 'bpm'],
            ['tdf_name' => '765', 'tdf_type' => 'bpm'],
            ['tdf_name' => '766', 'tdf_type' => 'bpm'],
            ['tdf_name' => '767', 'tdf_type' => 'bpm'],
            ['tdf_name' => '768', 'tdf_type' => 'bpm'],
            ['tdf_name' => '769', 'tdf_type' => 'bpm'],
            ['tdf_name' => '770', 'tdf_type' => 'bpm'],
            ['tdf_name' => '771', 'tdf_type' => 'bpm'],
            ['tdf_name' => '772', 'tdf_type' => 'bpm'],
            ['tdf_name' => '773', 'tdf_type' => 'bpm'],
            ['tdf_name' => '774', 'tdf_type' => 'bpm'],
            ['tdf_name' => '775', 'tdf_type' => 'bpm'],
            ['tdf_name' => '776', 'tdf_type' => 'bpm'],
            ['tdf_name' => '777', 'tdf_type' => 'bpm'],
            ['tdf_name' => '778', 'tdf_type' => 'bpm'],
            ['tdf_name' => '779', 'tdf_type' => 'bpm'],
            ['tdf_name' => '780', 'tdf_type' => 'bpm'],
            ['tdf_name' => '781', 'tdf_type' => 'bpm'],
            ['tdf_name' => '782', 'tdf_type' => 'bpm'],
            ['tdf_name' => '783', 'tdf_type' => 'bpm'],
            ['tdf_name' => '784', 'tdf_type' => 'bpm'],
            ['tdf_name' => '785', 'tdf_type' => 'bpm'],
            ['tdf_name' => '786', 'tdf_type' => 'bpm'],
            ['tdf_name' => '787', 'tdf_type' => 'bpm'],
            ['tdf_name' => '788', 'tdf_type' => 'bpm'],
            ['tdf_name' => '789', 'tdf_type' => 'bpm'],
            ['tdf_name' => '790', 'tdf_type' => 'bpm'],
            ['tdf_name' => '791', 'tdf_type' => 'bpm'],
            ['tdf_name' => '792', 'tdf_type' => 'bpm'],
            ['tdf_name' => '793', 'tdf_type' => 'bpm'],
            ['tdf_name' => '794', 'tdf_type' => 'bpm'],
            ['tdf_name' => '795', 'tdf_type' => 'bpm'],
            ['tdf_name' => '796', 'tdf_type' => 'bpm'],
            ['tdf_name' => '797', 'tdf_type' => 'bpm'],
            ['tdf_name' => '798', 'tdf_type' => 'bpm'],
            ['tdf_name' => '799', 'tdf_type' => 'bpm'],
            ['tdf_name' => '800', 'tdf_type' => 'bpm'],
            ['tdf_name' => '801', 'tdf_type' => 'bpm'],
            ['tdf_name' => '802', 'tdf_type' => 'bpm'],
            ['tdf_name' => '803', 'tdf_type' => 'bpm'],
            ['tdf_name' => '804', 'tdf_type' => 'bpm'],
            ['tdf_name' => '805', 'tdf_type' => 'bpm'],
            ['tdf_name' => '806', 'tdf_type' => 'bpm'],
            ['tdf_name' => '807', 'tdf_type' => 'bpm'],
            ['tdf_name' => '808', 'tdf_type' => 'bpm'],
            ['tdf_name' => '809', 'tdf_type' => 'bpm'],
            ['tdf_name' => '810', 'tdf_type' => 'bpm'],
            ['tdf_name' => '811', 'tdf_type' => 'bpm'],
            ['tdf_name' => '812', 'tdf_type' => 'bpm'],
            ['tdf_name' => '813', 'tdf_type' => 'bpm'],
            ['tdf_name' => '814', 'tdf_type' => 'bpm'],
            ['tdf_name' => '815', 'tdf_type' => 'bpm'],
            ['tdf_name' => '816', 'tdf_type' => 'bpm'],
            ['tdf_name' => '817', 'tdf_type' => 'bpm'],
            ['tdf_name' => '818', 'tdf_type' => 'bpm'],
            ['tdf_name' => '819', 'tdf_type' => 'bpm'],
            ['tdf_name' => '820', 'tdf_type' => 'bpm'],
            ['tdf_name' => '821', 'tdf_type' => 'bpm'],
            ['tdf_name' => '822', 'tdf_type' => 'bpm'],
            ['tdf_name' => '823', 'tdf_type' => 'bpm'],
            ['tdf_name' => '824', 'tdf_type' => 'bpm'],
            ['tdf_name' => '825', 'tdf_type' => 'bpm'],
            ['tdf_name' => '826', 'tdf_type' => 'bpm'],
            ['tdf_name' => '827', 'tdf_type' => 'bpm'],
            ['tdf_name' => '828', 'tdf_type' => 'bpm'],
            ['tdf_name' => '829', 'tdf_type' => 'bpm'],
            ['tdf_name' => '830', 'tdf_type' => 'bpm'],
            ['tdf_name' => '831', 'tdf_type' => 'bpm'],
            ['tdf_name' => '832', 'tdf_type' => 'bpm'],
            ['tdf_name' => '833', 'tdf_type' => 'bpm'],
            ['tdf_name' => '834', 'tdf_type' => 'bpm'],
            ['tdf_name' => '835', 'tdf_type' => 'bpm'],
            ['tdf_name' => '836', 'tdf_type' => 'bpm'],
            ['tdf_name' => '837', 'tdf_type' => 'bpm'],
            ['tdf_name' => '838', 'tdf_type' => 'bpm'],
            ['tdf_name' => '839', 'tdf_type' => 'bpm'],
            ['tdf_name' => '840', 'tdf_type' => 'bpm'],
            ['tdf_name' => '841', 'tdf_type' => 'bpm'],
            ['tdf_name' => '842', 'tdf_type' => 'bpm'],
            ['tdf_name' => '843', 'tdf_type' => 'bpm'],
            ['tdf_name' => '844', 'tdf_type' => 'bpm'],
            ['tdf_name' => '845', 'tdf_type' => 'bpm'],
            ['tdf_name' => '846', 'tdf_type' => 'bpm'],
            ['tdf_name' => '847', 'tdf_type' => 'bpm'],
            ['tdf_name' => '848', 'tdf_type' => 'bpm'],
            ['tdf_name' => '849', 'tdf_type' => 'bpm'],
            ['tdf_name' => '850', 'tdf_type' => 'bpm'],
            ['tdf_name' => '851', 'tdf_type' => 'bpm'],
            ['tdf_name' => '852', 'tdf_type' => 'bpm'],
            ['tdf_name' => '853', 'tdf_type' => 'bpm'],
            ['tdf_name' => '854', 'tdf_type' => 'bpm'],
            ['tdf_name' => '855', 'tdf_type' => 'bpm'],
            ['tdf_name' => '856', 'tdf_type' => 'bpm'],
            ['tdf_name' => '857', 'tdf_type' => 'bpm'],
            ['tdf_name' => '858', 'tdf_type' => 'bpm'],
            ['tdf_name' => '859', 'tdf_type' => 'bpm'],
            ['tdf_name' => '860', 'tdf_type' => 'bpm'],
            ['tdf_name' => '861', 'tdf_type' => 'bpm'],
            ['tdf_name' => '862', 'tdf_type' => 'bpm'],
            ['tdf_name' => '863', 'tdf_type' => 'bpm'],
            ['tdf_name' => '864', 'tdf_type' => 'bpm'],
            ['tdf_name' => '865', 'tdf_type' => 'bpm'],
            ['tdf_name' => '866', 'tdf_type' => 'bpm'],
            ['tdf_name' => '867', 'tdf_type' => 'bpm'],
            ['tdf_name' => '868', 'tdf_type' => 'bpm'],
            ['tdf_name' => '869', 'tdf_type' => 'bpm'],
            ['tdf_name' => '870', 'tdf_type' => 'bpm'],
            ['tdf_name' => '871', 'tdf_type' => 'bpm'],
            ['tdf_name' => '872', 'tdf_type' => 'bpm'],
            ['tdf_name' => '873', 'tdf_type' => 'bpm'],
            ['tdf_name' => '874', 'tdf_type' => 'bpm'],
            ['tdf_name' => '875', 'tdf_type' => 'bpm'],
            ['tdf_name' => '876', 'tdf_type' => 'bpm'],
            ['tdf_name' => '877', 'tdf_type' => 'bpm'],
            ['tdf_name' => '878', 'tdf_type' => 'bpm'],
            ['tdf_name' => '879', 'tdf_type' => 'bpm'],
            ['tdf_name' => '880', 'tdf_type' => 'bpm'],
            ['tdf_name' => '881', 'tdf_type' => 'bpm'],
            ['tdf_name' => '882', 'tdf_type' => 'bpm'],
            ['tdf_name' => '883', 'tdf_type' => 'bpm'],
            ['tdf_name' => '884', 'tdf_type' => 'bpm'],
            ['tdf_name' => '885', 'tdf_type' => 'bpm'],
            ['tdf_name' => '886', 'tdf_type' => 'bpm'],
            ['tdf_name' => '887', 'tdf_type' => 'bpm'],
            ['tdf_name' => '888', 'tdf_type' => 'bpm'],
            ['tdf_name' => '889', 'tdf_type' => 'bpm'],
            ['tdf_name' => '890', 'tdf_type' => 'bpm'],
            ['tdf_name' => '891', 'tdf_type' => 'bpm'],
            ['tdf_name' => '892', 'tdf_type' => 'bpm'],
            ['tdf_name' => '893', 'tdf_type' => 'bpm'],
            ['tdf_name' => '894', 'tdf_type' => 'bpm'],
            ['tdf_name' => '895', 'tdf_type' => 'bpm'],
            ['tdf_name' => '896', 'tdf_type' => 'bpm'],
            ['tdf_name' => '897', 'tdf_type' => 'bpm'],
            ['tdf_name' => '898', 'tdf_type' => 'bpm'],
            ['tdf_name' => '899', 'tdf_type' => 'bpm'],
            ['tdf_name' => '900', 'tdf_type' => 'bpm'],
            ['tdf_name' => '901', 'tdf_type' => 'bpm'],
            ['tdf_name' => '902', 'tdf_type' => 'bpm'],
            ['tdf_name' => '903', 'tdf_type' => 'bpm'],
            ['tdf_name' => '904', 'tdf_type' => 'bpm'],
            ['tdf_name' => '905', 'tdf_type' => 'bpm'],
            ['tdf_name' => '906', 'tdf_type' => 'bpm'],
            ['tdf_name' => '907', 'tdf_type' => 'bpm'],
            ['tdf_name' => '908', 'tdf_type' => 'bpm'],
            ['tdf_name' => '909', 'tdf_type' => 'bpm'],
            ['tdf_name' => '910', 'tdf_type' => 'bpm'],
            ['tdf_name' => '911', 'tdf_type' => 'bpm'],
            ['tdf_name' => '912', 'tdf_type' => 'bpm'],
            ['tdf_name' => '913', 'tdf_type' => 'bpm'],
            ['tdf_name' => '914', 'tdf_type' => 'bpm'],
            ['tdf_name' => '915', 'tdf_type' => 'bpm'],
            ['tdf_name' => '916', 'tdf_type' => 'bpm'],
            ['tdf_name' => '917', 'tdf_type' => 'bpm'],
            ['tdf_name' => '918', 'tdf_type' => 'bpm'],
            ['tdf_name' => '919', 'tdf_type' => 'bpm'],
            ['tdf_name' => '920', 'tdf_type' => 'bpm'],
            ['tdf_name' => '921', 'tdf_type' => 'bpm'],
            ['tdf_name' => '922', 'tdf_type' => 'bpm'],
            ['tdf_name' => '923', 'tdf_type' => 'bpm'],
            ['tdf_name' => '924', 'tdf_type' => 'bpm'],
            ['tdf_name' => '925', 'tdf_type' => 'bpm'],
            ['tdf_name' => '926', 'tdf_type' => 'bpm'],
            ['tdf_name' => '927', 'tdf_type' => 'bpm'],
            ['tdf_name' => '928', 'tdf_type' => 'bpm'],
            ['tdf_name' => '929', 'tdf_type' => 'bpm'],
            ['tdf_name' => '930', 'tdf_type' => 'bpm'],
            ['tdf_name' => '931', 'tdf_type' => 'bpm'],
            ['tdf_name' => '932', 'tdf_type' => 'bpm'],
            ['tdf_name' => '933', 'tdf_type' => 'bpm'],
            ['tdf_name' => '934', 'tdf_type' => 'bpm'],
            ['tdf_name' => '935', 'tdf_type' => 'bpm'],
            ['tdf_name' => '936', 'tdf_type' => 'bpm'],
            ['tdf_name' => '937', 'tdf_type' => 'bpm'],
            ['tdf_name' => '938', 'tdf_type' => 'bpm'],
            ['tdf_name' => '939', 'tdf_type' => 'bpm'],
            ['tdf_name' => '940', 'tdf_type' => 'bpm'],
            ['tdf_name' => '941', 'tdf_type' => 'bpm'],
            ['tdf_name' => '942', 'tdf_type' => 'bpm'],
            ['tdf_name' => '943', 'tdf_type' => 'bpm'],
            ['tdf_name' => '944', 'tdf_type' => 'bpm'],
            ['tdf_name' => '945', 'tdf_type' => 'bpm'],
            ['tdf_name' => '946', 'tdf_type' => 'bpm'],
            ['tdf_name' => '947', 'tdf_type' => 'bpm'],
            ['tdf_name' => '948', 'tdf_type' => 'bpm'],
            ['tdf_name' => '949', 'tdf_type' => 'bpm'],
            ['tdf_name' => '950', 'tdf_type' => 'bpm'],
            ['tdf_name' => '951', 'tdf_type' => 'bpm'],
            ['tdf_name' => '952', 'tdf_type' => 'bpm'],
            ['tdf_name' => '953', 'tdf_type' => 'bpm'],
            ['tdf_name' => '954', 'tdf_type' => 'bpm'],
            ['tdf_name' => '955', 'tdf_type' => 'bpm'],
            ['tdf_name' => '956', 'tdf_type' => 'bpm'],
            ['tdf_name' => '957', 'tdf_type' => 'bpm'],
            ['tdf_name' => '958', 'tdf_type' => 'bpm'],
            ['tdf_name' => '959', 'tdf_type' => 'bpm'],
            ['tdf_name' => '960', 'tdf_type' => 'bpm'],
            ['tdf_name' => '961', 'tdf_type' => 'bpm'],
            ['tdf_name' => '962', 'tdf_type' => 'bpm'],
            ['tdf_name' => '963', 'tdf_type' => 'bpm'],
            ['tdf_name' => '964', 'tdf_type' => 'bpm'],
            ['tdf_name' => '965', 'tdf_type' => 'bpm'],
            ['tdf_name' => '966', 'tdf_type' => 'bpm'],
            ['tdf_name' => '967', 'tdf_type' => 'bpm'],
            ['tdf_name' => '968', 'tdf_type' => 'bpm'],
            ['tdf_name' => '969', 'tdf_type' => 'bpm'],
            ['tdf_name' => '970', 'tdf_type' => 'bpm'],
            ['tdf_name' => '971', 'tdf_type' => 'bpm'],
            ['tdf_name' => '972', 'tdf_type' => 'bpm'],
            ['tdf_name' => '973', 'tdf_type' => 'bpm'],
            ['tdf_name' => '974', 'tdf_type' => 'bpm'],
            ['tdf_name' => '975', 'tdf_type' => 'bpm'],
            ['tdf_name' => '976', 'tdf_type' => 'bpm'],
            ['tdf_name' => '977', 'tdf_type' => 'bpm'],
            ['tdf_name' => '978', 'tdf_type' => 'bpm'],
            ['tdf_name' => '979', 'tdf_type' => 'bpm'],
            ['tdf_name' => '980', 'tdf_type' => 'bpm'],
            ['tdf_name' => '981', 'tdf_type' => 'bpm'],
            ['tdf_name' => '982', 'tdf_type' => 'bpm'],
            ['tdf_name' => '983', 'tdf_type' => 'bpm'],
            ['tdf_name' => '984', 'tdf_type' => 'bpm'],
            ['tdf_name' => '985', 'tdf_type' => 'bpm'],
            ['tdf_name' => '986', 'tdf_type' => 'bpm'],
            ['tdf_name' => '987', 'tdf_type' => 'bpm'],
            ['tdf_name' => '988', 'tdf_type' => 'bpm'],
            ['tdf_name' => '989', 'tdf_type' => 'bpm'],
            ['tdf_name' => '990', 'tdf_type' => 'bpm'],
            ['tdf_name' => '991', 'tdf_type' => 'bpm'],
            ['tdf_name' => '992', 'tdf_type' => 'bpm'],
            ['tdf_name' => '993', 'tdf_type' => 'bpm'],
            ['tdf_name' => '994', 'tdf_type' => 'bpm'],
            ['tdf_name' => '995', 'tdf_type' => 'bpm'],
            ['tdf_name' => '996', 'tdf_type' => 'bpm'],
            ['tdf_name' => '997', 'tdf_type' => 'bpm'],
            ['tdf_name' => '998', 'tdf_type' => 'bpm'],
            ['tdf_name' => '999', 'tdf_type' => 'bpm'],

            ['tdf_name' => 'Accordion', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Acoustic Bass', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Acoustic Grand Piano', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Acoustic Guitar (nylon)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Acoustic Guitar (steel)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Agogo', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Alto Sax', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Applause', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Bagpipe', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Banjo', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Baritone Sax', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Bass', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Bassoon', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Bird Tweet', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Blown Bottle', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Bongos', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Brass Section', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Breath Noise', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Bright Acoustic Piano', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Celesta', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Cello', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Choir Aahs', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Church Organ', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Clarinet', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Clavinet', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Congas', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Contrabass', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Cymbals', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Distortion Guitar', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Drawbar Organ', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Drums', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Dulcimer', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Electric Bass (finger)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Electric Bass (pick)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Electric Grand Piano', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Electric Guitar (clean)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Electric Guitar (jazz)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Electric Guitar (muted)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Electric Piano 1', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Electric Piano 2', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'English Horn', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'FX 1 (rain)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'FX 2 (soundtrack)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'FX 3 (crystal)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'FX 4 (atmosphere)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'FX 5 (brightness)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'FX 6 (goblins)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'FX 7 (echoes)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'FX 8 (sci-fi)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Fiddle', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Flute', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'French Horn', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Fretless Bass', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Glockenspiel', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Guitar Fret Noise', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Guitar Harmonics', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Guitar', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Gunshot', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Harmonica', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Harp', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Harpsichord', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Helicopter', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Honky-tonk Piano', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Kalimba', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Koto', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Lead 1 (square)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Lead 2 (sawtooth)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Lead 3 (calliope)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Lead 4 (chiff)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Lead 5 (charang)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Lead 6(voice)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Lead 7 (fifths)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Lead 8 (bass + lead)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Mandolin', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Maracas', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Marimba', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Melodic Tom', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Music Box', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Muted Trumpet', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Oboe', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Ocarina', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Orchestra Hit', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Orchestral Harp', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Overdriven Guitar', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Pad 1 (new age)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Pad 2 (warm)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Pad 3 (polysynth)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Pad 4 (choir)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Pad 5 (bowed)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Pad 6 (metallic)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Pad 7 (halo)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Pad 8 (sweep)', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Pan Flute', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Percussive Organ', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Piano', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Piccolo', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Pizzicato Strings', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Recorder', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Reed Organ', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Reverse Cymbal', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Rock Organ', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Saxophone', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Seashore', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Shakuhachi', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Shamisen', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Shanai', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Sitar', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Slap Bass 1', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Slap Bass 2', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Snare drum', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Soprano Sax', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Steel Drums', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Steel drums', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'String Ensemble 1', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'String Ensemble 2', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Synth Bass 1', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Synth Bass 2', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Synth Brass 1', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Synth Brass 2', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Synth Drum', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Synth Strings 1', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Synth Strings 2', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Synth Voice', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Tabla', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Taiko Drum', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Tango Accordion', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Telephone Ring', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Tenor Sax', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Timpani', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Tinkle Bell', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Tremolo Strings', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Triangle', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Trombone', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Trumpet', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Tuba', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Tubular Bells', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Vibraphone', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Viola', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Violin', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Vocals', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Voice Oohs', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Whistle', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Woodblock', 'tdf_type' => 'instrument'],
            ['tdf_name' => 'Xylophone', 'tdf_type' => 'instrument'],

            ['tdf_name' => 'No Mood', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Atmospheric', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Dark', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Dreamy', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Emotional', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Energetic', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Exotic', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Funky', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Happy', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Hopeful', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Hypnotic', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Intense', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Intimate', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Melancholic', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Mellow', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Mysterious', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Nostalgic', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Passionate', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Peaceful', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Playful', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Reflective', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Relaxed', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Raw', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Sad', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Sensual', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Soulful', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Triumphant', 'tdf_type' => 'mood'],
            ['tdf_name' => 'Uplifting', 'tdf_type' => 'mood'],

            ['tdf_name' => 'A', 'tdf_type' => 'key'],
            ['tdf_name' => 'Am', 'tdf_type' => 'key'],
            ['tdf_name' => 'Bb', 'tdf_type' => 'key'],
            ['tdf_name' => 'Bm', 'tdf_type' => 'key'],
            ['tdf_name' => 'C', 'tdf_type' => 'key'],
            ['tdf_name' => 'Cm', 'tdf_type' => 'key'],
            ['tdf_name' => 'C#', 'tdf_type' => 'key'],
            ['tdf_name' => 'C#m', 'tdf_type' => 'key'],
            ['tdf_name' => 'D', 'tdf_type' => 'key'],
            ['tdf_name' => 'Dm', 'tdf_type' => 'key'],
            ['tdf_name' => 'Eb', 'tdf_type' => 'key'],
            ['tdf_name' => 'Ebm', 'tdf_type' => 'key'],
            ['tdf_name' => 'E', 'tdf_type' => 'key'],
            ['tdf_name' => 'Em', 'tdf_type' => 'key'],
            ['tdf_name' => 'F', 'tdf_type' => 'key'],
            ['tdf_name' => 'Fm', 'tdf_type' => 'key'],
            ['tdf_name' => 'F#', 'tdf_type' => 'key'],
            ['tdf_name' => 'F#m', 'tdf_type' => 'key'],
            ['tdf_name' => 'G', 'tdf_type' => 'key'],
            ['tdf_name' => 'Gm', 'tdf_type' => 'key'],
            ['tdf_name' => 'G#', 'tdf_type' => 'key'],
            ['tdf_name' => 'G#m', 'tdf_type' => 'key'],

            ['tdf_name' => 'Male', 'tdf_type' => 'acapellaGender'],
            ['tdf_name' => 'Female', 'tdf_type' => 'acapellaGender'],
            ['tdf_name' => 'Male & Female', 'tdf_type' => 'acapellaGender'],
            ['tdf_name' => 'Male & Female', 'tdf_type' => 'acapellaGender'],

            ['tdf_name' => 'Accapella', 'tdf_type' => 'acapellaVocalStyle'],
            ['tdf_name' => 'Adlib', 'tdf_type' => 'acapellaVocalStyle'],
            ['tdf_name' => 'Harmonies', 'tdf_type' => 'acapellaVocalStyle'],
            ['tdf_name' => 'Melody', 'tdf_type' => 'acapellaVocalStyle'],
            ['tdf_name' => 'Rap', 'tdf_type' => 'acapellaVocalStyle'],
            ['tdf_name' => 'Spoken word', 'tdf_type' => 'acapellaVocalStyle'],
            ['tdf_name' => 'Vocal chop', 'tdf_type' => 'acapellaVocalStyle'],
            ['tdf_name' => 'Vocal effect', 'tdf_type' => 'acapellaVocalStyle'],
            ['tdf_name' => 'Vocal harmony', 'tdf_type' => 'acapellaVocalStyle'],
            ['tdf_name' => 'Vocal loop', 'tdf_type' => 'acapellaVocalStyle'],
            ['tdf_name' => 'Vocal one-shot', 'tdf_type' => 'acapellaVocalStyle'],
            ['tdf_name' => 'Vocal sample', 'tdf_type' => 'acapellaVocalStyle'],
            ['tdf_name' => 'Vocal sound effect', 'tdf_type' => 'acapellaVocalStyle'],
            ['tdf_name' => 'Whispering', 'tdf_type' => 'acapellaVocalStyle'],

            ['tdf_name' => 'Angry', 'tdf_type' => 'acapellaEmotion'],
            ['tdf_name' => 'Sad', 'tdf_type' => 'acapellaEmotion'],
            ['tdf_name' => 'Happy', 'tdf_type' => 'acapellaEmotion'],
            ['tdf_name' => 'Emotional', 'tdf_type' => 'acapellaEmotion'],
            ['tdf_name' => 'Passionate', 'tdf_type' => 'acapellaEmotion'],
            ['tdf_name' => 'Soulful', 'tdf_type' => 'acapellaEmotion'],
            ['tdf_name' => 'Intense', 'tdf_type' => 'acapellaEmotion'],
            ['tdf_name' => 'Playful', 'tdf_type' => 'acapellaEmotion'],
            ['tdf_name' => 'Melancholic', 'tdf_type' => 'acapellaEmotion'],
            ['tdf_name' => 'Nostalgic', 'tdf_type' => 'acapellaEmotion'],
            ['tdf_name' => 'Hypnotic', 'tdf_type' => 'acapellaEmotion'],
            ['tdf_name' => 'Mysterious', 'tdf_type' => 'acapellaEmotion'],
            ['tdf_name' => 'Mellow', 'tdf_type' => 'acapellaEmotion'],
            ['tdf_name' => 'Relaxed', 'tdf_type' => 'acapellaEmotion'],
            ['tdf_name' => 'Reflective', 'tdf_type' => 'acapellaEmotion'],

            ['tdf_name' => 'Alto', 'tdf_type' => 'acapellaScale'],
            ['tdf_name' => 'Baritone', 'tdf_type' => 'acapellaScale'],
            ['tdf_name' => 'Bass', 'tdf_type' => 'acapellaScale'],
            ['tdf_name' => 'Countertenor', 'tdf_type' => 'acapellaScale'],
            ['tdf_name' => 'Mezzo-soprano', 'tdf_type' => 'acapellaScale'],
            ['tdf_name' => 'Soprano', 'tdf_type' => 'acapellaScale'],
            ['tdf_name' => 'Tenor', 'tdf_type' => 'acapellaScale'],

            ['tdf_name' => 'No Autotune', 'tdf_type' => 'acapellaEffects'],
            ['tdf_name' => 'Autotune', 'tdf_type' => 'acapellaEffects'],
            ['tdf_name' => 'Chorus', 'tdf_type' => 'acapellaEffects'],
            ['tdf_name' => 'Delay', 'tdf_type' => 'acapellaEffects'],
            ['tdf_name' => 'Echo', 'tdf_type' => 'acapellaEffects'],
            ['tdf_name' => 'Flanger', 'tdf_type' => 'acapellaEffects'],
            ['tdf_name' => 'Harmony', 'tdf_type' => 'acapellaEffects'],
            ['tdf_name' => 'Phaser', 'tdf_type' => 'acapellaEffects'],
            ['tdf_name' => 'Reverb', 'tdf_type' => 'acapellaEffects'],
            ['tdf_name' => 'Vibrato', 'tdf_type' => 'acapellaEffects'],
            ['tdf_name' => 'Distortion', 'tdf_type' => 'acapellaEffects'],
            ['tdf_name' => 'Pitch shift', 'tdf_type' => 'acapellaEffects'],
            ['tdf_name' => 'Compression', 'tdf_type' => 'acapellaEffects'],
            ['tdf_name' => 'EQ', 'tdf_type' => 'acapellaEffects'],
            ['tdf_name' => 'Filtering', 'tdf_type' => 'acapellaEffects'],
            ['tdf_name' => 'Volume', 'tdf_type' => 'acapellaEffects'],
            ['tdf_name' => 'Wah-wah', 'tdf_type' => 'acapellaEffects'],

            ['tdf_name' => 'Bass', 'tdf_type' => 'samplePackType'],
            ['tdf_name' => 'Construction kit', 'tdf_type' => 'samplePackType'],
            ['tdf_name' => 'Drum', 'tdf_type' => 'samplePackType'],
            ['tdf_name' => 'FX', 'tdf_type' => 'samplePackType'],
            ['tdf_name' => 'Full track', 'tdf_type' => 'samplePackType'],
            ['tdf_name' => 'Guitar', 'tdf_type' => 'samplePackType'],
            ['tdf_name' => 'Live instrument', 'tdf_type' => 'samplePackType'],
            ['tdf_name' => 'Loop', 'tdf_type' => 'samplePackType'],
            ['tdf_name' => 'Midi', 'tdf_type' => 'samplePackType'],
            ['tdf_name' => 'One-shots', 'tdf_type' => 'samplePackType'],
            ['tdf_name' => 'Piano', 'tdf_type' => 'samplePackType'],
            ['tdf_name' => 'Percussion', 'tdf_type' => 'samplePackType'],
            ['tdf_name' => 'Preset', 'tdf_type' => 'samplePackType'],
            ['tdf_name' => 'Sample', 'tdf_type' => 'samplePackType'],
            ['tdf_name' => 'Synth', 'tdf_type' => 'samplePackType'],
            ['tdf_name' => 'Vocal', 'tdf_type' => 'samplePackType'],
        ];
    }

    /**
     * @return OnTrackDefaultField|null
     */
    public function getOnTrackDefaultField(): ?OnTrackDefaultField
    {
        return $this->onTrackDefaultField;
    }

    /**
     * @param OnTrackDefaultField|null $onTrackDefaultField
     */
    public function setOnTrackDefaultField(?OnTrackDefaultField $onTrackDefaultField): void
    {
        $this->onTrackDefaultField = $onTrackDefaultField;
    }

    /**
     * @return FieldData|null
     */
    public function getFieldData(): ?FieldData
    {
        return $this->fieldData;
    }

    /**
     * @param FieldData|null $fieldData
     */
    public function setFieldData(?FieldData $fieldData): void
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @return OnTrackCategoryDefaultField|null
     */
    public function getOnTrackCategoryDefaultField(): ?OnTrackCategoryDefaultField
    {
        return $this->onTrackCategoryDefaultField;
    }

    /**
     * @param OnTrackCategoryDefaultField|null $onTrackCategoryDefaultField
     */
    public function setOnTrackCategoryDefaultField(?OnTrackCategoryDefaultField $onTrackCategoryDefaultField): void
    {
        $this->onTrackCategoryDefaultField = $onTrackCategoryDefaultField;
    }

}