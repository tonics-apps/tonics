<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\Helper;

use App\Modules\Core\Library\View\Extensions\Interfaces\QueryModeHandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class GenreLoop implements QueryModeHandlerInterface
{

    public function handleQueryData(TonicsView $tonicsView, string $query_name, \stdClass $queryData, callable $callback = null): string
    {
        $frag = '';
        $genrePrefix = '/admin/genres/';
        $genres = (isset($queryData->data)) ? $queryData->data : [];
        foreach ($genres as $k => $genre) {
            $genre->list_id = $k;
            $genre->genre_name = strip_tags($genre->genre_name);
            $genre->edit_link = $genrePrefix . $genre->genre_slug . '/edit';
            $genre->preview_link = '/genres/' . $genre->genre_slug;

            if ($genre->can_delete === 1){
                $genre->destroy_link = $genrePrefix . $genre->genre_slug . '/delete';
                $genre->destroy_text = 'Delete';
            }

            if ($callback !== null){
                $queryMode = $tonicsView->getVariableData()['QUERY_MODE'];
                $queryMode[$query_name] = $genre;
                $tonicsView->setDontCacheVariable(true);
                $tonicsView->addToVariableData('QUERY_MODE', $queryMode);
                $frag .= $callback();
            }

        }
        return $frag;
    }
}