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

class MediaFileManager implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Media Manager', 'Upload Anything Through The Native File Manager', 'Media',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event) {
                return $this->userForm($event, $data);
            },
        );
    }

    /**
     * @throws \Exception
     */
    public function settingsForm (OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'MediaManager';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $defaultFileLink = (isset($data->file_url)) ? $data->file_url : '';
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
     <label class="menu-settings-handle-name" for="featured-link-$changeID">Upload File (do nothing for no default)
         <input id="featured-link-$changeID" class="tonics-featured-link color:black border-width:default border:black placeholder-color:gray" 
         name="featured_link" type="file">
     </label>
 </div>

<div class="form-group">
    <label class="menu-settings-handle-name" for="featured-link-url-$changeID">Default File Link (leave blank for no default)
        <input id="featured-link-url-$changeID" placeholder="File URL" type="url" data-widget-file-url="true" name="file_url" value="$defaultFileLink">
    </label>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function userForm (OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'MediaManager';
        $keyValue = $event->getKeyValueInData($data, $data->inputName);
        $defaultFileLink = (isset($data->file_url) && !empty($keyValue)) ? $keyValue : $data->file_url;
        $slug = $data->field_slug;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $inputName = (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";
        $frag .= <<<FORM
<div data-draggable-ignore class="d:flex flex-d:row flex-gap align-items:flex-end">

    <div class="form-group margin-top:0">
         <label class="menu-settings-handle-name" for="featured-link-$changeID">Upload File
             <input id="featured-link-$changeID" class="tonics-featured-link color:black border-width:default border:black placeholder-color:gray" name="featured_link" type="file">
         </label>
     </div>
    
    <div class="form-group">
        <label class="menu-settings-handle-name" for="featured-link-url-$changeID">File Link (leave blank for no default)
            <input id="featured-link-url-$changeID" placeholder="Or File URL" type="url" data-widget-file-url="true" name="$inputName" value="$defaultFileLink">
        </label>
    </div>

</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }
}