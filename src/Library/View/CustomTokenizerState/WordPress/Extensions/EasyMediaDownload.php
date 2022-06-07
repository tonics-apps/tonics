<?php

namespace App\Library\View\CustomTokenizerState\WordPress\Extensions;

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
class="easy_media_download text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">{$args['text']}</a>
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