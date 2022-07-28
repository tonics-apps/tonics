<?php

namespace App\Modules\Post\Helper;

use App\Modules\Core\Library\View\Extensions\Interfaces\QueryModeHandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class PostLoop implements QueryModeHandlerInterface
{

    public function handleQueryData(TonicsView $tonicsView, string $query_name, \stdClass $queryData, callable $callback = null): string
    {
        $frag = '';
        $posts = (isset($queryData->data)) ? $queryData->data : [];
        foreach ($posts as $k => $post) {
            $post->list_id = $k;
            $post->post_title = strip_tags($post->post_title);
            $post->edit_link = '/admin/posts/' . $post->post_slug . '/edit';
            $post->preview_link = '/posts/' . $post->slug_id . '/' . $post->post_slug;
            if ($post->post_status === -1){
                $post->destroy_link = '/admin/posts/' . $post->post_slug . '/delete';
                $post->destroy_text = 'Delete';
            } else {
                $post->destroy_link = '/admin/posts/' . $post->post_slug . '/trash';
                $post->destroy_text = 'Trash';
            }
            if ($callback !== null){
                $queryMode = $tonicsView->getVariableData()['QUERY_MODE'];
                $queryMode[$query_name] = $post;
                $tonicsView->setDontCacheVariable(true);
                $tonicsView->addToVariableData('QUERY_MODE', $queryMode);
                $frag .= $callback();
            }
        }

        return $frag;
    }
}