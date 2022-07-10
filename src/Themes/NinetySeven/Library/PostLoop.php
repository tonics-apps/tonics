<?php

namespace App\Themes\NinetySeven\Library;

use App\Modules\Core\Library\View\Extensions\Interfaces\QueryModeHandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class PostLoop implements QueryModeHandlerInterface
{

    public function handleQueryData(TonicsView $tonicsView, \stdClass $queryData): string
    {
        $frag = '';
        $posts = (isset($queryData->data)) ? $queryData->data : [];
        foreach ($posts as $post) {
            $image = '';
            if (!empty($post->image_url)){
                $image = <<<IMAGE
                <img loading="lazy" decoding="async" src="$post->image_url" class="" alt="$post->post_title">
IMAGE;
            }
            $frag .= <<<HTML
     <li>
        <div class="owl width:100% border-width:default border:black color:black height:100%">
            <div class="post-thumbnail">
                $image
            </div>
            <div class="text-on-admin-util padding:default owl cursor:text">
            <h4>$post->post_title</h4>
            <div class="form-group d:flex flex-gap:small">
                             <a title="$post->post_title" href="/posts/$post->slug_id/$post->post_slug" 
                             class="text-align:center text:paragraph-fluid-one text:no-wrap no-text-highlight bg:transparent border:none color:black border-width:default border:black padding:default
                                margin-top:0 cart-width cursor:pointer">Read More</a>    
            </div>
            </div>
           <div></div>         
        </div>
    </li>
 HTML;
        }
        return $frag;
    }
}