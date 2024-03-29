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
use App\Modules\Core\Library\View\Extensions\Traits\QueryModeHandlerHelper;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class PostLoop implements QueryModeHandlerInterface
{

    public function handleQueryData(TonicsView $tonicsView, string $query_name, \stdClass $queryData, callable $callback = null): string
    {
        $frag = '';
        $posts = (isset($queryData->data)) ? $queryData->data : [];
        $postPrefix = '/admin/posts/';
        foreach ($posts as $k => $post) {
            $post->list_id = $k;
            $post->post_title = strip_tags($post->post_title);
            $post->edit_link = $postPrefix . $post->post_slug . '/edit';
            $post->preview_link = '/posts/' . $post->post_slug_id . '/' . $post->post_slug;
            if ($post->post_status === -1){
                $post->destroy_link = $postPrefix . $post->post_slug . '/delete';
                $post->destroy_text = 'Delete';
                $post->button_data_attr = 'data-click-onconfirmdelete="true"';
            } else {
                $post->destroy_link = $postPrefix . $post->post_slug . '/trash';
                $post->destroy_text = 'Trash';
                $post->button_data_attr = 'data-click-onconfirmtrash="true"';
            }

            if ($callback !== null){
                $queryMode = $tonicsView->getVariableData()['QUERY_MODE'];
                $queryMode[$query_name] = $post;
                $tonicsView->addToVariableData('QUERY_MODE', $queryMode);
                $frag .= $callback();
            }
        }

        return $frag;
    }
}