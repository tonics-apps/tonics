<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Post\Helper;

use App\Modules\Core\Library\View\Extensions\Interfaces\QueryModeHandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class PostCategoryLoop implements QueryModeHandlerInterface
{

    public function handleQueryData(TonicsView $tonicsView, string $query_name, \stdClass $queryData, callable $callback = null): string
    {
        $frag = '';
        $categories = (isset($queryData->data)) ? $queryData->data : [];
        $categoryPrefix = '/admin/posts/category/';
        foreach ($categories as $k => $category) {
            $category->list_id = $k;
            $category->cat_name = strip_tags($category->cat_name);
            $category->edit_link = $categoryPrefix . $category->cat_slug . '/edit';
            $category->preview_link = '/categories/' . $category->slug_id . '/' . $category->cat_slug;
            if ($category->cat_status === -1){
                $category->destroy_link = $categoryPrefix . $category->cat_slug . '/delete';
                $category->destroy_text = 'Delete';
                $category->button_data_attr = 'data-click-onconfirmdelete="true"';
            } else {
                $category->destroy_link = $categoryPrefix . $category->cat_slug . '/trash';
                $category->destroy_text = 'Trash';
                $category->button_data_attr = 'data-click-onconfirmtrash="true"';
            }

            if ($callback !== null){
                $queryMode = $tonicsView->getVariableData()['QUERY_MODE'];
                $queryMode[$query_name] = $category;
                $tonicsView->addToVariableData('QUERY_MODE', $queryMode);
                $frag .= $callback();
            }
        }

        return $frag;
    }
}