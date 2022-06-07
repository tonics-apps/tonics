<?php

namespace App\Modules\Field\EventHandlers\Fields\Track;

use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Track\Data\TrackData;
use App\Modules\Track\Events\OnTrackCreate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TrackGenreRadio implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('TrackGenreRadio', 'Track Genre Radio (Works Only In The Context of Track)', 'Track',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event) {
                return $this->userForm($event, $data);
            },
            handleViewProcessing: function () {
            }
        );
    }

    /**
     * @throws \Exception
     */
    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Track Genre Radio';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $form = '';
        if (isset($data->_topHTMLWrapper)) {
            $topHTMLWrapper = $data->_topHTMLWrapper;
            $slug = $data->_field->field_name ?? null;
            $name = $event->getRealName($slug);
            $form = $topHTMLWrapper($name, $slug);
        }
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $form .= <<<FORM
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

        if (isset($data->_bottomHTMLWrapper)) {
            $form .= $data->_bottomHTMLWrapper;
        }

        return $form;
    }

    /**
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'TrackGenreRadio';
        $inputName = (isset($data->_field->postData[$data->inputName])) ? $data->_field->postData[$data->inputName] : '';
        $trackData = new TrackData();
        $topHTMLWrapper = $data->_topHTMLWrapper;
        $slug = $data->field_slug;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";
        $genre = $trackData->getGenrePaginationData();
        $onTrackCreate = null;
        if (!empty($data->_field->postData)){
            $onTrackCreate = new OnTrackCreate((object)$data->_field->postData, $trackData);
        }
        $genreCheckBoxListing = $trackData->genreCheckBoxListing($genre, onTrackCreate: $onTrackCreate, inputname: $inputName);
        $form = $topHTMLWrapper($fieldName, $slug);
        $form .= <<<FORM
<div class="form-group margin-top:0">     
<label class="menu-settings-handle-name screen-reader-text" for="trackGenreRadio-$changeID">$fieldName</label>
    <ul style="margin-left: 0" id="trackGenreRadio-$changeID" class="list:style:none max-height:300px overflow-x:auto menu-box-radiobox-items">
         $genreCheckBoxListing
    </ul>
</div>
FORM;

        if (isset($data->_bottomHTMLWrapper)) {
            $form .= $data->_bottomHTMLWrapper;
        }

        return $form;
    }


}