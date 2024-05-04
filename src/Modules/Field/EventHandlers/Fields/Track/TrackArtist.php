<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
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