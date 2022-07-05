<?php

namespace App\Modules\Field\EventHandlers\Fields\Post;

use App\Modules\Core\Data\UserData;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Post\Data\PostData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class PostAuthorSelect implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('PostAuthorSelect', 'Post Author HTML Selection', 'Post',
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
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Posts Author Select';
        $inputName =  (isset($data->inputName)) ? $data->inputName : '';
        $attributes = (isset($data->attributes)) ? helper()->htmlSpecChar($data->attributes) : '';
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'PostCategorySelect';
        $inputName = (isset(getPostData()[$data->inputName])) ? getPostData()[$data->inputName] : '';
        $userData = new UserData();
        $categories = $userData->getPostAuthorHTMLSelect($inputName ?: null);
        $slug = $data->field_slug;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $inputName =  (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";
        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $frag .= <<<FORM
<div class="form-group margin-top:0">
    <select id="categories" name="$inputName" class="default-selector">
                    <option value="" selected style="color: #004085">-Parent Category-</option>
                    $categories
    </select>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper(true);
        return $frag;
    }
}