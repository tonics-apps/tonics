<?php

namespace App\Modules\Field\EventHandlers\Fields\Post;

use App\Modules\Core\Library\Tables;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Post\Data\PostData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class Posts implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Posts', 'Posts With Several Customization', 'Post',
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Posts Settings';
        $showPostImage = (isset($data->showPostImage)) ? $data->showPostImage : '1';
        $noOfPostPerPage = (isset($data->noOfPostPerPage)) ? $data->noOfPostPerPage : '6';
        $elementWrapper = (isset($data->elementWrapper)) ? $data->elementWrapper : '';
        $attributes = (isset($data->attributes)) ? helper()->htmlSpecChar($data->attributes) : '';
        $postDescriptionLength = (isset($data->postDescriptionLength)) ? $data->postDescriptionLength : '200';
        $postInCategories = (isset($data->postInCategories)) ? $data->postInCategories : '';
        $readMoreLabel = (isset($data->readMoreLabel)) ? $data->readMoreLabel : 'Read More';
        if ($showPostImage === '1') {
            $showPostImage = <<<HTML
<option value="1" selected>True</option>
<option value="0">False</option>
HTML;
        } else {
            $showPostImage = <<<HTML
<option value="1">True</option>
<option value="0" selected>False</option>
HTML;
        }

        $form = '';
        if (isset($data->_topHTMLWrapper)) {
            $topHTMLWrapper = $data->_topHTMLWrapper;
            $slug = $data->_field->field_name ?? null;
            $name = $event->getRealName($slug);
            $form = $topHTMLWrapper($name, $slug);
        }
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $form .= <<<FORM
<div class="form-group">
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Field Name
            <input id="fieldName-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="showPostImage-$changeID">Show Post Image
     <select name="showPostImage" class="default-selector mg-b-plus-1" id="showPostImage-$changeID">
        $showPostImage
     </select>
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="noOfPostPerPage-$changeID">Number of Post Per Page (Applicable if Post Pagination is True)
            <input id="noOfPostPerPage-$changeID" name="noOfPostPerPage" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$noOfPostPerPage">
    </label>
</div>

