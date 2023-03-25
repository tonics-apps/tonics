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

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Helper\FieldHelpers;
use App\Modules\Post\Events\OnPostCategoryCreate;
use App\Modules\Post\Events\OnPostCategoryDefaultField;
use App\Modules\Post\Events\OnPostDefaultField;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

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

        $result = null;
        db(onGetDB: function ($db) use (&$result){
            $categoryTable = $this->getCategoryTable();
            // tcs stands for tonics category system ;)
            $result = $db->run("
        WITH RECURSIVE cat_recursive AS 
	( SELECT cat_id, cat_parent_id, cat_slug, cat_name, CAST(cat_slug AS VARCHAR (255))
            AS path
      FROM $categoryTable WHERE cat_parent_id IS NULL
      UNION ALL
      SELECT tcs.cat_id, tcs.cat_parent_id, tcs.cat_slug, tcs.cat_name, CONCAT(path, '/' , tcs.cat_slug)
      FROM cat_recursive as fr JOIN $categoryTable as tcs ON fr.cat_id = tcs.cat_parent_id
      ) 
     SELECT * FROM cat_recursive;
        ");
        });

        return $result;
    }

    /**
     * @param string|int $idSlug
     * @return array|bool
     * @throws \Exception
     */
    public function getChildCategoriesOfParent(string|int $idSlug): bool|array
    {

        $result = null;
        db(onGetDB: function ($db) use ($idSlug, &$result){
            $categoryTable = $this->getCategoryTable();

            $where = "cat_slug = ?";
            if (is_numeric($idSlug)) {
                $where = "cat_id = ?";
            }

            $result = $db->run("
        WITH RECURSIVE cat_recursive AS 
	( SELECT cat_id, cat_parent_id, cat_slug, cat_name, slug_id, field_settings, 
	        JSON_UNQUOTE(JSON_EXTRACT(field_settings, '$.seo_description')) AS _description,
            CAST(cat_slug AS VARCHAR (255)) AS path
      FROM {$categoryTable} WHERE $where
      UNION ALL
      SELECT tcs.cat_id, tcs.cat_parent_id, tcs.cat_slug, tcs.cat_name, tcs.slug_id, tcs.field_settings,
      JSON_UNQUOTE(JSON_EXTRACT(tcs.field_settings, '$.seo_description')) AS _description,
      CONCAT(path, '/' , tcs.cat_slug)
      FROM cat_recursive as fr JOIN {$categoryTable} as tcs ON fr.cat_id = tcs.cat_parent_id
      ) 
     SELECT * FROM cat_recursive;
        ", $idSlug);
        });

        return $result;
    }

    /**
     * @throws \Exception
     */
    public function getPostCategoryParents(string|int $idSlug)
    {
        $result = null;
        db(onGetDB: function ($db) use ($idSlug, &$result){
            $categoryTable = $this->getCategoryTable();

            $where = "cat_slug = ?";
            if (is_numeric($idSlug)) {
                $where = "cat_id = ?";
            }
            $result = $db->run("
        WITH RECURSIVE child_to_parent AS 
	( SELECT cat_id, cat_parent_id, slug_id, cat_slug, cat_status, cat_name, CAST(cat_slug AS VARCHAR (255))
            AS path
      FROM $categoryTable WHERE $where
      UNION ALL
      SELECT fr.cat_id, fr.cat_parent_id, fr.slug_id, fr.cat_slug, fr.cat_status, fr.cat_name, CONCAT(fr.cat_slug, '/', path)
      FROM $categoryTable as fr INNER JOIN child_to_parent as cp ON fr.cat_id = cp.cat_parent_id
      ) 
     SELECT *, cat_name as _name, CONCAT_WS('/', '/categories', slug_id, cat_slug) as _link FROM child_to_parent;
        ", $idSlug);
        });

        return $result;
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
     * @param null $currentCatIDS
     * @return string
     * @throws \Exception
     */
    private function getCategoryHTMLSelectFragments($category, $currentCatIDS = null): string
    {
        $currentCatIDS = (is_object($currentCatIDS) && property_exists($currentCatIDS, 'cat_parent_id')) ? $currentCatIDS->cat_parent_id : $currentCatIDS;

        if (!is_array($currentCatIDS)){
            $currentCatIDS = [$currentCatIDS];
        }

        $catSelectFrag = '';
        $catID = $category->cat_id;
        if ($category->depth === 0) {
            $catSelectFrag .= <<<CAT
    <option data-is-parent="yes" data-depth="$category->depth"
            data-slug="$category->cat_slug" data-path="/$category->path/" value="$catID"
CAT;
            foreach ($currentCatIDS as $currentCatID){
                if ($currentCatID == $category->cat_id) {
                    $catSelectFrag .= 'selected';
                }
            }

            $catSelectFrag .= ">" . $category->cat_name;
        } else {
            $catSelectFrag .= <<<CAT
    <option data-slug="$category->cat_slug" data-depth="$category->depth" data-path="/$category->path/"
            value="$catID"
CAT;
            foreach ($currentCatIDS as $currentCatID){
                if ($currentCatID == $category->cat_id) {
                    $catSelectFrag .= 'selected';
                }
            }

            $catSelectFrag .= ">" . str_repeat("&nbsp;&nbsp;&nbsp;", $category->depth + 1);
            $catSelectFrag .= $category->cat_name;
        }
        $catSelectFrag .= "</option>";

        if (isset($category->_children)) {
            foreach ($category->_children as $catChildren) {
                $catSelectFrag .= $this->getCategoryHTMLSelectFragments($catChildren, $currentCatIDS);
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
    public function createCategory(array $ignore = [], bool $prepareFieldSettings = true): array
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

        if ($prepareFieldSettings){
            return $this->getFieldData()->prepareFieldSettingsDataForCreateOrUpdate($category, 'cat_name', 'cat_content');
        }

        return $category;
    }

    /**
     * @throws \Exception
     */
    public function createPost(array $ignore = [], bool $prepareFieldSettings = true): array
    {
        # Since post_excerpt columns is a generated column, it can be in the column to return since inserting directly does not work and hence, it would throw an error
        $ignore[] = 'post_excerpt';
        $ignore[] = 'token'; # there is no such column, so we ignore

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

        if ($prepareFieldSettings){
            return $this->getFieldData()->prepareFieldSettingsDataForCreateOrUpdate($post);
        }

        return $post;
    }

    /**
     * @throws \Exception
     */
    public function insertForPost(array $data, int $type = self::Post_INT, array $return = []): bool|\stdClass
    {
        if (!key_exists($type, self::$POST_TABLES)) {
            throw new \Exception("Invalid Post Table Type");
        }

        if (empty($return)) {
            $return = $this->getCategoryColumns();
        }

        $table = Tables::getTable(self::$POST_TABLES[$type]);
        $primaryKey = 'post_id';
        if ($type === self::Category_INT){
            $primaryKey = 'cat_id';
        } elseif ($type === self::PostCategory_INT){
            $primaryKey = 'id';
        }

        $result = null;
        db(onGetDB: function ($db) use ($primaryKey, $return, $data, $table, &$result){
            $result = $db->insertReturning($table, $data, $return, $primaryKey);
        });

        return $result;
    }

    /**
     * @param $ID
     * @param string $column
     * @param callable|null $onPostData
     * If there is a callable, you get the postData in array and the role in case you wanna do anything with that
     * @return array|void
     * @throws \Exception
     */
    public function getPostByUniqueID($ID, string $column = 'slug_id', callable $onPostData = null)
    {
        # verify post column
        if (!Tables::hasColumn(Tables::POSTS, $column)){
            return [];
        }

        # Role ACCESS Key
        $role = UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_Role);

        $postData = null;
        db(onGetDB: function ($db) use ($ID, $column, $role, &$postData){
            $postTable = Tables::getTable(Tables::POSTS);
            $postToCatTable = Tables::getTable(Tables::POST_CATEGORIES);
            $categoryTable = Tables::getTable(Tables::CATEGORIES);
            $userTable = Tables::getTable(Tables::USERS);

            $postData = $db->Select(PostData::getPostTableJoiningRelatedColumns())
                ->From($postToCatTable)
                ->Join($postTable, table()->pickTable($postTable, ['post_id']), table()->pickTable($postToCatTable, ['fk_post_id']))
                ->Join($categoryTable, table()->pickTable($categoryTable, ['cat_id']), table()->pickTable($postToCatTable, ['fk_cat_id']))
                ->Join($userTable, table()->pickTable($userTable, ['user_id']), table()->pickTable($postTable, ['user_id']))
                // ->WhereEquals('post_status', 1)
                ->WhereEquals('cat_status', 1)
                ->WhereEquals(table()->pickTable($postTable, [$column]), $ID)
                # If User doesn't have read access, then, they are probably not logged in, so, we check if the post is live
                ->when(Roles::RoleHasPermission($role, Roles::getPermission(Roles::CAN_READ)) === false, function (TonicsQuery $db) use ($postTable) {
                    $db->Where(table()->pickTable($postTable, ['created_at']), '<=', helper()->date());
                })
                ->FetchFirst();
        });

        if (empty($postData) || $postData?->post_status === null){
            $postData = [];
        }

        if (isset($postData->fk_cat_id)) {
            $categories = explode(',', $postData->fk_cat_id);
            foreach ($categories as $category){
                $category = explode('::', $category);
                if (count($category) === 2){
                    $reverseCategory = array_reverse($this->getPostCategoryParents($category[0]));
                    $postData->categories[] = $reverseCategory;
                }
            }
        }

        $postData = (array)$postData;
        if ($onPostData){
            $onPostData($postData, $role);
        } else {
            return $postData;
        }

    }

    /**
     * @throws \Exception
     */
    public function setDefaultPostCategoryIfNotSet()
    {
        if (input()->fromPost()->hasValue('fk_cat_id') === false) {
            $findDefault = null;
            db(onGetDB: function (TonicsQuery $db) use (&$findDefault){
                $findDefault = $db->Select("cat_slug, cat_id")
                    ->From($this->getCategoryTable())
                    ->WhereEquals('cat_slug', 'default-category')
                    ->FetchFirst();
            });
            if (is_object($findDefault) && isset($findDefault->cat_id)) {
                $_POST['fk_cat_id'] = [$findDefault->cat_id];
                return;
            }

            $defaultCategory = [
                'cat_name' => 'Default Category',
                'cat_slug' => 'default-category',
                'cat_status' => 1,
            ];

            $returning = $this->insertForPost($defaultCategory, self::Category_INT);
            $_POST['fk_cat_id'] = [$returning->cat_id];
            $categoryCreate = new OnPostCategoryCreate($returning, $this);
            event()->dispatch($categoryCreate);
        }
    }

    /**
     * @throws \Exception
     */
    public static function getPostTableJoiningRelatedColumns($includeFieldSettingsCol = true): string
    {
        $fieldSettings = [];
        if ($includeFieldSettingsCol){
            $fieldSettings = ['field_settings'];
        }

        $postTable = Tables::getTable(Tables::POSTS);
        return  table()->pick(
                [
                    $postTable => [...['post_id', 'slug_id', 'post_title', 'post_slug', 'post_status', 'created_at', 'updated_at', 'image_url'], ...$fieldSettings],
                    Tables::getTable(Tables::USERS) => ['user_name', 'email']
                ]
            )
            . ', GROUP_CONCAT(CONCAT(cat_id, "::", cat_slug ) ) as fk_cat_id'
            . ", CONCAT('/admin/posts/', post_slug, '/edit') as _edit_link, CONCAT_WS('/', '/posts', $postTable.slug_id, post_slug) as _preview_link ";
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function getPostPaginationColumns(): string
    {
        $postTable = Tables::getTable(Tables::POSTS);
        return  table()->pick(
            [
                $postTable => ['post_id', 'slug_id', 'post_title', 'post_slug', 'post_status', 'created_at', 'updated_at', 'image_url']
            ]
        ) . ", post_excerpt AS _excerpt, 
        CONCAT_WS('/', '/posts', $postTable.slug_id, post_slug) as _link, 
        CONCAT_WS('/', '/posts', $postTable.slug_id, post_slug) as _preview_link, 
        post_title AS _name, $postTable.post_id AS _id ";
    }

    /**
     * @return string
     */
    public function getCategoryPaginationColumns(): string
    {
        return '`cat_id`, `cat_parent_id`, slug_id, `cat_name`, `cat_slug`, `created_at`, `cat_status`, `updated_at`,
        CONCAT_WS( "/", "/categories", slug_id, cat_slug ) AS `_link`, `cat_name` AS `_name`, `cat_id` AS `_id`';
    }


    public function categoryCheckBoxListing(array $categories, $selected = [], string $inputName = 'cat[]', string $type = 'radio'): string
    {
        $htmlFrag = '';
        $type = ($type !== 'radio') ? 'checkbox' : 'radio';
        $selected = array_combine($selected, $selected);

        foreach ($categories as $category) {
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
        return $htmlFrag;
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