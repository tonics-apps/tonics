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

namespace App\Modules\Core\Data;

use App\Modules\Core\Library\AbstractDataLayer;

class ImportData extends AbstractDataLayer
{

    public function adminImportListing(array $importTypes): string
    {
        $htmlFrag = ''; $urlPrefix = "/admin/tools/imports";
        foreach ($importTypes as $slug => $type){
            $htmlFrag .= <<<HTML
    <li 
    tabindex="0" 
    data-db_click_link="$urlPrefix/$slug"
    class="admin-widget-item-for-listing d:flex flex-d:column align-items:center justify-content:center cursor:pointer no-text-highlight">
        <fieldset class="padding:default width:100% box-shadow-variant-1 d:flex justify-content:center">
            <legend class="bg:pure-black color:white padding:default">$type</legend>
            <div class="admin-widget-information owl width:100%">
            <div class="text-on-admin-util text-highlight">$type</div>
         
                <div class="form-group d:flex flex-gap:small">
                     <a href="$urlPrefix/$slug" 
class="listing-button text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Use</a> 
                </div>
                
            </div>
        </fieldset>
    </li>
HTML;
        }

        return $htmlFrag;

    }

    public function getImportTypes(): array
    {
        return [
            'wordpress' => 'WordPress',
            'beatstars' => 'BeatStars',
            'airbit' => 'AirBit',
        ];
    }
}