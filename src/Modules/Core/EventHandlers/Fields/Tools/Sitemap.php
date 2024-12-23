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

namespace App\Modules\Core\EventHandlers\Fields\Tools;

use App\Modules\Core\Events\Tools\Sitemap\OnAddSitemap;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class Sitemap implements HandlerInterface
{

    public function handleEvent (object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox(
            'Sitemap',
            'Add Sitemap Handlers',
            'Tool',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            }, userForm: function ($data) use ($event) {
            return $this->userForm($event, $data);
        },
        );
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     *
     * @return string
     * @throws \Throwable
     */
    public function settingsForm (OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Sitemaps';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $frag .= <<<FORM
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Field Name
            <input id="fieldName-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
    <label class="menu-settings-handle-name" for="inputName-$changeID">Input Name
            <input id="inputName-$changeID" name="inputName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$inputName" placeholder="(Optional) Input Name">
    </label>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }


    /**
     * @param OnFieldMetaBox $event
     * @param $data
     *
     * @return string
     * @throws \Throwable
     */
    public function userForm (OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Sitemaps';
        $selectedSitemap = (isset(getPostData()[$data->inputName])) ? getPostData()[$data->inputName] : [];
        if (!is_array($selectedSitemap)) {
            $selectedSitemap = [];
        }
        $selectedSitemap = array_combine($selectedSitemap, $selectedSitemap);

        $slug = $data->field_slug;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";
        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $onAddSitemapEvent = (new OnAddSitemap())->dispatchEvent();
        $sitemapHandlers = $onAddSitemapEvent->getSitemap();

        $sitemapFrag = '';
        foreach ($sitemapHandlers as $sitemapName => $sitemapObject) {
            $selected = '';
            $sitemapName = ucfirst($sitemapName);
            if (isset($selectedSitemap[$sitemapName])) {
                $selected = 'checked';
            }
            $sitemapFrag .= <<<HTML
<li>
    <input $selected type="checkbox"  id="{$sitemapName}_$changeID" name="{$inputName}[]" value="$sitemapName">
    <label for="{$sitemapName}_$changeID">$sitemapName
    </label>
</li>
HTML;
        }

        $frag .= <<<FORM
<div class="form-group margin-top:0">     
<label class="menu-settings-handle-name screen-reader-text" for="SitemapChoiceHandler-$changeID">$fieldName</label>
<ul style="margin-left: 0;" class="list:style:none margin-top:0">
    $sitemapFrag
</ul>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

}