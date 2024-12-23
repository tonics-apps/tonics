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

namespace App\Modules\Field\EventHandlers\Fields\Media;

use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class MediaAudio implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Media Audio', 'Upload An Audio Through The Native File Manager', 'Media',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event) {
                return $this->userForm($event, $data);
            },
        );
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     *
     * @return string
     * @throws \Exception
     */
    public function settingsForm (OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Audio';
        $defaultAudio = (isset($data->audio_url)) ? $data->audio_url : '';
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

<div class="form-group">
 <label class="menu-settings-handle-name" for="featured-audio-$changeID">Upload Default Audio (do nothing for no default)
     <input id="featured-audio-$changeID" class="tonics-audio-featured color:black border-width:default border:black placeholder-color:gray" name="featured_audio" type="file">
 </label>
</div>
<div class="form-group">
    <input id="default-image-$changeID" placeholder="Audio Demo URL" type="url" data-widget-audio-url="true" name="audio_url" value="$defaultAudio">
</div>
<div class="margin-top:0">
    <button type="button" class="remove-audio-demo d:none background:transparent border:none color:black bg:white-one border-width:default border:black padding:default margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">
        Remove Audio
    </button>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function userForm (OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Image';

        $keyValue = $event->getKeyValueInData($data, $data->inputName);
        $defaultAudio = (isset($data->audio_url) && !empty($keyValue)) ? $keyValue : $data->audio_url;
        $slug = $data->field_slug;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";
        $frag .= <<<FORM
<div class="form-group">
 <label class="menu-settings-handle-name" for="featured-audio-$changeID">Upload Default Audio (do nothing for no default)
     <input id="featured-audio-$changeID" class="tonics-audio-featured color:black border-width:default border:black placeholder-color:gray" name="featured_audio" type="file">
 </label>
</div>
<div style="margin-top: 1em;" class="form-group">
    <input id="default-image-$changeID" placeholder="Or Audio Demo URL" type="url" data-widget-audio-url="true" name="$inputName" value="$defaultAudio">
</div>
<div class="margin-top:0">
    <button type="button" class="remove-audio-demo d:none background:transparent border:none color:black bg:white-one border-width:default border:black padding:default margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">
        Remove Audio
    </button>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

}