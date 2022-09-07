<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library\View\CustomTokenizerState\WordPress\Extensions;

use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRenderWithTagInterface;
use Devsrealm\TonicsTemplateSystem\Node\Tag;

class EasyMediaDownload extends TonicsTemplateViewAbstract implements TonicsModeRenderWithTagInterface
{

    /**
     * @param string $content
     * @param array $args
     * @param Tag $tag
     * @return string
     * @throws \Exception
     */
    public function render(string $content, array $args, Tag $tag): string
    {
        $args = helper()->htmlSpecialCharsOnArrayValues($args);
        $noFollow = (isset($args['nofollow'])) ? 'rel=' .$args['nofollow'] : '';
        if (isset($args['window']) && $args['window'] !== 'new'){
            $args['target'] = "_self";
        }
        $contentForMax =<<<HTML
<a href="{$args['url']}" 
target="{$args['target']}" $noFollow
style="margin: 0 auto"
class="easy_media_download d:flex justify-content:center text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer">{$args['text']}</a>
HTML;

        return $contentForMax.$content;
    }

    public function defaultArgs(): array
    {
        return [
            'url'         => '',
            'text' => 'Download',
            'force_dl'      => '1',
            'width'      => '200',
            'height'    => '40',
            'target'      => '_blank',
            'color'      => '_blank',

            // for maxbutton
            'nofollow'      => 'true',
            'window'      => '_self',
        ];
    }
}