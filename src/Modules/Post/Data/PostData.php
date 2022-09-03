<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Post\Data;

use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Modules\Post\Events\OnPostCategoryCreate;
use App\Modules\Post\Events\OnPostCategoryDefaultField;
use App\Modules\Post\Events\OnPostDefaultField;

class PostData extends AbstractDataLayer
{

    private ?FieldData $fieldData;
    private ?OnPostDefaultField $onPostDefaultField;
    private ?OnPostCategoryDefaultField $onPostCategoryDefaultField;

    public function __construct(FieldData $fieldData = null, OnPostDefaultField $onPostDefaultField = null, OnPostCategoryDefaultField $onPostCategoryDefaultField = null)
    {
        $this->fieldData = $fieldData;
        $this->onPostDefaultField = $onPostDefaultField;
        $this->onPostCategoryDefaultField = $onPostCategoryDefaultField;
    }

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
	( SELECT cat_id, cat_parent_id, cat_slug, cat_name, CAST(cat_slug AS VARCHAR (255))
            AS path
      FROM {$categoryTable} WHERE cat_parent_id IS NULL
      UNION ALL
      SELECT tcs.cat_id, tcs.cat_parent_id, tcs.cat_slug, tcs.cat_name, CONCAT(path, '/' , tcs.cat_slug)
      FROM cat_recursive as fr JOIN {$categoryTable} as tcs ON fr.cat_id = tcs.cat_parent_id
      ) 
     SELECT * FROM cat_recursive;
        ");
    }

    public function getPostStatusHTMLFrag($currentStatus = null): string
    {
        $frag = "<option value='0'" . ($currentStatus === 0 ? 'selected' : '') . ">Draft</option>";
        $frag .= "<option value='1'" . ($currentStatus === 1 ? 'selected' : '') . ">Publish</option>";

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
        if (count($categories) > 0) {
            foreach ($categories as $category) {
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
        $currentCatData = (is_object($currentCatData) && property_exists($currentCatData, 'cat_parent_id')) ? $currentCatData->cat_parent_id : $currentCatData;
        $catSelectFrag = '';
        $catID = $category->cat_id;
        if ($category->depth === 0) {
            $catSelectFrag .= <<<CAT
    <option data-is-parent="yes" data-depth="$category->depth"
            data-slug="$category->cat_slug" data-path="/$category->path/" value="$catID"
CAT;
            if (!empty($currentCatData) && $currentCatData == $category->cat_id) {
                $catSelectFrag .= 'selected';
            }
            $catSelectFrag .= ">" . $category->cat_name;
        } else {
            $catSelectFrag .= <<<CAT
    <option data-slug="$category->cat_slug" data-depth="$category->depth" data-path="/$category->path/"
            value="$catID"
CAT;
            if (!empty($currentCatData) && $currentCatData == $category->cat_id) {
                $catSelectFrag .= 'selected';
            }

            $catSelectFrag .= ">" . str_repeat("&nbsp;&nbsp;&nbsp;", $category->depth + 1);
            $catSelectFrag .= $category->cat_name;
        }
        $catSelectFrag .= "</option>";

        if (isset($category->_children)) {
            foreach ($category->_children as $catChildren) {
                $catSelectFrag .= $this->getCategoryHTMLSelectFragments($catChildren, $currentCatData);
            }
        }

        return $catSelectFrag;

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
        return Tables::$TABLES[Tables::CATEGORIES];
    }

    public function getPostColumns(): array
    {
        return Tables::$TABLES[Tables::POSTS];
    }

    public function getPostToCategoriesColumns(): array
    {
        return Tables::$TABLES[Tables::POST_CATEGORIES];
    }

    /**
     * @throws \Exception
     */
    public function createCategory(array $ignore = []): array
    {
        $slug = $this->generateUniqueSlug($this->getCategoryTable(),
            'cat_slug',
            helper()->slug(input()->fromPost()->retrieve('cat_slug')));

        $category = [];
        $categoryCols = array_flip($this->getCategoryColumns());
        if (input()->fromPost()->hasValue('cat_parent_id')) {
            $category['cat_parent_id'] = input()->fromPost()->retrieve('cat_parent_id');
        }

        foreach (input()->fromPost()->all() as $inputKey => $inputValue) {
            if (key_exists($inputKey, $categoryCols) && input()->fromPost()->has($inputKey)) {
                if ($inputKey === 'cat_parent_id' && empty($inputValue)) {
                    $category[$inputKey] = null;
                    continue;
                }

                if ($inputKey === 'created_at') {
                    $category[$inputKey] = helper()->date(datetime: $inputValue);
                    continue;
                }
                if ($inputKey === 'cat_slug') {
                    $category[$inputKey] = $slug;
                    continue;
                }
                $category[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $category);
        if (!empty($ignores)) {
            foreach ($ignores as $v) {
                unset($category[$v]);
            }
        }

        return $this->getFieldData()->prepareFieldSettingsDataForCreateOrUpdate($category, 'cat_name', 'cat_content');
    }

    /**
     * @throws \Exception
     */
    public function createPost(array $ignore = []): array
    {
        $slug = $this->generateUniqueSlug($this->getPostTable(),
            'post_slug', helper()->slug(input()->fromPost()->retrieve('post_slug')));

        $post = [];
        $postColumns = array_flip($this->getPostColumns());
        foreach (input()->fromPost()->all() as $inputKey => $inputValue) {
            if (key_exists($inputKey, $postColumns) && input()->fromPost()->has($inputKey)) {

                if ($inputKey === 'created_at') {
                    $post[$inputKey] = helper()->date(datetime: $inputValue);
                    continue;
                }
                if ($inputKey === 'post_slug') {
                    $post[$inputKey] = $slug;
                    continue;
                }
                $post[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $post);
        if (!empty($ignores)) {
            foreach ($ignores as $v) {
                unset($post[$v]);
            }
        }

        return $this->getFieldData()->prepareFieldSettingsDataForCreateOrUpdate($post);
    }

    /**
     * @throws \Exception
     */
    public function insertForPost(array $data, int $type = PostData::Post_INT, array $return = []): bool|\stdClass
    {
        if (!key_exists($type, self::$POST_TABLES)) {
            throw new \Exception("Invalid Post Table Type");
        }

        if (empty($return)) {
            $return = $this->getCategoryColumns();
        }

        $table = Tables::getTable(self::$POST_TABLES[$type]);
        return db()->insertReturning($table, $data, $return);
    }


    /**
     * @throws \Exception
     */
    public function getPostCategoryParents(string|int $idSlug)
    {
        $categoryTable = $this->getCategoryTable();

        $where = "cat_slug = ?";
        if (is_numeric($idSlug)) {
            $where = "cat_id = ?";
        }
        return db()->run("
        WITH RECURSIVE child_to_parent AS 
	( SELECT cat_id, cat_parent_id, slug_id, cat_slug, cat_name, CAST(cat_slug AS VARCHAR (255))
            AS path
      FROM $categoryTable WHERE $where
      UNION ALL
      SELECT fr.cat_id, fr.cat_parent_id, fr.slug_id, fr.cat_slug, fr.cat_name, CONCAT(fr.cat_slug, '/', path)
      FROM $categoryTable as fr INNER JOIN child_to_parent as cp ON fr.cat_id = cp.cat_parent_id
      ) 
     SELECT * FROM child_to_parent;
        ", $idSlug);
    }

    /**
     * You should get an array
     * @param $uniqueID
     * @return array|mixed
     * @throws \Exception
     */
    public function getPostByUniqueID($uniqueID): mixed
    {
        $postTable = Tables::getTable(Tables::POSTS);
        $postToCatTable = Tables::getTable(Tables::POST_CATEGORIES);
        $categoryTable = Tables::getTable(Tables::CATEGORIES);
        $userTable = Tables::getTable(Tables::USERS);

        $sql = <<<SQL
SELECT *, 
       $postTable.created_at as 'published_time', 
       $postTable.updated_at as 'modified_time', 
       $postTable.field_settings as 'field_settings', 
       $postTable.slug_id as post_slug_id, 
       $categoryTable.slug_id as cat_slug_id
    FROM $postToCatTable 
    JOIN $postTable ON $postToCatTable.fk_post_id = $postTable.post_id
    JOIN $userTable ON $userTable.user_id = $postTable.user_id
    JOIN $categoryTable ON $postToCatTable.fk_cat_id = $categoryTable.cat_id
WHERE $postTable.slug_id = ?
SQL;

        $stmt = db()->prepare($sql);
        $stmt->execute([$uniqueID]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        unset($data['user_password']);

        if (isset($data['cat_id'])) {
            $data['categories'] = $this->getPostCategoryParents($data['cat_id']);
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

        if ($colToSelect === ['*']) {
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
        if ($colToSelect === ['*']) {
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
        if (input()->fromPost()->hasValue('fk_cat_id') === false) {
            $findDefault = $this->selectWithConditionFromCategory(['cat_slug', 'cat_id'], "cat_slug = ?", ['default-category']);
            if (is_object($findDefault) && isset($findDefault->cat_id)) {
                $_POST['fk_cat_id'] = $findDefault->cat_id;
                return;
            }

            $defaultCategory = [
                'cat_name' => 'Default Category',
                'cat_slug' => 'default-category',
                'cat_status' => 1,
            ];

            $returning = $this->insertForPost($defaultCategory, self::Category_INT);
            $_POST['fk_cat_id'] = $returning->cat_id;
            $onPostCategoryCreate = new OnPostCategoryCreate($returning, $this);
            event()->dispatch($onPostCategoryCreate);
        }
    }

    /**
     * @return string
     */
    public function getPostPaginationColumns(): string
    {
        return '`post_id`, `slug_id`, `post_title`, `post_slug`, `post_status`, `user_id`, `created_at` AS `post_created_at`, `updated_at`,
        CONCAT_WS( "/", "/posts", slug_id, post_slug ) AS `_link`, `post_title` AS `_name`, `post_id` AS `_id`';
    }

    /**
     * @return string
     */
    public function getCategoryPaginationColumns(): string
    {
        return '`cat_id`, `cat_parent_id`, slug_id, `cat_name`, `cat_slug`, `created_at`, `cat_status`, `updated_at`,
        CONCAT_WS( "/", "/categories", slug_id, cat_slug ) AS `_link`, `cat_name` AS `_name`, `cat_id` AS `_id`';
    }

    /**
     * @throws \Exception
     */
    public function getCategoriesPaginationData(): ?object
    {
        $settings = [
            'query_name' => 'cat_query',
            'page_name' => 'cat_page',
            'per_page_name' => 'cat_per_page',
        ];
        return $this->generatePaginationData($this->getCategoryPaginationColumns(), 'cat_name', $this->getCategoryTable(), 200, $settings);
    }

    /**
     * @param object|null $categories
     * @return void
     * @throws \Exception
     */
    public function categoryMetaBox(?object $categories)
    {
        if (url()->getHeaderByKey('menuboxname') === 'category') {
            if (url()->getHeaderByKey('action') === 'more') {
                $frag = $this->categoryCheckBoxListing($categories);
                helper()->onSuccess($frag);
            }
        }
    }

    public function categoryCheckBoxListing(?object $categories, $selected = [], string $inputName = 'cat[]', string $type = 'radio'): string
    {
        $htmlFrag = '';
        $htmlMoreFrag = '';
        $type = ($type !== 'radio') ? 'checkbox' : 'radio';
        $selected = array_combine($selected, $selected);

        if (isset($categories->data) && is_array($categories->data) && !empty($categories->data)) {

            foreach ($categories->data as $category) {
                $id = 'category' . $category->cat_id . '_' . $category->cat_slug;
                if (key_exists($category->cat_id, $selected)) {
                    $htmlFrag .= <<<HTML
<li class="menu-item">
    <input type="$type"
    id="$id" checked="checked" name="$inputName" value="{$category->cat_id}">
    <label for="$id">{$category->cat_name}</label>
</li>
HTML;
                    continue;
                }
                $htmlFrag .= <<<HTML
<li class="menu-item">
    <input type="$type"
    id="$id" name="$inputName" value="{$category->cat_id}">
    <label for="$id">{$category->cat_name}</label>
</li>
HTML;
            }

            # MORE BUTTON
            if (isset($categories->has_more) && $categories->has_more) {
                $htmlMoreFrag = <<<HTML
 <button 
 data-morepageUrl="$categories->next_page_url" 
 data-menuboxname = "category"
 data-nextpageid="$categories->next_page"
 data-action = "more"
 class="border:none bg:white-one border-width:default border:black padding:gentle margin-top:0 cursor:pointer act-like-button more-button">More →</button>
HTML;
            }
        }
        return $htmlFrag . $htmlMoreFrag;
    }

    /**
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->fieldData;
    }

    /**
     * @param FieldData $fieldData
     */
    public function setFieldData(FieldData $fieldData): void
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @return OnPostDefaultField|null
     */
    public function getOnPostDefaultField(): ?OnPostDefaultField
    {
        return $this->onPostDefaultField;
    }

    /**
     * @param OnPostDefaultField|null $onPostDefaultField
     */
    public function setOnPostDefaultField(?OnPostDefaultField $onPostDefaultField): void
    {
        $this->onPostDefaultField = $onPostDefaultField;
    }

    /**
     * @return OnPostCategoryDefaultField|null
     */
    public function getOnPostCategoryDefaultField(): ?OnPostCategoryDefaultField
    {
        return $this->onPostCategoryDefaultField;
    }

    /**
     * @param OnPostCategoryDefaultField|null $onPostCategoryDefaultField
     */
    public function setOnPostCategoryDefaultField(?OnPostCategoryDefaultField $onPostCategoryDefaultField): void
    {
        $this->onPostCategoryDefaultField = $onPostCategoryDefaultField;
    }

}