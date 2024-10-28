<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Modules\Post\RequestInterceptor;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnFieldFormHelper;
use App\Modules\Post\Data\PostData;
use App\Modules\Post\Helper\PostRedirection;
use App\Modules\Post\Services\PostService;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsRouterSystem\Exceptions\URLNotFound;
use JetBrains\PhpStorm\NoReturn;

class PostAccessView
{
    const POST_PAGE_TEMPLATE     = ['ninetysevenpostpagetemplate'];
    const CATEGORY_PAGE_TEMPLATE = ['ninetysevencategorypagetemplate'];

    private PostData   $postData;
    private array      $post     = [];
    private ?\stdClass $category = null;

    public function __construct (PostData $postData)
    {
        $this->postData = $postData;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handlePost (): void
    {
        $uniqueSlugID = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams()[0] ?? null;
        $postSlug = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams()[1] ?? null;
        $this->getPostData()->getPostByUniqueID($uniqueSlugID, 'slug_id', function ($postData, $role) use ($postSlug) {
            # if empty we can check with the post_slug and do a redirection
            if (empty($postData)) {
                $postData = (array)$this->getPostData()->getPostByUniqueID($postSlug, 'post_slug');
                if (isset($postData['slug_id'])) {
                    redirect(PostRedirection::getPostAbsoluteURLPath($postData), 302);
                }
                # if postSlug is not equals to $post['post_slug'], do a redirection to the correct one
            } elseif (isset($postData['post_slug']) && $postData['post_slug'] !== $postSlug) {
                redirect(PostRedirection::getPostAbsoluteURLPath($postData), 302);
            }

            if (!empty($postData)) {
                $postData['field_settings'] = json_decode($postData['field_settings']);
                $postData['field_settings']->_fieldDetails = json_decode($postData['field_settings']?->_fieldDetails ?? '');
                $postData['field_settings']->post_content = json_decode($postData['field_settings']->post_content) ?? $postData['field_settings']->post_content ?? null;
                $postData['payload_settings'] = (object)[
                    'fieldDetails' => $postData['field_settings']->_fieldDetails,
                    'post_content' => $postData['field_settings']->post_content,
                ];
            }

            if (Roles::ROLE_HAS_PERMISSIONS($role, Roles::CAN_READ) === false) {
                if (key_exists('post_status', $postData)) {
                    if ($postData['post_status'] === 1) {
                        $this->post = $postData;
                        return;
                    }
                }
                throw new URLNotFound(SimpleState::ERROR_FORBIDDEN__MESSAGE, SimpleState::ERROR_FORBIDDEN__CODE);
            } else {
                $this->post = $postData;
            }
        });
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handleCategory (): void
    {
        $routeParams = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams();
        $uniqueSlugID = $routeParams[0] ?? null;
        $catSlug = $routeParams[1] ?? null;

        $category = PostService::QueryLoopCategory([
            PostService::QUERY_LOOP_SETTINGS_CATEGORY_FIELD_NAME => 'slug_id',
            PostService::QUERY_LOOP_SETTINGS_PAGINATION_PER_PAGE => 1,
            PostService::QUERY_LOOP_SETTINGS_CATEGORY_IN         => $uniqueSlugID,
        ]);

        # If we can find the category by slug_id, try with cat_slug
        if (!PostService::QueryHasData($category)) {
            $category = PostService::QueryLoopCategory([
                PostService::QUERY_LOOP_SETTINGS_CATEGORY_FIELD_NAME => 'cat_slug',
                PostService::QUERY_LOOP_SETTINGS_PAGINATION_PER_PAGE => 1,
                PostService::QUERY_LOOP_SETTINGS_CATEGORY_IN         => $catSlug,
            ]);
        }

        if (PostService::QueryHasData($category)) {

            $category = PostService::GrabQueryData($category, true);
            $category->{'category_id_trail'} = $this->getCategoryTrail($category);
            $category->{'field_settings'} = json_decode($category->{'field_settings'});

            if (isset($category->{'field_settings'}->_fieldDetails)) {
                $category->{'field_settings'}->_fieldDetails = json_decode($category->{'field_settings'}->_fieldDetails);
            }

            $category->{'field_settings'}->post_content = json_decode($category->{'field_settings'}->cat_content);
            $category->{'payload_settings'} = (object)[
                'fieldDetails' => $category->{'field_settings'}->_fieldDetails ?? [],
                'post_content' => $category->{'field_settings'}->post_content,
            ];

            # Redirect if category exists but cat_slug doesn't match
            if (isset($category->{'cat_slug'}) && $category->{'cat_slug'} !== $catSlug) {
                redirect(PostRedirection::getCategoryAbsoluteURLPath($category), 302);
            }

            # Handle category based on its status and creation time
            if ($this->isValidCategory($category)) {
                $this->category = $category;
                return;
            }

            # Check if the user has read access for draft categories
            if ($this->userHasReadAccess()) {
                $this->category = $category;
                return;
            }

        }

        throw new URLNotFound(SimpleState::ERROR_FORBIDDEN__MESSAGE, SimpleState::ERROR_FORBIDDEN__CODE);
    }

    /**
     * @param $category
     *
     * @return bool
     * @throws \Exception
     */
    private function isValidCategory ($category): bool
    {
        if (property_exists($category, 'cat_status')) {
            $category->{'categories'}[] = array_reverse($this->postData->getPostCategoryParents($category->{'cat_parent_id'} ?? ''));
            $catCreatedAtTimeStamp = strtotime($category->{'created_at'});
            return $category->{'cat_status'} === 1 && time() >= $catCreatedAtTimeStamp;
        }

        return false;
    }

    /**
     * Check if the current user has read access to draft categories.
     * @throws \Exception
     */
    private function userHasReadAccess (): bool
    {
        $role = UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_Role);
        return Roles::ROLE_HAS_PERMISSIONS($role, Roles::CAN_READ);
    }

    /**
     * @param $category
     *
     * @return string
     * @throws \Exception
     */
    public function getCategoryTrail ($category): string
    {
        $catIDSResult = $this->getPostData()->getChildCategoriesOfParent($category->{'cat_id'});

        $ids = [];
        foreach ($catIDSResult as $catID) {
            $ids[] = $catID->cat_id;
        }

        return implode(',', $ids);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function showCategory (string $postView, $moreData = []): void
    {
        $category = $this->category;
        if (!empty($category)) {

            # GET CORRESPONDING POST IN CATEGORY
            $postTbl = Tables::getTable(Tables::POSTS);

            $postData = [];
            try {
                $catIDSResult = $this->getPostData()->getChildCategoriesOfParent($category['cat_id']);
                $catIDS = [];
                foreach ($catIDSResult as $catID) {
                    $catIDS[] = $catID->cat_id;
                }

                $postData = null;
                db(onGetDB: function (TonicsQuery $db) use ($catIDS, $postTbl, &$postData) {
                    $postCatTbl = Tables::getTable(Tables::POST_CATEGORIES);
                    $CatTbl = Tables::getTable(Tables::CATEGORIES);
                    $tblCol = table()->pickTableExcept($postTbl, ['updated_at'])
                        . ", CONCAT_WS('/', '/posts', $postTbl.slug_id, post_slug) as _preview_link "
                        . ", $postTbl.post_excerpt AS _excerpt";

                    $postData = $db->Select($tblCol)
                        ->From($postCatTbl)
                        ->Join($postTbl, table()->pickTable($postTbl, ['post_id']), table()->pickTable($postCatTbl, ['fk_post_id']))
                        ->Join($CatTbl, table()->pickTable($CatTbl, ['cat_id']), table()->pickTable($postCatTbl, ['fk_cat_id']))
                        ->WhereEquals('post_status', 1)
                        ->WhereIn('cat_id', $catIDS)
                        ->Where("$postTbl.created_at", '<=', helper()->date())
                        ->OrderByDesc(table()->pickTable($postTbl, ['created_at']))->SimplePaginate(AppConfig::getAppPaginationMax());
                });

                $postData = ['PostData' => $postData, 'CategoryData' => $catIDSResult];

            } catch (\Exception $exception) {
                // log..
            }

            $fieldSettings = json_decode($category['field_settings'], true);
            $this->getFieldData()->unwrapFieldContent($fieldSettings, contentKey: 'cat_content');
            $category = [...$fieldSettings, ...$category];

            $date = new \DateTime($category['created_at']);
            $category['created_at_words'] = strtoupper($date->format('j M, Y'));
            $onFieldUserForm = new OnFieldFormHelper([], $this->getFieldData());

            event()->dispatch($this->getPostData()->getOnPostCategoryDefaultField());
            $slugs = $this->getPostData()->getOnPostCategoryDefaultField()->getHiddenFieldSlug();

            # MoreData can't use the _fieldDetails here
            unset($moreData['_fieldDetails']);
            $dataBundle = [...$category, ...$moreData, ...$postData];
            $onFieldUserForm->handleFrontEnd($slugs, $dataBundle);
            view($postView, $dataBundle);
        }

        exit();
    }

    /**
     * @return PostData
     */
    public function getPostData (): PostData
    {
        return $this->postData;
    }

    /**
     * @param PostData $postData
     */
    public function setPostData (PostData $postData): void
    {
        $this->postData = $postData;
    }

    /**
     * @return FieldData
     */
    public function getFieldData (): FieldData
    {
        return $this->getPostData()->getFieldData();
    }

    public function getPost (): array
    {
        return $this->post;
    }

    public function setPost (array $post): void
    {
        $this->post = $post;
    }

    public function getCategory (): ?\stdClass
    {
        return $this->category;
    }

    public function setCategory (?\stdClass $category): PostAccessView
    {
        $this->category = $category;
        return $this;
    }

}