<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\EventHandlers\Fields\Media;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class MediaImage implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Media Image', 'Upload An Image Through The Native File Manager', 'Media',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event) {
                return $this->userForm($event, $data);
            },
            handleViewProcessing: function ($data) use ($event) {
                $this->viewData($event, $data);
            }
        );
    }

    /**
     * @throws \Exception
     */
    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Image';
        $defaultImage = (isset($data->defaultImage)) ? $data->defaultImage : '';
        $imageLink = (isset($data->imageLink)) ? $data->imageLink : '';
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
 <label class="menu-settings-handle-name" for="imageLink-$changeID">Enter a image Link (via [[v('Image_$inputName.Link')]])
     <input id="imageLink-$changeID" value="$imageLink" class="color:black border-width:default border:black placeholder-color:gray" name="imageLink" type="text" placeholder="Would be used if uploaded image does not exist">
 </label>
</div>
<div> OR </div>
<div class="form-group">
 <label class="menu-settings-handle-name" for="featured-image-$changeID">Upload Image (do nothing for no default)
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
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @return string
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Image';
        $keyValue =  $event->getKeyValueInData($data, $data->inputName);
        $defaultImage = (isset($data->defaultImage) && !empty($keyValue)) ? $keyValue : $data->defaultImage;
        $slug = $data->field_slug;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";

        $frag .= <<<FORM
<div class="form-group">
 <label class="menu-settings-handle-name" for="featured-image-$changeID">
     <input id="featured-image-$changeID" class="tonics-featured-image color:black border-width:default border:black placeholder-color:gray" name="featured_image" type="file">
 </label>
</div>
<div class="margin-top:0">
            <input id="default-image-$changeID" name="$inputName" placeholder="Image Link" type="hidden" data-widget-image-name="true" 
            class="menu-name color:black border-width:default border:black placeholder-color:gray" value="$defaultImage">
    <img src="$defaultImage" class="image:featured-image featured-image widgetSettings" alt="">
</div>
<div class="margin-top:0">
    <button type="button" class="remove-featured-image d:none background:transparent border:none color:black bg:white-one border-width:default border:black padding:default margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">
        Remove Image
    </button>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function viewData(OnFieldMetaBox $event, $data = null)
    {
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Widget';
        $fieldData = (isset($data->_field->field_data)) ? $data->_field->field_data : '';
        $postData = !empty(getPostData()) ? getPostData() : $fieldData;
        $inputName = (isset($postData[$data->inputName])) ? $postData[$data->inputName] : '';
        $defaultImage = (isset($data->defaultImage) && !empty($inputName)) ? $inputName : $data->defaultImage;
        $imageLink = (isset($data->imageLink)) ? $data->imageLink : '';
        if (empty($defaultImage)) {
            $defaultImage = $imageLink;
        }
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        addToGlobalVariable("Image_$inputName", ['Name' => $fieldName, 'inputName' => $inputName, 'Link' => $defaultImage]);
    }

}