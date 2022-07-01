<?php

namespace App\Modules\Field\EventHandlers\Fields\Media;

use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class MediaAudio implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Media Audio', 'Upload An Audio Through The Native File Manager', 'Media',
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
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Audio';
        $defaultAudio = (isset($data->audio_url)) ? $data->audio_url: '';
        $elementWrapper =  (isset($data->elementWrapper)) ? $data->elementWrapper : '';
        $attributes = (isset($data->attributes)) ? helper()->htmlSpecChar($data->attributes) : '';
        $inputName =  (isset($data->inputName)) ? $data->inputName : '';
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

<div class="form-group d:flex flex-gap align-items:flex-end">
      <label class="menu-settings-handle-name" for="element-wrapper-$changeID">Element Wrapper
            <input id="element-wrapper-$changeID" name="elementWrapper" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$elementWrapper" placeholder="e.g div, section, input">
    </label>
      <label class="menu-settings-handle-name" for="element-attributes-$changeID">Element Attributes
            <input id="element-attributes-$changeID" name="attributes" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$attributes" placeholder="e.g class='class-name' id='id-name' or any attributes">
    </label>
</div>

{$event->handleViewProcessingFrag($data)}
{$event->getTemplateEngineFrag($data)}
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
        $inputName =  (isset($data->_field->postData[$data->inputName])) ? $data->_field->postData[$data->inputName] : '';
        $defaultAudio = (isset($data->audio_url) && !empty($inputName)) ? $inputName : $data->audio_url;
        $slug = $data->field_slug;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $inputName =  (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";
        $frag .= <<<FORM
<div class="form-group">
 <label class="menu-settings-handle-name" for="featured-audio-$changeID">Upload Default Audio (do nothing for no default)
     <input id="featured-audio-$changeID" class="tonics-audio-featured color:black border-width:default border:black placeholder-color:gray" name="featured_audio" type="file">
 </label>
</div>
<div class="form-group">
    <input id="default-image-$changeID" placeholder="Audio Demo URL" type="url" data-widget-audio-url="true" name="$inputName" value="$defaultAudio">
</div>
<div class="margin-top:0">
    <button type="button" class="remove-audio-demo d:none background:transparent border:none color:black bg:white-one border-width:default border:black padding:default margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">
        Remove Audio
    </button>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper(true);
        return $frag;
    }

}