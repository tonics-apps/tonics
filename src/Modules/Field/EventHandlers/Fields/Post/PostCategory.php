<?php

namespace App\Modules\Field\EventHandlers\Fields\Post;

use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class PostCategory implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('PostCategory', 'Post Category With Customizations', 'Post',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function (){},
            handleViewProcessing: function (){}
        );
    }

    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Posts Category Settings';
        $postCategoryPagination =  (isset($data->postCategoryPagination)) ? $data->postCategoryPagination : '1';
        $noOfPostCategoryPerPage =  (isset($data->noOfPostCategoryPerPage)) ? $data->noOfPostCategoryPerPage : '6';
        $attributes = (isset($data->attributes)) ? helper()->htmlSpecChar($data->attributes) : '';
        if ($postCategoryPagination=== '1'){
            $postCategoryPagination = <<<HTML
<option value="1" selected>True</option>
<option value="0">False</option>
HTML;
        } else {
            $postCategoryPagination = <<<HTML
<option value="1">True</option>
<option value="0" selected>False</option>
HTML;
        }

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
     <label class="menu-settings-handle-name" for="postCategoryPagination-$changeID">Post Category Pagination
     <select name="postCategoryPagination" class="default-selector mg-b-plus-1" id="postCategoryPagination-$changeID">
        $postCategoryPagination
     </select>
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="noOfPostCategoryPerPage-$changeID">Number of Post Category Per Page (Applicable if Post Category Pagination is True)
            <input id="noOfPostCategoryPerPage-CHANGEID" name="noOfPostCategoryPerPage" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$noOfPostCategoryPerPage">
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