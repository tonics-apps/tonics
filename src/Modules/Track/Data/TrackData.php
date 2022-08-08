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
use App\Modules\Track\Events\OnTrackCreate;

class TrackData extends AbstractDataLayer
{
    use UniqueSlug;

    public function getArtistTable(): string
    {
        return Tables::getTable(Tables::ARTISTS);
    }

    public function getGenreTable(): string
    {
        return Tables::getTable(Tables::GENRES);
    }

    public function getTrackTable(): string
    {
        return Tables::getTable(Tables::TRACKS);
    }

    public function getLicenseTable(): string
    {
        return Tables::getTable(Tables::LICENSES);
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
        return ['track_id', 'slug_id', 'track_slug', 'image_url', 'audio_url', 'license_attr_id_link', 'track_title', 'track_plays', 'track_bpm',
            'field_ids', 'field_settings', 'track_status', 'fk_genre_id', 'fk_artist_id', 'fk_license_id', 'created_at', 'updated_at'];
    }

    public function getAllTrackPageColumns(): string
    {
        $trackTable = Tables::getTable(Tables::TRACKS);
        $artistTable = Tables::getTable(Tables::ARTISTS);

        return "
        `track_id`, `slug_id` AS `track_slug`, `track_slug`, `track_title`, `track_status`, `track_status`, track_plays, track_bpm `field_settings`,
        $trackTable.image_url AS `track_image`, `audio_url` AS `track_audio`, 
        `genre_id`, `genre_name`, `genre_name` AS `track_genre`, `genre_slug`,
        `artist_id`, `artist_name`, `artist_name` AS `track_artist`, `artist_slug`, `artist_bio`, $artistTable.image_url AS `artist_image`,
        license_id, license_name, license_slug, license_status, license_attr, license_attr AS track_licenses,
        CONCAT_WS( '/', '/tracks', slug_id, track_slug ) AS `track_link`,
        CONCAT_WS( '/', '/artist', artist_slug ) AS `track_artist_link`,
        CONCAT_WS( '/', '/genres', genre_slug ) AS `track_genre_link`
        ";
    }

    /**
     * @return string
     */
    public function getTrackPaginationColumns(): string
    {
        return '`track_id`, `slug_id`, `track_slug`, `track_title`, `track_status`,
        CONCAT_WS( "/", "tracks", slug_id, track_slug ) AS `_link`, `track_title` AS `_name`, `track_id` AS `_id`';
    }

    public function getGenrePaginationColumn(): string
    {
        return '`genre_id`, `genre_name`, `genre_slug`, `genre_description`, `created_at`, `updated_at`,
        CONCAT_WS( "", "/genre/", genre_slug ) AS `_link`, `genre_name` AS `_name`, `genre_id` AS `_id`';
    }

    public function getTrackStatusHTMLFrag($currentStatus = null): string
    {
        $frag = "<option value='0'".  ($currentStatus === 0 ? 'selected' : '') . ">Draft</option>";
        $frag .= "<option value='1'".  ($currentStatus === 1 ? 'selected' : '') . ">Publish</option>";

        return $frag;
    }

    public function getTrackColumnsForAdminCreate(): array
    {
        $trackTable = Tables::getTable(Tables::TRACKS);
        $licenseTable = Tables::getTable(Tables::LICENSES);

        return [
            "track_id", "slug_id", "track_slug", 'audio_url', 'track_title', 'track_status', 'field_ids', 'field_settings',
            "$trackTable.image_url",'license_attr_id_link','fk_genre_id', 'fk_artist_id', 'fk_license_id',
            "$trackTable.created_at", "$trackTable.updated_at", "$licenseTable.license_attr",
            "genre_name", "genre_slug",
        ];
    }

