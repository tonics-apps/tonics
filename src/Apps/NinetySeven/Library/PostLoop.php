<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\NinetySeven\Library;

use App\Modules\Core\Library\View\Extensions\Interfaces\QueryModeHandlerInterface;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class PostLoop implements QueryModeHandlerInterface
{

    public function handleQueryData(TonicsView $tonicsView, string $query_name, \stdClass $queryData, callable $callback = null): string
    {
        $fieldData = new FieldData();
        $frag = '';
        $posts = (isset($queryData->data)) ? $queryData->data : [];
        foreach ($posts as $post) {
            $post->post_title = strip_tags($post->post_title);
            $post->_full_link = "/posts/$post->post_slug_id/$post->post_slug";
            $post->post_field_settings = json_decode($post->post_field_settings);
            $post->_og_description = $post->post_field_settings->seo_description;
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