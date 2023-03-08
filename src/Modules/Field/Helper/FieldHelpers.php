<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\Helper;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class FieldHelpers
{
    /**
     * @param $childrenFieldItems
     * @param string $orderByName
     * @param array $settings
     * - to search by post_title, add `search` key and a value of true,
     * <br>
     * - to search by cat_id, add `cat_id` key and a value of true,
     * @return object|null
     * @throws \Exception
     */
    public static function postDataFromPostQueryBuilderField($childrenFieldItems, string $orderByName = 'updated_at', array $settings = []): ?object
    {
        $postTbl = Tables::getTable(Tables::POSTS);
        $postCatTbl = Tables::getTable(Tables::POST_CATEGORIES);
        $CatTbl = Tables::getTable(Tables::CATEGORIES);

        $tblCol = table()->pick([$postTbl => ['post_id', 'post_title', 'slug_id', 'post_slug', 'field_settings', 'created_at', 'updated_at', 'image_url']])
            . ', CONCAT(cat_id, "::", cat_slug ) as fk_cat_id, CONCAT_WS("/", "/posts", post_slug) as _preview_link, post_title as _title '
            . ", post_excerpt AS _excerpt"
            . ", DATE_FORMAT($postTbl.updated_at, '%a, %d %b %Y %T') as rssPubDate";

        $db = db()->Select($tblCol)
            ->From($postCatTbl)
            ->Join($postTbl, table()->pickTable($postTbl, ['post_id']), table()->pickTable($postCatTbl, ['fk_post_id']))
            ->Join($CatTbl, table()->pickTable($CatTbl, ['cat_id']), table()->pickTable($postCatTbl, ['fk_cat_id']))
            ->WhereEquals('post_status', 1)
            ->Where("$postTbl.created_at", '<=', helper()->date())
            ->when(!empty($settings), function (TonicsQuery $db) use ($settings) {
                if (isset($settings['search']) && $settings['search'] === true){
                    if (url()->hasParamAndValue('query')){
                        $db->WhereLike('post_title', url()->getParam('query'));
                    }
                }

                if (isset($settings['cat_id']) && $settings['cat_id'] === true){
                    if (url()->hasParamAndValue('cat')){
                        $db->WhereIn('cat_id', url()->getParam('cat'));
                    }
                }
            });

        $perPage = AppConfig::getAppPaginationMax();
        $orderBy = 'asc';
        $operator = 'IN';
        $pinPostIDs = [];

        foreach ($childrenFieldItems as $child){
            if (isset($child->field_options)){
                if (isset($child->field_options->post_query_builder_orderBy)){
                    $orderBy = $child->field_options->post_query_builder_orderBy;
                }

                if (isset($child->field_options->post_query_builder_perPost)){
                    $perPage = (int)$child->field_options->post_query_builder_perPost;
                }

                if (isset($child->field_options->post_query_builder_pinPost)){
                    # remove all empty spaces
                    $pinPostIDString = preg_replace('/\s+/', '', $child->field_options->post_query_builder_pinPost);
                    $pinPostIDs = explode(',', $pinPostIDString);
                }

                // for Category
                if (isset($child->field_input_name) && $child->field_input_name === 'post_query_builder_CategoryIn'){
                    if (isset($child->_children)){
                        foreach ($child->_children as $catChild){
                            if (isset($catChild->field_options->categoryOperator)){
                                $operator = $catChild->field_options->categoryOperator;
                            }
                            if (isset($catChild->field_options->{"post_query_builder_Category[]"})){
                                $value = $catChild->field_options->{"post_query_builder_Category[]"};
                                foreach ($value as $k => $val){
                                    $value[$k] = (int)$val;
                                }
                                if (empty($value)){
                                    continue;
                                }

                                switch ($operator){
                                    case 'IN':
                                        $db->WhereIn('cat_id', $value);
                                        break;
                                    case 'NOT IN':
                                        $db->WhereNotIn('cat_id', $value);
                                        break;
                                    default:
                                        $db->WhereIn('cat_id', $value);
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($pinPostIDs as $postID){
            $postID = (int)$postID;
            $db->OrderByDesc(table()->pickTable($postTbl, ['post_id']), function ($col) use ($db, $postID) {
                $db->addParam($postID);
                return "($col = ?)";
            });
        }

        return $db->when($orderBy === 'asc', function (TonicsQuery $db) use ($orderByName, $postTbl) {
            $db->OrderByAsc(table()->pickTable($postTbl, [$orderByName]));
        }, function (TonicsQuery $db) use ($orderByName, $postTbl) {
            $db->OrderByDesc(table()->pickTable($postTbl, [$orderByName]));
        })->SimplePaginate($perPage);
    }
}