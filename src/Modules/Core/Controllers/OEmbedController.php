<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Tables;

class OEmbedController
{
    /**
     * @throws \Exception
     */
    public function OEmbed()
    {
        if (request()->hasParamAndValue('url') && str_starts_with(request()->getParam('url'), AppConfig::getAppUrl())){
            $url = request()->getParam('url');
            $path = parse_url($url, PHP_URL_PATH);
            $splitPath = array_values(array_filter(explode('/', $path)));
            if ((isset($splitPath[0]) && isset($splitPath[1]))){
                $jsonData = [
                    'version' => '1.0',
                    'type' => 'link',
                    'author_name' => '',
                    'author_url' => '',
                    'thumbnail_url' => '',
                    'thumbnail_width' => (int)request()->getParam('maxwidth', 600),
                    'thumbnail_height' => (int)request()->getParam('maxheight', 600),
                    'html' => '',
                ];
                $jsonData['provider_name'] = AppConfig::getAppName();
                $jsonData['provider_url'] = AppConfig::getAppUrl();
                $data  = null;
                $slugID = $splitPath[1];
                if ($splitPath[0] === 'posts'){
                    $postTbl = Tables::getTable(Tables::POSTS);
                    $postFieldSettings = $postTbl . '.field_settings';
                    $tblCol = table()->pick([$postTbl => ['post_id', 'post_title', 'slug_id', 'post_slug', 'field_settings', 'created_at', 'updated_at', 'image_url']])
                        . ', CONCAT_WS("/", "/posts", post_slug) as _preview_link, post_title as _title '
                        . ", JSON_UNQUOTE(JSON_EXTRACT($postFieldSettings, '$.seo_description')) as _description";
                    $data = db()->Select($tblCol)->From($postTbl)
                        ->WhereEquals('slug_id', $slugID)
                        ->WhereEquals('post_status', 1)
                        ->FetchFirst();
                }

                if (is_object($data)){
                    if (str_starts_with($data->image_url, AppConfig::getAppUrl())){
                        $data->image_url = str_replace(AppConfig::getAppUrl(), '', $data->image_url);
                    }
                    $data->image_url = AppConfig::getAppUrl() . $data->image_url;
                    $data->_title = helper()->htmlSpecChar($data->_title);
                    $jsonData['thumbnail_url'] = $data->image_url;
                    $jsonData['html'] = <<<HTML
    <div tabindex="0" class="owl width:100% padding:default color:black bg:white-one border-width:default border:black position:relative">
      <div style="border-top-right-radius: 10px;" class="bg:pure-black color:white padding:small tonics-oembed-title">{$data->{'_title'}}</div>
        <div class="tonics-oembed-image">
        <a href="{$data->{'_preview_link'}}" target="_top">
            <img src="{$data->{'image_url'}}" alt="{$data->{'_title'}}" title="{$data->{'_title'}}" 
            width="{$jsonData['thumbnail_width']}" height="{$jsonData['thumbnail_height']}" loading="lazy" decoding="async">
        </a>
      </div>
    <div class="tonics-oembed-description">
    {$data->{'_description'}}
    </div>
        <div class="tonics-oembed-footer d:flex align-items:center flex-d:column">
            <a class="text-align:center bg:transparent border:none bg:pure-black color:white border-width:default border:black padding:small
                    margin-top:0 cursor:pointer button:box-shadow-variant-1" href="{$data->{'_preview_link'}}" 
                    title="{$data->{'_title'}}" target="_blank">Read More</a>
        </div>
    </div>
HTML;
                }

                response()->httpResponseCode(200)->json($jsonData, JSON_PRETTY_PRINT);
            }
        }

        response()->onError(404);
    }
}