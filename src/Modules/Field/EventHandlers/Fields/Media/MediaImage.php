<?php

namespace App\Modules\Field\EventHandlers\Fields\Media;

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
                return $this->viewFrag($event, $data);
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
        $attributes = (isset($data->attributes)) ? helper()->htmlSpecChar($data->attributes) : '';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $handleViewProcessingFrag = $event->handleViewProcessingFrag((isset($data->handleViewProcessing)) ? $data->handleViewProcessing : '');

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
<div class="form-group">
 <label class="menu-settings-handle-name" for="imageLink-$changeID">Enter a image Link
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

<div class="form-group">
      <label class="menu-settings-handle-name" for="element-attributes-$changeID">More Image Attributes
            <input id="element-attributes-$changeID" name="attributes" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$attributes" placeholder="e.g class='class-name' id='id-name' or any attributes">
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="handleViewProcessing-$changeID">Automatically Handle View Processing
     <select name="handleViewProcessing" class="default-selector mg-b-plus-1" id="handleViewProcessing-$changeID">
        $handleViewProcessingFrag
     </select>
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Image';
        $inputName = (isset($data->_field->postData[$data->inputName])) ? $data->_field->postData[$data->inputName] : '';
        $defaultImage = (isset($data->defaultImage) && !empty($inputName)) ? $inputName : $data->defaultImage;
        $topHTMLWrapper = $data->_topHTMLWrapper;
        $slug = $data->field_slug;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $form = $topHTMLWrapper($fieldName, $slug, $changeID);
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";
        $form .= <<<FORM
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

        if (isset($data->_bottomHTMLWrapper)) {
            $form .= $data->_bottomHTMLWrapper;
        }

        return $form;
    }

    /**
     * @throws \Exception
     */
    public function viewFrag(OnFieldMetaBox $event, $data = null): string
    {
        $inputName = (isset($data->_field->postData[$data->inputName])) ? $data->_field->postData[$data->inputName] : '';
        $defaultImage = (isset($data->defaultImage) && !empty($inputName)) ? $inputName : $data->defaultImage;
        $imageLink = (isset($data->imageLink)) ? $data->imageLink : '';
        if (empty($defaultImage)) {
            $defaultImage = $imageLink;
        }
        $frag = '';
        if (isset($data->handleViewProcessing) && $data->handleViewProcessing === '1') {
            $frag .= <<<HTML
<img src="$defaultImage" 
HTML;
            if (!empty($data->attributes)) {
                $attributes = $event->flatHTMLTagAttributes($data->attributes);
                $frag .= $attributes;
            }
            $frag .= ">";
        }

        return $frag;
    }

}