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
            ->From(self::getTrackCategoryTable())->WhereEquals('track_cat_slug', 'default-coupon')
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