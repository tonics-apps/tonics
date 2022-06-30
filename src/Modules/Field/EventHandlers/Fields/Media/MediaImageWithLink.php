<?php

namespace App\Modules\Field\EventHandlers\Fields\Media;

use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class MediaImageWithLink implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Media Image With Link', 'Upload An Image Through The Native File Manager', 'Media',
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
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Image';
        $defaultImage = (isset($data->defaultImage)) ? $data->defaultImage: '';
        $defaultLinkTo = (isset($data->link)) ? $data->link: '';
        $attributes = (isset($data->attributes)) ? helper()->htmlSpecChar($data->attributes) : '';

        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $frag .= <<<FORM
<div class="form-group">
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Field Name
            <input id="fieldName-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
</div>

<div class="form-group">
<label class="menu-settings-handle-name" for="image-link-$changeID">Default Image Link (leave blank for no default)
            <input id="image-link-$changeID" name="link" placeholder="Image Link" type="url" class="menu-name color:black border-width:default border:black placeholder-color:gray" name="image_url" value="$defaultLinkTo">
    </label>
</div>

<div class="form-group">
 <label class="menu-settings-handle-name" for="featured-image-$changeID">Upload Default Image (do nothing for no default)
     <input id="featured-image-$changeID" class="tonics-featured-image color:black border-width:default border:black placeholder-color:gray" name="featured_image" type="file">
 </label>
</div>
<div class="margin-top:0">
            <input id="default-image-$changeID" name="defaultImage" placeholder="Image Link" type="hidden" data-widget-image-name="true" 
            class="menu-name color:black border-width:default border:black placeholder-color:gray" value="$defaultImage">
    <img src="$defaultImage" class="image:featured-image featured-image widgetSettings" alt="">
</div>
<div class="margin-top:0">
    <button type="button" class="remove-featured-image d:none background:transparent border:none color:black bg:white-one border-width:default border:black padding:default margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">
        Remove Featured Image
    </button>
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

    /**
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Image';
        $defaultImage = (isset($data->defaultImage)) ? $data->defaultImage: '';
        $defaultLinkTo = (isset($data->link)) ? $data->link: '';

        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $frag .= <<<FORM
<div class="form-group margin-top:0">
     <label class="menu-settings-handle-name" for="featured-link-$changeID">Upload File
         <input id="featured-link-$changeID" class="tonics-featured-link color:black border-width:default border:black placeholder-color:gray" name="featured_link" type="file">
     </label>
 </div>

<div class="form-group">
<label class="menu-settings-handle-name" for="image-link-$changeID">Default Image Link (leave blank for no default)
            <input id="image-link-$changeID" name="link_$changeID" placeholder="Image Link" type="url" class="menu-name color:black border-width:default border:black placeholder-color:gray" name="image_url" value="$defaultLinkTo">
    </label>
</div>

<div class="form-group">
 <label class="menu-settings-handle-name" for="featured-image-$changeID">Upload Default Image (do nothing for no default)
     <input id="featured-image-$changeID" class="tonics-featured-image color:black border-width:default border:black placeholder-color:gray" name="featured_image_$changeID" type="file">
 </label>
</div>
<div class="margin-top:0">
            <input id="default-image-$changeID" name="defaultImage" placeholder="Image Link" type="hidden" data-widget-image-name="true" 
            class="menu-name color:black border-width:default border:black placeholder-color:gray" value="$defaultImage">
    <img src="$defaultImage" class="image:featured-image featured-image widgetSettings" alt="">
</div>
<div class="margin-top:0">
    <button type="button" class="remove-featured-image d:none background:transparent border:none color:black bg:white-one border-width:default border:black padding:default margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">
        Remove Image
    </button>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper(true);
        return $frag;
    }

}