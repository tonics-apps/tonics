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

use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TrackArtist implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('TrackArtist', 'Track Artist With Customizations', 'Track',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            }, userForm: function (){},handleViewProcessing: function (){}
        );
    }

    /**
     * @param OnFieldMetaBox $event
     * @param null $data
     * @return string
     * @throws \Exception
     */
    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Tracks Artist Settings';
        $artistPagination =  (isset($data->artistPagination)) ? $data->artistPagination : '1';
        $noOfArtistPerPage =  (isset($data->noOfArtistPerPage)) ? $data->noOfArtistPerPage : '6';
        $attributes = (isset($data->attributes)) ? helper()->htmlSpecChar($data->attributes) : '';
        if ($artistPagination=== '1'){
            $artistPagination = <<<HTML
<option value="1" selected>True</option>
<option value="0">False</option>
HTML;
        } else {
            $artistPagination = <<<HTML
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
     <label class="menu-settings-handle-name" for="track-artist-$changeID">Artist Pagination
     <select name="artistPagination" class="default-selector mg-b-plus-1" id="track-artist-CHANGEID">
        $artistPagination
     </select>
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="no-of-artist-$changeID">Number of Artist Per Page (Applicable if Artist Pagination is True)
            <input id="no-of-artist-$changeID" name="noOfArtistPerPage" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$noOfArtistPerPage">
    </label>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;

    }
}