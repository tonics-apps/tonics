<?php

namespace App\Modules\Post\Data;

use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\Tables;

class PostData extends AbstractDataLayer
{
    use UniqueSlug;

    const Post_INT = 1;
    const Category_INT = 2;
    const PostCategory_INT = 3;

    const Post_STRING = 'posts';
    const Category_STRING = 'categories';
    const PostCategory_STRING = 'post_categories';

    static array $POST_TABLES = [
        self::Post_INT => self::Post_STRING,
        self::Category_INT => self::Category_STRING,
        self::PostCategory_INT => self::PostCategory_STRING,
    ];

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getCategory(): mixed
    {
        $categoryTable = $this->getCategoryTable();
        // tcs stands for tonics category system ;)
        return db()->run("
        WITH RECURSIVE cat_recursive AS 
	( SELECT cat_id, cat_parent_id, cat_slug, `cat_url_slug`, cat_content, cat_name, CAST(cat_slug AS VARCHAR (255))
            AS path
      FROM {$categoryTable} WHERE cat_parent_id IS NULL
      UNION ALL
      SELECT tcs.cat_id, tcs.cat_parent_id, tcs.cat_slug, tcs.cat_url_slug, tcs .cat_content, tcs.cat_name, CONCAT(path, '/' , tcs.cat_slug)
      FROM cat_recursive as fr JOIN {$categoryTable} as tcs ON fr.cat_id = tcs.cat_parent_id
      ) 
     SELECT * FROM cat_recursive;
        ");
    }

    public function getPostStatusHTMLFrag($currentStatus = null): string
    {
        $frag = "<option value='0'".  ($currentStatus === 0 ? 'selected' : '') . ">Draft</option>";
        $frag .= "<option value='1'".  ($currentStatus === 1 ? 'selected' : '') . ">Publish</option>";

        return $frag;
    }

    /**
     * @param null $currentCatData
     * @return string
     * @throws \Exception
     */
    public function getCategoryHTMLSelect($currentCatData = null): string
    {
        $categories = helper()->generateTree(['parent_id' => 'cat_parent_id', 'id' => 'cat_id'], $this->getCategory());
        $catSelectFrag = '';
        if (count($categories) > 0){
            foreach ($categories as $category){
                $catSelectFrag .= $this->getCategoryHTMLSelectFragments($category, $currentCatData);
            }
        }

        return $catSelectFrag;
    }

    /**
     * @param $category
     * @param null $currentCatData
     * @param $type
     * @return string
     * @throws \Exception
     */
    private function getCategoryHTMLSelectFragments($category, $currentCatData = null): string
    {
        $catSelectFrag = '';
        $catID =  $category->cat_id;
        if ($category->depth === 0){
            $catSelectFrag .= <<<CAT
    <option data-is-parent="yes" data-depth="$category->depth"
            data-slug="$category->cat_slug" data-path="/$category->path/" value="$catID"
CAT;
            if(!empty($currentCatData) && $currentCatData == $category->cat_id){
                $catSelectFrag .= 'selected';
            }
            $catSelectFrag .=">" . $category->cat_name;
        } else {
            $catSelectFrag .= <<<CAT
    <option data-slug="$category->cat_slug" data-depth="$category->depth" data-path="/$category->path/"
            value="$catID"
CAT;
            if(!empty($currentCatData) && $currentCatData == $category->cat_id){
                $catSelectFrag .= 'selected';
            }

            $catSelectFrag .= ">" . str_repeat("&nbsp;&nbsp;&nbsp;", $category->depth + 1);
            $catSelectFrag .= $category->cat_name;
        }
        $catSelectFrag .= "</option>";

        if (isset($category->_children)){
            foreach ($category->_children as $catChildren){
                $catSelectFrag .= $this->getCategoryHTMLSelectFragments($catChildren, $currentCatData);
            }
        }

        return $catSelectFrag;

    }

    /**
     * @throws \Exception
     */
    public function adminPostListing(array $posts, int|null $status = 1): string
    {
        $csrfToken = session()->getCSRFToken();
        $htmlFrag = '';
        foreach ($posts as $k => $post){
            if ($post->post_status === $status || $status === null){
                $isDraft = ($post->post_status === 0) ?  'Draft' : 'Published';
                $catURLSlug = (empty($post->cat_url_slug) ? '~' : $post->cat_url_slug);
                $postTitle = helper()->htmlSpecChar($post->post_title);
                if ($post->post_status === -1){
                    $otherFrag = <<<HTML
<form method="post" class="d:contents" action="/admin/posts/$post->post_slug/delete">
   <input type="hidden" name="token" value="$csrfToken">
       <button data-click-onconfirmdelete="true" type="button" class="listing-button bg:pure-black color:white border:none border-width:default border:black padding:gentle
        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Delete
        </button>
</form>
HTML;
                }
             else {
                $otherFrag = <<<HTML
<form method="post" class="d:contents" action="/admin/posts/$post->post_slug/trash">
   <input type="hidden" name="token" value="$csrfToken" >
       <button data-click-onconfirmtrash="true" type="button" class="listing-button bg:pure-black color:white border:none border-width:default border:black padding:gentle
        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Trash
        </button>
</form>
HTML;
            }
                $htmlFrag .=<<<HTML
    <li 
    data-list_id="$k" data-id="$post->post_id"  data-post_id="$post->post_id" data-cat_url_slug="$catURLSlug" 
    data-post_slug="$post->post_slug" data-post_title="$postTitle"
    data-db_click_link="/admin/posts/$post->post_slug/edit"
    data-user_id="$post->user_id"
    tabindex="0" 
    class="admin-widget-item-for-listing d:flex flex-d:column align-items:center justify-content:center cursor:pointer no-text-highlight">
        <fieldset class="padding:default width:100% min-height:300 box-shadow-variant-1 draggable d:flex justify-content:center">
            <legend class="bg:pure-black color:white padding:default">$catURLSlug $isDraft</legend>
            <div class="admin-widget-information owl width:100%">
            <div class="text-on-admin-util text-highlight">$post->post_title</div>
         
                <div class="form-group d:flex flex-gap:small">
                     <a href="/admin/posts/$post->post_slug/edit" class="text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:default
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Edit</a>
                        
                      <a href="/posts/$post->slug_id/$post->post_slug" target="_blank"
                      class="text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:default
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Preview</a>
                  
                   $otherFrag
                </div>
                
            </div>
        </fieldset>
    </li>
HTML;
            }
        }

        return $htmlFrag;
    }

    /**
     * @throws \Exception
     */
    public function adminCategoryListing(array $categories): string
    {
        $csrfToken = session()->getCSRFToken();
        $htmlFrag = '';
        foreach ($categories as $k => $category){
            if ($category->cat_status >= 0){
                $catURLSlug = (empty($category->cat_url_slug) ? '~' : $category->cat_url_slug);
                $htmlFrag .=<<<HTML
    <li 
    data-db_click_link="/admin/posts/category/$category->cat_slug/edit"
    data-list_id="$k" data-id="$category->cat_id" 
    data-cat_id="$category->cat_id" 
    data-cat_name="$category->cat_name" 
    data-cat_slug="$category->cat_slug" 
    data-cat_url_slug="$catURLSlug"
    tabindex="0" 
    class="admin-widget-item-for-listing d:flex flex-d:column align-items:center justify-content:center cursor:pointer no-text-highlight">
        <fieldset class="padding:default width:100% min-height:300 box-shadow-variant-1 d:flex justify-content:center">
            <legend class="bg:pure-black color:white padding:default">$catURLSlug</legend>
            <div class="admin-widget-information owl width:100%">
            <div class="text-on-admin-util text-highlight">$category->cat_name</div>
         
                <div class="form-group d:flex flex-gap:small">
                     <a href="/admin/posts/category/$category->cat_slug/edit" type="submit" class="text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:default
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Edit</a>
                   
                   <form method="post" class="d:contents" action="/admin/posts/category/$category->cat_slug/trash">
                    <input type="hidden" name="token" value="$csrfToken" >
                       <button data-click-onconfirmtrash="true" type="button" class="bg:pure-black border:none border-width:default border:black padding:default
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Trash</button>
                    </form>
                </div>
                
            </div>
        </fieldset>
    </li>
HTML;
            }
        }

        return $htmlFrag;
    }


    /**
     * @return string
     */
    public function getCategoryTable(): string
    {
        return Tables::getTable(Tables::CATEGORIES);
    }

    public function getPostTable(): string
    {
        return Tables::getTable(Tables::POSTS);
    }

    public function getPostToCategoryTable(): string
    {
        return Tables::getTable(Tables::POST_CATEGORIES);
    }

    public function getCategoryColumns(): array
    {
        return [
                'cat_id', 'cat_parent_id', 'cat_name', 'cat_slug', 'cat_status',
                'cat_url_slug', 'cat_content', 'created_at', 'updated_at'
            ];
    }

    public function getPostColumns(): array
    {
        return [
            'post_id', 'slug_id', 'user_id', 'post_title', 'field_ids',
            'post_slug', 'image_url', 'post_status', 'field_settings',
            'created_at', 'updated_at'
        ];
    }

    public function getPostToCategoriesColumns(): array
    {
        return [
            'id', 'fk_cat_id', 'fk_post_id', 'created_at', 'updated_at'
        ];
    }

    /**
     * @throws \Exception
     */
    public function createCategory(array $ignore = []): array
    {
        $slug = $this->generateUniqueSlug($this->getCategoryTable(),
            'cat_slug',
            helper()->slug(input()->fromPost()->retrieve('cat_slug')));
        $catUrlSlug = filter_var(input()->fromPost()->retrieve('cat_url_slug'), FILTER_SANITIZE_URL);
        $catUrlSlug = preg_replace("#//+#", "\\1/", $catUrlSlug);

        $category = []; $categoryCols = array_flip($this->getCategoryColumns());
        if (input()->fromPost()->hasValue('cat_parent_id')){
            $category['cat_parent_id'] = input()->fromPost()->retrieve('cat_parent_id');
        }

        foreach (input()->fromPost()->all() as $inputKey => $inputValue){
            if (key_exists($inputKey, $categoryCols) && input()->fromPost()->has($inputKey)){
                if ($inputKey === 'cat_parent_id' && empty($inputValue)){
                    $category[$inputKey] = null;
                    continue;
                }

                if($inputKey === 'created_at'){
                    $category[$inputKey] = helper()->date(timestamp: $inputValue);
                    continue;
                }
                if ($inputKey === 'cat_slug'){
                    $category[$inputKey] = $slug;
                    continue;
                }
                if ($inputKey === 'cat_url_slug'){
                    $category[$inputKey] = $catUrlSlug;
                    continue;
                }
                $category[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $category);
        if (!empty($ignores)){
            foreach ($ignores as $v){
                unset($category[$v]);
            }
        }

        return $category;
    }

    /**
     * @throws \Exception
     */
    public function createPost(array $ignore = []): array
    {
        $slug = $this->generateUniqueSlug($this->getPostTable(),
            'post_slug', helper()->slug(input()->fromPost()->retrieve('post_slug')));

        $_POST['field_settings'] = input()->fromPost()->all();
        unset($_POST['field_settings']['token']);

        $post = []; $postColumns = array_flip($this->getPostColumns());
        foreach (input()->fromPost()->all() as $inputKey => $inputValue){
            if (key_exists($inputKey, $postColumns) && input()->fromPost()->has($inputKey)){

                if($inputKey === 'created_at'){
                    $post[$inputKey] = helper()->date(timestamp: $inputValue);
                    continue;
                }
                if ($inputKey === 'post_slug'){
                    $post[$inputKey] = $slug;
                    continue;
                }
                $post[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $post);
        if (!empty($ignores)){
            foreach ($ignores as $v){
                unset($post[$v]);
            }
        }

        if (isset($post['field_ids'])){
            $post['field_ids'] = array_values(array_flip(array_flip($post['field_ids'])));
            $post['field_ids'] = json_encode($post['field_ids']);
        }

        if (isset($post['field_settings'])){
            $post['field_settings'] = json_encode($post['field_settings']);
            if (isset($post['field_ids'])){
                $_POST['field_settings']['field_ids'] = $post['field_ids'];
            }
        }

        return $post;
    }

    /**
     * @throws \Exception
     */
    public function insertForPost(array $data, int $type = PostData::Post_INT, array $return = []): bool|\stdClass
    {
        if (!key_exists($type, self::$POST_TABLES)){
            throw new \Exception("Invalid Post Table Type");
        }

        if (empty($return)){
            $return = $this->getCategoryColumns();
        }

        $table = Tables::getTable(self::$POST_TABLES[$type]);
        return db()->insertReturning($table, $data, $return);
    }

    public function singlePost($slugNumericID)
    {
        $postTable = Tables::getTable(Tables::POSTS);
        $postToCatTable = Tables::getTable(Tables::POST_CATEGORIES);
        $categoryTable = Tables::getTable(Tables::CATEGORIES);
        $userTable = Tables::getTable(Tables::USERS);

        $data = db()->row(<<<SQL
SELECT * FROM $postToCatTable 
    JOIN $postTable ON $postToCatTable.fk_post_id = $postTable.post_id
    JOIN $userTable ON $userTable.user_id = $postTable.user_id
    JOIN $categoryTable ON $postToCatTable.fk_cat_id = $categoryTable.cat_id
WHERE slug_id = ?
SQL, $slugNumericID);
        if (isset($data->user_password)){
            unset($data->user_password);
        }

        return $data;
    }

    /**
     * Usage:
     * <br>
     * `$newUserData->selectWithCondition(['cat_id', 'cat_content'], "cat_slug = ?", ['slug-1']));`
     *
     * Note: Make sure you use a question-mark(?) in place u want a user input and pass the actual input in the $parameter
     * @param array $colToSelect
     * To select all, use ['*']
     * @param string $whereCondition
     * @param array $parameter
     * @return mixed
     * @throws \Exception
     */
    public function selectWithConditionFromCategory(array $colToSelect, string $whereCondition, array $parameter): mixed
    {
        $select = helper()->returnDelimitedColumnsInBackTick($colToSelect);
        $table = Tables::getTable(Tables::CATEGORIES);

        if ($colToSelect === ['*']){
            return db()->row(<<<SQL
SELECT * FROM $table WHERE $whereCondition
SQL, ...$parameter);
        }

        return db()->row(<<<SQL
SELECT $select FROM $table WHERE $whereCondition
SQL, ...$parameter);
    }

    /**
     * Usage:
     * <br>
     * `$newUserData->selectWithCondition(['post_id', 'post_content'], "slug_id = ?", ['5475353']));`
     *
     * Note: Make sure you use a question-mark(?) in place u want a user input and pass the actual input in the $parameter
     * @param array $colToSelect
     * To select all, use ['*']
     * @param string $whereCondition
     * @param array $parameter
     * @return mixed
     * @throws \Exception
     */
    public function selectWithConditionFromPost(array $colToSelect, string $whereCondition, array $parameter): mixed
    {
        $select = helper()->returnDelimitedColumnsInBackTick($colToSelect);
        $postTable = Tables::getTable(Tables::POSTS);
        $postToCatTable = Tables::getTable(Tables::POST_CATEGORIES);

        // Instead of selecting from $postTable, I started the selection from $postToCatTable,
        // this way, it would replace the column that is same from $postToCatTable in $postTable.
        // we do not wanna use the created_at or updated_at of the $postToCatTable
        if ($colToSelect === ['*']){
            return db()->row(<<<SQL
SELECT * FROM $postToCatTable JOIN $postTable ON $postToCatTable.fk_post_id = $postTable.post_id WHERE $whereCondition
SQL, ...$parameter);
        }

        return db()->row(<<<SQL
SELECT $select FROM $postToCatTable JOIN $postTable ON $postToCatTable.fk_post_id = $postTable.post_id WHERE $whereCondition
SQL, ...$parameter);
    }

    /**
     * @throws \Exception
     */
    public function setDefaultPostCategoryIfNotSet()
    {
        if (input()->fromPost()->hasValue('fk_cat_id') === false){
            $findDefault = $this->selectWithConditionFromCategory(['cat_slug', 'cat_id'], "cat_slug = ?", ['default-category']);
            if (is_object($findDefault) && isset($findDefault->cat_id)){
                $_POST['fk_cat_id'] = $findDefault->cat_id;
                return;
            }

            $defaultCategory = [
                'cat_name' => 'Default Category',
                'cat_slug' => 'default-category',
                'cat_url_slug' => '',
                'cat_content' => '',
                'cat_status' => 1,
            ];

            $returning = $this->insertForPost($defaultCategory, self::Category_INT);
            $_POST['fk_cat_id'] = $returning->cat_id;
        }
    }

    /**
     * @return string
     */
    public function getPostPaginationColumns(): string
    {
        return '`post_id`, `slug_id`, `post_title`, `post_slug`, `post_status`, `user_id`, `created_at`, `updated_at`,
        CONCAT_WS( "/", "posts", slug_id, post_slug ) AS `_link`, `post_title` AS `_name`, `post_id` AS `_id`';
    }

    /**
     * @return string
     */
    public function getCategoryPaginationColumns(): string
    {
        return '`cat_id`, `cat_parent_id`, `cat_name`, `cat_slug`, `cat_url_slug`, `created_at`, `cat_status`, `updated_at`,
        CONCAT_WS( "/", "posts/category", cat_slug ) AS `_link`, `cat_name` AS `_name`, `cat_id` AS `_id`';
    }

}