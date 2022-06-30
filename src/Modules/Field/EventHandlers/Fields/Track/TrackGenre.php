<?php

namespace App\Modules\Field\EventHandlers\Fields\Track;

use App\Modules\Field\Events\OnFieldMetaBox;

class TrackGenre implements \Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('TrackGenre', 'Track Genre With Customizations', 'Track',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function (){},
            handleViewProcessing: function (){}
        );
    }

    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Tracks Genre Settings';
        $genrePagination =  (isset($data->genrePagination)) ? $data->genrePagination : '1';
        $noOfGenrePerPage =  (isset($data->noOfGenrePerPage)) ? $data->noOfGenrePerPage : '6';
        $attributes = (isset($data->attributes)) ? helper()->htmlSpecChar($data->attributes) : '';
        if ($genrePagination=== '1'){
            $genrePagination = <<<HTML
<option value="1" selected>True</option>
<option value="0">False</option>
HTML;
        } else {
            $genrePagination = <<<HTML
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
     <label class="menu-settings-handle-name" for="genre-pagination-$changeID">Genre Pagination
     <select name="genrePagination" class="default-selector mg-b-plus-1" id="genre-pagination-CHANGEID">
        $genrePagination
     </select>
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="no-of-genre-$changeID">Number of Genre Per Page (Applicable if Genre Pagination is True)
            <input id="no-of-genre-$changeID" name="noOfGenrePerPage" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$noOfGenrePerPage">
    </label>
</div>

    <div class="form-group">
      <label class="menu-settings-handle-name" for="element-attributes-$changeID">Element Attributes
            <input id="element-attributes-$changeID" name="attributes" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$attributes" placeholder="e.g class='class-name' id='id-name' or any attributes">
    </label>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;

    }
}