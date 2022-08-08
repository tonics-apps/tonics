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

class ArtistLoop implements QueryModeHandlerInterface
{

    public function handleQueryData(TonicsView $tonicsView, string $query_name, \stdClass $queryData, callable $callback = null): string
    {
        $frag = '';
        $artistPrefix = '/admin/artists/';
        $artists = (isset($queryData->data)) ? $queryData->data : [];
        foreach ($artists as $k => $artist) {
            $artist->list_id = $k;
            $artist->artist_name = strip_tags($artist->artist_name);
            $artist->edit_link = $artistPrefix . $artist->artist_slug . '/edit';
            $artist->preview_link = '/artists/' . $artist->artist_slug;

            $artist->destroy_link = $artistPrefix . $artist->artist_slug . '/delete';
            $artist->destroy_text = 'Delete';

            if ($callback !== null){
                $queryMode = $tonicsView->getVariableData()['QUERY_MODE'];
                $queryMode[$query_name] = $artist;
                $tonicsView->setDontCacheVariable(true);
                $tonicsView->addToVariableData('QUERY_MODE', $queryMode);
                $frag .= $callback();
            }
        }
        return $frag;
    }
}