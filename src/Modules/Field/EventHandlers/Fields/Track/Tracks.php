<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\EventHandlers\Fields\Track;

use App\Modules\Core\Library\Tables;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Track\Data\TrackData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class Tracks implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Tracks', 'Tracks With Several Customization', 'Track',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event) {
                return $this->userForm($event, $data);
            },
            handleViewProcessing: function ($data) use ($event) {
                return $this->viewFrag($event, $data);
            }
        );
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @return string
     */
    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Tracks Settings';
        $noOfTrackPerPage =  (isset($data->noOfTrackPerPage)) ? $data->noOfTrackPerPage : '6';
        $showTrackImage = (isset($data->showTrackImage)) ? $data->showTrackImage : '1';
        if ($showTrackImage === '1') {
            $showTrackImage = <<<HTML
<option value="1" selected>True</option>
<option value="0">False</option>
HTML;
        } else {
            $showTrackImage = <<<HTML
<option value="1">True</option>
<option value="0" selected>False</option>
HTML;
        }

        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $frag .= <<<FORM
<div class="form-group">
     <label class="menu-settings-handle-name" for="widget-name-$changeID">Field Name
            <input id="widget-name-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="no-of-track-$changeID">Number of Track Per Page
            <input id="no-of-track-$changeID" name="noOfTrackPerPage" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$noOfTrackPerPage">
    </label>
</div>

<div class="form-group">
    <label class="menu-settings-handle-name" for="showTrackImage-$changeID">Show Track Image
     <select name="showTrackImage" class="default-selector mg-b-plus-1" id="showTrackImage-$changeID">
        $showTrackImage
     </select>
    </label>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;

    }

    /**
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Tracks';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function viewFrag(OnFieldMetaBox $event, $data): string
    {
        $trackData = new TrackData();
        $trackTable = Tables::getTable(Tables::TRACKS);
        $genreTable = Tables::getTable(Tables::GENRES);
        $artistTable = Tables::getTable(Tables::ARTISTS);
        $licenseTable = Tables::getTable(Tables::LICENSES);

        $noOfTrackPerPage = (isset($data->noOfTrackPerPage)) ? (int)$data->noOfTrackPerPage : 6;

        $customCallable = [
            'customSearchTableCount' => function ($table, $searchTerm, $colToSearch) use ($trackTable, $genreTable, $artistTable, $licenseTable) {
                $where = "WHERE track_status = 1 AND $colToSearch LIKE CONCAT('%', ?, '%')";
                $params[] = $searchTerm;
                return db()->row(<<<SQL
SELECT COUNT(*) AS 'r' FROM $trackTable 
    JOIN $genreTable ON $trackTable.fk_genre_id = $genreTable.genre_id
    JOIN $artistTable ON $trackTable.fk_artist_id = $artistTable.artist_id
    JOIN $licenseTable ON $trackTable.fk_license_id = $licenseTable.license_id
$where
SQL, ...$params)->r;
            },
            'customTableCount' => function ($table) use ($trackTable, $genreTable, $artistTable, $licenseTable) {
                $where = "WHERE track_status = 1";
                return db()->row(<<<SQL
SELECT COUNT(*) AS 'r' FROM $trackTable 
    JOIN $genreTable ON $trackTable.fk_genre_id = $genreTable.genre_id
    JOIN $artistTable ON $trackTable.fk_artist_id = $artistTable.artist_id
    JOIN $licenseTable ON $trackTable.fk_license_id = $licenseTable.license_id
$where
SQL)->r;
            },
            'customSearchRowWithOffsetLimit' => function ($table, $searchTerm, $offset, $limit, $colToSearch, $cols) use ($trackTable, $genreTable, $artistTable, $licenseTable) {
                $where = "WHERE track_status = 1 AND $colToSearch LIKE CONCAT('%', ?, '%') LIMIT ? OFFSET ?";
                $params[] = $searchTerm;
                $params[] = $limit;
                $params[] = $offset;
                return db()->run(<<<SQL
SELECT * FROM $trackTable 
    JOIN $genreTable ON $trackTable.fk_genre_id = $genreTable.genre_id
    JOIN $artistTable ON $trackTable.fk_artist_id = $artistTable.artist_id
    JOIN $licenseTable ON $trackTable.fk_license_id = $licenseTable.license_id
$where
SQL, ...$params);
            },
            'customGetRowWithOffsetLimit' => function ($table, $offset, $limit, $cols) use ($trackTable, $genreTable, $artistTable, $licenseTable) {
                $where = "WHERE track_status = 1 LIMIT ? OFFSET ?";
                $params[] = $limit;
                $params[] = $offset;
                return  db()->run(<<<SQL
SELECT $cols FROM $trackTable 
    JOIN $genreTable ON $trackTable.fk_genre_id = $genreTable.genre_id
    JOIN $artistTable ON $trackTable.fk_artist_id = $artistTable.artist_id
    JOIN $licenseTable ON $trackTable.fk_license_id = $licenseTable.license_id
$where
SQL, ...$params);
            },
        ];

        try {
            $tracks = $trackData->generatePaginationData(
                $trackData->getAllTrackPageColumns(),
                'track_title',
                $trackData->getTrackTable(), $noOfTrackPerPage, $customCallable);
        }catch (\Exception $exception){
            dd($exception->getMessage());
        }
        $trackArray = (isset($tracks->data)) ? $tracks->data : [];
        foreach ($trackArray as $track){
            $track->track_licenses = json_decode($track->license_attr);
        }

        addToGlobalVariable('TrackData', $trackArray);
        return '';
    }

}