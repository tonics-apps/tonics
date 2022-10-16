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
     * @return object|null
     * @throws \Exception
     */
    public static function postDataFromPostQueryBuilderField($childrenFieldItems): ?object
    {
        $postTbl = Tables::getTable(Tables::POSTS);
        $postCatTbl = Tables::getTable(Tables::POST_CATEGORIES);
        $CatTbl = Tables::getTable(Tables::CATEGORIES);

        $postFieldSettings = $postTbl . '.field_settings';
        $tblCol = table()->pick([$postTbl => ['post_id', 'post_title', 'post_slug', 'field_settings', 'updated_at', 'image_url']])
            . ', CONCAT(cat_id, "::", cat_slug ) as fk_cat_id, CONCAT_WS("/", "/posts", post_slug) as _preview_link '
            . ", JSON_UNQUOTE(JSON_EXTRACT($postFieldSettings, '$.seo_description')) as post_description";

        $db = db()->Select($tblCol)
            ->From($postCatTbl)
            ->Join($postTbl, table()->pickTable($postTbl, ['post_id']), table()->pickTable($postCatTbl, ['fk_post_id']))
            ->Join($CatTbl, table()->pickTable($CatTbl, ['cat_id']), table()->pickTable($postCatTbl, ['fk_cat_id']))
            ->WhereEquals('post_status', 1)
            ->Where("$postTbl.created_at", '<=', helper()->date());

        $perPage = AppConfig::getAppPaginationMax();
        $orderBy = 'asc';
        $operator = 'IN';

        foreach ($childrenFieldItems as $child){
            if (isset($child->field_options)){
                if (isset($child->field_options->post_query_builder_orderBy)){
                    $orderBy = $child->field_options->post_query_builder_orderBy;
                }

                if (isset($child->field_options->post_query_builder_perPost)){
                    $perPage = (int)$child->field_options->post_query_builder_perPost;
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

        return $db->when($orderBy === 'asc', function (TonicsQuery $db) use ($postTbl) {
            $db->OrderByAsc(table()->pickTable($postTbl, ['updated_at']));
        }, function (TonicsQuery $db) use ($postTbl) {
            $db->OrderByDesc(table()->pickTable($postTbl, ['updated_at']));
        })->SimplePaginate($perPage);
    }
}