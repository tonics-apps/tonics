<?php

namespace App\Modules\Field\EventHandlers\Fields\Track;

use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Track\Data\TrackData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TrackArtistSelect implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('TrackArtistSelect', 'Track Artist Select (Works Only In The Context of Track)', 'Track',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event){
                return $this->userForm($event, $data);
            },
            handleViewProcessing: function (){}
        );
    }

    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Track Artist Select';
        $inputName =  (isset($data->inputName)) ? $data->inputName : '';
                $attributes = (isset($data->attributes)) ? helper()->htmlSpecChar($data->attributes) : '';
        $form = '';
        if (isset($data->_topHTMLWrapper)){
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
    <div class="form-group">
      <label class="menu-settings-handle-name" for="element-attributes-$changeID">Element Attributes
            <input id="element-attributes-$changeID" name="attributes" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$attributes" placeholder="e.g class='class-name' id='id-name' or any attributes">
    </label>
</div>
FORM;

        if (isset($data->_bottomHTMLWrapper)){
            $form .= $data->_bottomHTMLWrapper;
        }

        return $form;
    }

    /**
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'TrackLicenseSelect';
        $inputName = (isset($data->_field->postData[$data->inputName])) ? $data->_field->postData[$data->inputName] : '';
        $trackData = new TrackData();
        $artistSelectListing = $trackData->artistSelectListing($inputName ?: null);
        $topHTMLWrapper = $data->_topHTMLWrapper;
        $slug = $data->field_slug;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $inputName =  (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";
        $form = $topHTMLWrapper($fieldName, $slug);
        $form .= <<<FORM
<div class="form-group margin-top:0">     
<label class="menu-settings-handle-name screen-reader-text" for="trackArtistSelect-$changeID">$fieldName</label>
    <select id="trackArtistSelect-$changeID" name="$inputName" class="default-selector">
                    $artistSelectListing
    </select>
</div>
FORM;

        if (isset($data->_bottomHTMLWrapper)){
            $form .= $data->_bottomHTMLWrapper;
        }

        return $form;
    }
}