<?php

namespace App\Apps\NinetySeven\Library;

use App\Modules\Core\Library\View\Extensions\Interfaces\QueryModeHandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class PostLoop implements QueryModeHandlerInterface
{

    public function handleQueryData(TonicsView $tonicsView, string $query_name, \stdClass $queryData, callable $callback = null): string
    {
        $frag = '';
        $posts = (isset($queryData->data)) ? $queryData->data : [];
        foreach ($posts as $post) {
            $post->post_title = strip_tags($post->post_title);
            $post->_full_link = "/posts/$post->post_slug_id/$post->post_slug";
            $post->field_settings = json_decode($post->field_settings);
            $post->_og_description = $post->field_settings->seo_description;
            if ($callback !== null){
                $queryMode = $tonicsView->getVariableData()['QUERY_MODE'];
                $queryMode[$query_name] = $post;
                $tonicsView->setDontCacheVariable(true);
                $tonicsView->addToVariableData('QUERY_MODE', $queryMode);
                $frag .= $callback();
            }
        }
        $tonicsView->setDontCacheVariable(false);
        return $frag;
    }
}