    /**
     * Usage:
     * <br>
     * `$newUserData->selectWithCondition(['track_id', 'track_content'], "slug_id = ?", ['5475353']));`
     *
     * Note: Make sure you use a question-mark(?) in place u want a user input and pass the actual input in the $parameter
     * @param array $colToSelect
     * To select all, use ['*']
     * @param string $whereCondition
     * @param array $parameter
     * @return mixed
     * @throws \Exception
     */
    public function selectWithConditionFromTrack(array $colToSelect, string $whereCondition, array $parameter): mixed
    {
        $select = helper()->returnColumnsSeparatedByCommas($colToSelect);
        $trackTable = Tables::getTable(Tables::TRACKS);
        $artistTable = Tables::getTable(Tables::ARTISTS);
        $licenseTable = Tables::getTable(Tables::LICENSES);
        $genreTable = Tables::getTable(Tables::GENRES);


        if ($colToSelect === ['*']){
            return db()->row(<<<SQL
SELECT * FROM $trackTable
    JOIN $genreTable ON fk_genre_id = genre_id
    JOIN $licenseTable  ON license_id = fk_license_id 
    JOIN $artistTable ON artist_id = fk_artist_id
WHERE $whereCondition
SQL, ...$parameter);
        }

        return db()->row(<<<SQL
SELECT $select FROM $trackTable
    JOIN $genreTable ON fk_genre_id = genre_id
    JOIN $licenseTable  ON license_id = fk_license_id 
    JOIN $artistTable ON artist_id = fk_artist_id
WHERE $whereCondition
SQL, ...$parameter);

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
        $table = $this->getArtistTable();
        $artists = db()->run("SELECT * FROM $table");
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
     * @param $genres
     * @param bool $showSearch
     * @param OnTrackCreate|null $onTrackCreate
     * @param string $inputName
     * @return string
     */
    public function genreCheckBoxListing($genres, bool $showSearch = true, OnTrackCreate $onTrackCreate = null, string $inputName = 'fk_genre_id', string $type = 'radio'): string
    {
        $htmlFrag = ''; $htmlMoreFrag = ''; $type = ($type !== 'radio') ? 'checkbox' : 'radio';
        # RADIO-BOX
        if(isset($genres->data) && is_array($genres->data) && !empty($genres->data)){
            if ($showSearch){
                $htmlFrag =<<<HTML
<input id="genre-search" style="margin-bottom: 1em;"
 data-action ="search" 
 data-query="{$genres->path}&genre_query="
 data-menuboxname = "genre"
 data-searchvalue =""
 class="menu-box-item-search position:sticky top:0" type="search" aria-label="Search Genre and Hit Enter" placeholder="Search Genre &amp; Hit Enter">
HTML;
            }

            $checkedGenreID = null;

            # SELECTED GENRE_ID IF WE HAVE ONE
            if ($onTrackCreate instanceof OnTrackCreate){
                $checkedGenreID = $onTrackCreate->getTrackFKGenreID();
                $id = 'genre'. $onTrackCreate->getTrackFKGenreID() . '_' . $onTrackCreate->getTrackGenreSlug();
                $htmlFrag .= <<<HTML
<li class="menu-item">
    <input type="$type"
    id="$id" checked="checked" name="$inputName" value="{$onTrackCreate->getTrackFKGenreID()}">
    <label for="$id">{$onTrackCreate->getTrackGenreName()}</label>
</li>
HTML;
            }

            foreach ($genres->data as $genre){
                $id = 'genre'. $genre->genre_id . '_' . $genre->genre_slug;

                if ($onTrackCreate !== null && $checkedGenreID === $genre->genre_id){
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

            # MORE BUTTON
            if(isset($genres->has_more) && $genres->has_more){
                $htmlMoreFrag = <<<HTML
 <button 
 type="button"
 data-morepageUrl="$genres->next_page_url" 
 data-menuboxname = "genre"
 data-nextpageid="$genres->next_page"
 data-action = "more"
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
        $table = $this->getLicenseTable();
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


    /**
     * @param $licenses
     * @return string
     * @throws \Exception
     */
    public function adminLicenseListing($licenses): string
    {
        $csrfToken = session()->getCSRFToken();
        $htmlFrag = ''; $urlPrefix = "/admin/tools/license";
        foreach ($licenses as $k => $license) {
            $htmlFrag .= <<<HTML
    <li 
    data-list_id="$k" data-id="$license->license_id"  
    data-license_id="$license->license_id" 
    data-license_slug="$license->license_slug" 
    data-license_name="$license->license_name"
    data-db_click_link="$urlPrefix/$license->license_slug/edit"
    tabindex="0" 
    class="admin-widget-item-for-listing d:flex flex-d:column align-items:center justify-content:center cursor:pointer no-text-highlight">
        <fieldset class="padding:default width:100% box-shadow-variant-1 d:flex justify-content:center">
            <legend class="bg:pure-black color:white padding:default">$license->license_name</legend>
            <div class="admin-widget-information owl width:100%">
            <div class="text-on-admin-util text-highlight">$license->license_name</div>
         
                <div class="form-group d:flex flex-gap:small">
                     <a href="$urlPrefix/$license->license_slug/edit" class="listing-button text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Edit</a>
                        
                         <a href="$urlPrefix/items/$license->license_slug/builder" class="listing-button text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Builder</a>
                   
                   <form method="post" class="d:contents" action="$urlPrefix/$license->license_slug/delete">
                    <input type="hidden" name="token" value="$csrfToken" >
                       <button data-click-onconfirmdelete="true" type="button" class="listing-button bg:pure-black color:white border:none border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Delete</button>
                    </form>
                </div>
                
            </div>
        </fieldset>
    </li>
HTML;
        }

        return $htmlFrag;
    }

    /**
     * @param $tracks
     * @param int|null $status
     * @return string
     * @throws \Exception
     */
    public function adminTrackListing($tracks, int|null $status = 1): string
    {
        $csrfToken = session()->getCSRFToken();
        $htmlFrag = ''; $urlPrefix = "/admin/tracks";
        foreach ($tracks as $k => $track) {
            if ($track->track_status === $status || $status === null) {
                if ($track->track_status === -1) {
                    $otherFrag = <<<HTML
<form method="post" class="d:contents" action="$urlPrefix/$track->track_slug/delete">
   <input type="hidden" name="token" value="$csrfToken">
       <button data-click-onconfirmdelete="true" type="button" class="listing-button bg:pure-black color:white border:none border-width:default border:black padding:gentle
        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Delete
        </button>
</form>
HTML;
                } else {
                    $otherFrag = <<<HTML
<form method="post" class="d:contents" action="$urlPrefix/$track->track_slug/trash">
   <input type="hidden" name="token" value="$csrfToken" >
       <button data-click-onconfirmtrash="true" type="button" class="listing-button bg:pure-black color:white border:none border-width:default border:black padding:gentle
        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Trash
        </button>
</form>
HTML;
                }
                $htmlFrag .= <<<HTML
    <li 
    data-list_id="$k" data-id="$track->track_id"  
    data-track_id="$track->track_id" 
    data-track_slug="$track->track_slug" 
    data-track_title="$track->track_title"
    data-fk_genre_id="$track->fk_genre_id"
    data-fk_artist_id="$track->fk_artist_id"
    data-fk_license_id="$track->fk_license_id"
    data-db_click_link="$urlPrefix/$track->track_slug/edit"
    tabindex="0" 
    class="admin-widget-item-for-listing d:flex flex-d:column align-items:center justify-content:center cursor:pointer no-text-highlight">
        <fieldset class="padding:default width:100% box-shadow-variant-1 d:flex justify-content:center">
            <legend class="bg:pure-black color:white padding:default">$track->track_title</legend>
            <div class="admin-widget-information owl width:100%">
            <div class="text-on-admin-util text-highlight">$track->track_title</div>
         
                <div class="form-group d:flex flex-gap:small">
                     <a href="$urlPrefix/$track->track_slug/edit" class="listing-button text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Edit</a>

                   $otherFrag
                </div>
                
            </div>
        </fieldset>
    </li>
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
        <span class="width:100% height:100% z-index:hidden-over-draggable draggable-hidden-over"></span>
        <fieldset
            class="width:100% padding:default box-shadow-variant-1 d:flex justify-content:center pointer-events:none">
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
        $slug = $this->generateUniqueSlug($this->getLicenseTable(),
            'license_slug', helper()->slug(input()->fromPost()->retrieve('license_slug')));

        $license = []; $postColumns = array_flip($this->getLicenseColumns());
        foreach (input()->fromPost()->all() as $inputKey => $inputValue){
            if (key_exists($inputKey, $postColumns) && input()->fromPost()->has($inputKey)){

                if($inputKey === 'created_at'){
                    $license[$inputKey] = helper()->date(timestamp: $inputValue);
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
    public function createTrack(array $ignore = []): array
    {
        $slug = $this->generateUniqueSlug($this->getTrackTable(),
            'track_slug', helper()->slug(input()->fromPost()->retrieve('track_slug')));

        $_POST['field_settings'] = input()->fromPost()->all();
        unset($_POST['field_settings']['token']);

        $track = []; $postColumns = array_flip($this->getTrackColumns());
        foreach (input()->fromPost()->all() as $inputKey => $inputValue){
            if (key_exists($inputKey, $postColumns) && input()->fromPost()->has($inputKey)){

                if($inputKey === 'created_at'){
                    $track[$inputKey] = helper()->date(timestamp: $inputValue);
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

        if (isset($track['field_ids'])){
            $track['field_ids'] = array_values(array_flip(array_flip($track['field_ids'])));
            $track['field_ids'] = json_encode($track['field_ids']);
        }

        if (isset($track['field_settings'])){
            $track['field_settings'] = json_encode($track['field_settings']);
            if (isset($track['field_ids'])){
                $_POST['field_settings']['field_ids'] = $track['field_ids'];
            }
        }

        return $track;
    }

    /**
     * @throws \Exception
     */
    public function createArtist(array $ignore = []): array
    {
        $slug = $this->generateUniqueSlug($this->getArtistTable(),
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
        $slug = $this->generateUniqueSlug($this->getGenreTable(),
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
        $table = $this->getLicenseTable();
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
            $this->getGenreTable(), 200, $settings);
    }

    /**
     * @throws \Exception
     */
    public function genreMetaBox($genre, string $inputName='fk_genre_id', $type = 'radio'){

        if (url()->getHeaderByKey('menuboxname') === 'genre') {
            if (url()->getHeaderByKey('action') === 'more' || url()->getHeaderByKey('action') === 'search') {
                $frag = $this->genreCheckBoxListing($genre, false, inputName: $inputName, type: $type);
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
                $licenseAttr = $this->selectWithCondition($this->getLicenseTable(), ['license_attr'], 'license_id = ?', [$licenseID]);
                $licenseAttr = json_decode($licenseAttr->license_attr);
            }

            if (is_array($licenseAttr)){
                helper()->onSuccess($this->getLicenseURLDownloadListing($licenseAttr, $licenseAttrIDLink));
            }
        }
    }

}