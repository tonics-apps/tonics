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
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsRouterSystem\Exceptions\URLNotFound;
use JetBrains\PhpStorm\NoReturn;

class PostAccessView
{
    private PostData $postData;
    private array    $post     = [];
    private array    $category = [];

    public function __construct (PostData $postData)
    {
        $this->postData = $postData;
    }

    /**
     * @throws \Exception
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
     */
    public function handleCategory (): void
    {
        $uniqueSlugID = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams()[0] ?? null;
        $catSlug = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams()[1] ?? null;
        $category = null;
        db(onGetDB: function (TonicsQuery $db) use ($uniqueSlugID, &$category) {
            $category = $db->Select("*")
                ->From($this->getPostData()->getCategoryTable())->WhereEquals('slug_id', $uniqueSlugID)
                ->setPdoFetchType(\PDO::FETCH_ASSOC)->FetchFirst();
        });

        # if empty we can check with the cat_slug and do a redirection
        if (empty($category)) {
            $category = null;
            db(onGetDB: function (TonicsQuery $db) use ($catSlug, &$category) {
                $category = $db->Select("*")
                    ->From($this->getPostData()->getCategoryTable())->WhereEquals('cat_slug', $catSlug)
                    ->setPdoFetchType(\PDO::FETCH_ASSOC)->FetchFirst();
            });
            if (isset($category['slug_id'])) {
                redirect(PostRedirection::getCategoryAbsoluteURLPath($category), 302);
            }
            # if catSlug is not equals to $category['cat_slug'], do a redirection to the correct one
        } elseif (isset($category['cat_slug']) && $category['cat_slug'] !== $catSlug) {
            redirect(PostRedirection::getCategoryAbsoluteURLPath($category), 302);
        }

        if (is_array($category) && key_exists('cat_status', $category)) {
            $category['categories'][] = array_reverse($this->postData->getPostCategoryParents($category['cat_parent_id'] ?? ''));
            $catCreatedAtTimeStamp = strtotime($category['created_at']);
            if ($category['cat_status'] === 1 && time() >= $catCreatedAtTimeStamp) {
                $this->category = $category;
                return;
            }

            ## Else, category is in draft, check if user is logged in and has a read access
            $role = UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_Role);
            if (Roles::ROLE_HAS_PERMISSIONS($role, Roles::CAN_READ)) {
                $this->category = $category;
                return;
            }
        }

        throw new URLNotFound(SimpleState::ERROR_FORBIDDEN__MESSAGE, SimpleState::ERROR_FORBIDDEN__CODE);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function showPost (string $postView, $moreData = []): void
    {
        $post = $this->post;
        if (!empty($post)) {
            $catID = [];
            if (isset($post['categories'])) {
                foreach ($post['categories'] as $categories) {
                    foreach ($categories as $category) {
                        $catID[] = $category->cat_id;
                    }
                }
            }

            # GET CORRESPONDING POST IN CATEGORY
            $postTbl = Tables::getTable(Tables::POSTS);

            $relatedPost = null;
            db(onGetDB: function ($db) use ($post, $catID, $postTbl, &$relatedPost) {
                $postCatTbl = Tables::getTable(Tables::POST_CATEGORIES);
                $CatTbl = Tables::getTable(Tables::CATEGORIES);

                $tblCol = table()->pickTableExcept($postTbl, ['updated_at'])
                    . ", CONCAT_WS('/', '/posts', $postTbl.slug_id, post_slug) as _preview_link "
                    . ", $postTbl.post_excerpt AS _excerpt";

                $relatedPost = $db->Select($tblCol)
                    ->From($postCatTbl)
                    ->Join($postTbl, table()->pickTable($postTbl, ['post_id']), table()->pickTable($postCatTbl, ['fk_post_id']))
                    ->Join($CatTbl, table()->pickTable($CatTbl, ['cat_id']), table()->pickTable($postCatTbl, ['fk_cat_id']))
                    ->addRawString("WHERE MATCH(post_title) AGAINST(?)")->addParam($post['post_title'])->setLastEmittedType('WHERE')
                    ->WhereEquals('post_status', 1)
                    ->when(!empty($catID), function (TonicsQuery $db) use ($catID) {
                        $db->WhereIn('cat_id', $catID);
                    })
                    ->WhereNotIn('post_id', $post['post_id'])
                    ->Where("$postTbl.created_at", '<=', helper()->date())
                    ->OrderByDesc(table()->pickTable($postTbl, ['updated_at']))->SimplePaginate(6);
            });

            $post['related_post'] = $relatedPost;

            $this->getFieldData()->unwrapForPost($post);
            $onFieldUserForm = new OnFieldFormHelper([], $this->getFieldData());
            event()->dispatch($this->getPostData()->getOnPostDefaultField());

            # We are only interested in the hidden slug
            $slugs = $this->getPostData()->getOnPostDefaultField()->getHiddenFieldSlug();
            # MoreData can't use the _fieldDetails here
            unset($moreData['_fieldDetails']);
            # Cache Post Data
            $onFieldUserForm->handleFrontEnd($slugs, [...$post, ...$moreData]);
            view($postView);
        }

        exit();
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
}