<div class="form-group d:flex flex-gap align-items:flex-end">
 <label class="menu-settings-handle-name" for="postDescriptionLength-$changeID">Post Excerpt Length:
            <input id="postDescriptionLength-$changeID" name="postDescriptionLength" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$postDescriptionLength">
    </label>
      <label class="menu-settings-handle-name" for="postInCategories-$changeID">Posts In Category (empty for all posts):
            <input id="postInCategories-$changeID" name="postInCategories" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$postInCategories" placeholder="Category slug (seperated by comma)">
    </label>
          <label class="menu-settings-handle-name" for="readMoreLabel-$changeID">ReadMore Label:
            <input id="readMoreLabel-$changeID" name="readMoreLabel" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$readMoreLabel">
    </label>
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Posts';
        $topHTMLWrapper = $data->_topHTMLWrapper;
        $slug = $data->field_slug;
        $form = $topHTMLWrapper($fieldName, $slug);
        if (isset($data->_bottomHTMLWrapper)) {
            $form .= $data->_bottomHTMLWrapper;
        }

        return $form;
    }

    /**
     * @throws \Exception
     */
    public function viewFrag(OnFieldMetaBox $event, $data): string
    {
        $postData = new PostData();
        $noOfPostPerPage = (isset($data->noOfPostPerPage)) ? (int)$data->noOfPostPerPage : 6;
        $postInCategories = (isset($data->postInCategories)) ? $data->postInCategories : '';
        $postInCategories = (empty($postInCategories)) ? [] : explode(',', $postInCategories);
        $postDescriptionLength = (isset($data->postDescriptionLength)) ? $data->postDescriptionLength : '200';
        $readMoreLabel = (isset($data->readMoreLabel)) ? $data->readMoreLabel : 'Read More';
        $postDescriptionLength = (int)$postDescriptionLength;
        $postTable = Tables::getTable(Tables::POSTS);
        $postToCatTable = Tables::getTable(Tables::POST_CATEGORIES);
        $categoryTable = Tables::getTable(Tables::CATEGORIES);
        $customCallable = [
            'customSearchTableCount' => function ($table, $searchTerm, $colToSearch) use ($categoryTable, $postTable, $postToCatTable, $postInCategories) {
                if (!is_array($postInCategories)) {
                    $postInCategories = [];
                }
                $where = "WHERE post_status = 1 AND $colToSearch LIKE CONCAT('%', ?, '%')";
                if (is_array($postInCategories) && !empty($postInCategories)) {
                    $qMark = helper()->returnRequiredQuestionMarks($postInCategories);
                    $postInCategories[] = $searchTerm;
                    $where = "WHERE post_status = 1 AND cat_slug IN($qMark) AND $colToSearch LIKE CONCAT('%', ?, '%')";
                }
                $postInCategories[] = $searchTerm;
                return db()->row(<<<SQL
SELECT COUNT(*) AS 'r' FROM $postToCatTable 
    JOIN $postTable ON $postToCatTable.fk_post_id = $postTable.post_id
    JOIN $categoryTable ON $postToCatTable.fk_cat_id = $categoryTable.cat_id
$where
SQL, ...$postInCategories)->r;
            },
            'customTableCount' => function ($table) use ($categoryTable, $postTable, $postToCatTable, $postInCategories) {
                if (!is_array($postInCategories)) {
                    $postInCategories = [];
                }
                $where = "WHERE post_status = 1";
                if (is_array($postInCategories) && !empty($postInCategories)) {
                    $qMark = helper()->returnRequiredQuestionMarks($postInCategories);
                    $where = "WHERE post_status = 1 AND cat_slug IN($qMark)";
                }
                return db()->row(<<<SQL
SELECT COUNT(*) AS 'r' FROM $postToCatTable 
    JOIN $postTable ON $postToCatTable.fk_post_id = $postTable.post_id
    JOIN $categoryTable ON $postToCatTable.fk_cat_id = $categoryTable.cat_id
$where
SQL, ...$postInCategories)->r;
            },
            'customSearchRowWithOffsetLimit' => function ($table, $searchTerm, $offset, $limit, $colToSearch, $cols) use ($postTable, $categoryTable, $postToCatTable, $postInCategories) {
                if (!is_array($postInCategories)) {
                    $postInCategories = [];
                }
                $where = "WHERE post_status = 1 AND $colToSearch LIKE CONCAT('%', ?, '%') LIMIT ? OFFSET ?";
                if (is_array($postInCategories) && !empty($postInCategories)) {
                    $qMark = helper()->returnRequiredQuestionMarks($postInCategories);
                    $postInCategories[] = $searchTerm;
                    $where = "WHERE post_status = 1 AND cat_slug IN($qMark) AND $colToSearch LIKE CONCAT('%', ?, '%') LIMIT ? OFFSET ?";
                }
                $postInCategories[] = $searchTerm;
                $postInCategories[] = $limit;
                $postInCategories[] = $offset;
                return db()->run(<<<SQL
SELECT * FROM $postToCatTable 
    JOIN $postTable ON $postToCatTable.fk_post_id = $postTable.post_id
    JOIN $categoryTable ON $postToCatTable.fk_cat_id = $categoryTable.cat_id
$where
SQL, ...$postInCategories);
            },
            'customGetRowWithOffsetLimit' => function ($table, $offset, $limit, $cols) use ($postTable, $postToCatTable, $categoryTable, $postInCategories) {
                $where = "WHERE post_status = 1";
                if (is_array($postInCategories) && !empty($postInCategories)) {
                    $qMark = helper()->returnRequiredQuestionMarks($postInCategories);
                    $where = "WHERE post_status = 1 AND cat_slug IN($qMark)";
                }
                $postInCategories[] = $limit;
                $postInCategories[] = $offset;
                return  db()->run(<<<SQL
SELECT * FROM $postToCatTable
    JOIN $postTable ON $postToCatTable.fk_post_id = $postTable.post_id
    JOIN $categoryTable ON $postToCatTable.fk_cat_id = $categoryTable.cat_id
$where LIMIT ? OFFSET ?
SQL, ...$postInCategories);
            },
        ];
        $posts = $postData->generatePaginationData(
            $postData->getPostPaginationColumns(),
            'post_title',
            $postData->getPostTable(), $noOfPostPerPage, $customCallable);
        $elementName = strtolower($data->elementWrapper);
        if (!key_exists($elementName, helper()->htmlTags())) {
            $elementName = 'li';
        }
        $attributes = '';
        if (!empty($data->attributes)) {
            $attributes = $event->flatHTMLTagAttributes($data->attributes);
        }
        $showPostImage = (isset($data->showPostImage)) ? $data->showPostImage : '1';
        $frag = '';
        foreach ($posts->data as $post) {
            $image = '';
            if (!empty($post->image_url) && $showPostImage === '1'){
                $image = <<<IMAGE
                <img loading="lazy" decoding="async" src="$post->image_url" class="" alt="$post->post_title">
IMAGE;
            }
            $fieldSettings = json_decode($post->field_settings, true);
            $stripTagsContent = strip_tags($fieldSettings['post_content']);
            $summary = substr($stripTagsContent, 0, $postDescriptionLength);
            if (strlen($stripTagsContent) > $postDescriptionLength){
                $summary .="...";
            }
            $frag .= <<<HTML
 <$elementName $attributes >
    <div class="owl width:100% border-width:default border:black color:black height:100%">
        <div class="post-thumbnail">
            $image
        </div>
        <div class="text-on-admin-util padding:default owl cursor:text">
        <h4>$post->post_title</h4>
        <p style="max-width:unset;">$summary</p>
        <div class="form-group d:flex flex-gap:small">
                         <a title="$post->post_title" href="/posts/$post->slug_id/$post->post_slug" class="text-align:center text:paragraph-fluid-one text:no-wrap no-text-highlight bg:transparent border:none color:black border-width:default border:black padding:default
                            margin-top:0 cart-width cursor:pointer">$readMoreLabel</a>    
        </div>
        </div>
       <div></div>         
    </div>
 HTML;
            $frag .= <<<HTML
</$elementName>
HTML;
        }
        unset($posts->data);
        addToGlobalVariable('PostLoopData', $posts);
        return $frag;
    }
}