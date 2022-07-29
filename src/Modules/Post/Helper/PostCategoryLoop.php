<?php

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
            } else {
                $category->destroy_link = $categoryPrefix . $category->cat_slug . '/trash';
                $category->destroy_text = 'Trash';
            }

            if ($callback !== null){
                $queryMode = $tonicsView->getVariableData()['QUERY_MODE'];
                $queryMode[$query_name] = $category;
                $tonicsView->setDontCacheVariable(true);
                $tonicsView->addToVariableData('QUERY_MODE', $queryMode);
                $frag .= $callback();
            }
        }

        return $frag;
    }
}