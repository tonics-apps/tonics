<?php

namespace App\Modules\Field\EventHandlers\Fields\Track;

use App\Modules\Field\Events\OnFieldMetaBox;
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
            userForm: function (){},
            handleViewProcessing: function (){}
        );
    }

    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Tracks Settings';
        $trackPagination =  (isset($data->trackPagination)) ? $data->trackPagination : '1';
        $noOfTrackPerPage =  (isset($data->noOfTrackPerPage)) ? $data->noOfTrackPerPage : '6';
        $trackPurchasable =  (isset($data->trackPurchasable)) ? $data->trackPurchasable : '1';
                $attributes = (isset($data->attributes)) ? helper()->htmlSpecChar($data->attributes) : '';
        if ($trackPurchasable === '1'){
            $trackPurchasable = <<<HTML
<option value="1" selected>True</option>
<option value="0">False</option>
HTML;
        } else {
            $trackPurchasable = <<<HTML
<option value="1">True</option>
<option value="0" selected>False</option>
HTML;
        }

        if ($trackPagination=== '1'){
            $trackPagination = <<<HTML
<option value="1" selected>True</option>
<option value="0">False</option>
HTML;
        } else {
            $trackPagination = <<<HTML
<option value="1">True</option>
<option value="0" selected>False</option>
HTML;
        }

        $form = '';
        if (isset($data->_topHTMLWrapper)){
            $topHTMLWrapper = $data->_topHTMLWrapper;
            $slug = $data->_field->field_name ?? null;
            $name = $event->getRealName($slug);
            $form = $topHTMLWrapper($name, $slug);
        }
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $form .= <<<FORM
<div class="form-group">
     <label class="menu-settings-handle-name" for="widget-name-$changeID">Field Name
            <input id="widget-name-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="track-purchable-$changeID">Tracks Purchable
     <select name="trackPurchasable" class="default-selector mg-b-plus-1" id="track-purchable-$changeID">
        $trackPurchasable
     </select>
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="track-pagination-$changeID">Track Pagination
     <select name="trackPagination" class="default-selector mg-b-plus-1" id="track-pagination-$changeID">
        $trackPagination
     </select>
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="no-of-track-$changeID">Number of Track Per Page (Applicable if Post Category Pagination is True)
            <input id="no-of-track-$changeID" name="noOfTrackPerPage" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$noOfTrackPerPage">
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

}