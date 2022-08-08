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

class Audio extends TonicsTemplateViewAbstract implements TonicsModeRenderWithTagInterface
{

    /**
     * @throws \Exception
     */
    public function render(string $content, array $args, Tag $tag): string
    {
        $args = helper()->htmlSpecialCharsOnArrayValues($args);
        $source = '';
        $updateOptSrc = false;
        ## Orders are not preserved, so, if mp3 comes last, this src of mp3
        ## would be the one used in  $args['src']regardless of its position (when it is empty, it uses ogg, etc)
        foreach ($args as $k => $src){
            $k = strtolower($k);

            if (empty($src)){
                continue;
            }
            if ($k === 'mp3'){
                $updateOptSrc = true;
                $source .= <<<HTML
  <source src="$src" type="audio/mpeg">
HTML;
            }
            if ($k === 'ogg'){
                $source .= <<<HTML
  <source src="$src" type="audio/ogg">
HTML;
            }
            if ($k === 'wav'){
                $updateOptSrc = true;
                $source .= <<<HTML
  <source src="$src" type="audio/wav">
HTML;
            }

            if ($k === 'wma'){
                $updateOptSrc = true;
                $source .= <<<HTML
  <source src="$src">
HTML;
            }

            if ($k === 'm4a'){
                $updateOptSrc = true;
                $source .= <<<HTML
  <source src="$src">
HTML;
            }

            if ($updateOptSrc && empty($args['src'])){
                $args['src'] = $src;
            }
        }

        $autoPlay = (strtolower($args['autoplay']) === 'on') ? 'autoplay' : '';
        $loop = (strtolower($args['loop']) === 'on') ? 'loop' : '';

        return <<<HTML
<figure class="audio-shortcode">
        <audio style="{$args['style']}" $autoPlay $loop preload="{$args['preload']}"
        class="{$args['class']}"
        controls
        src="{$args['src']}">
        $source
            Your browser does not support the
            <code>audio</code> element.
    </audio>
</figure>
HTML;
    }

    public function defaultArgs(): array
    {
        return  [
            'src'      => '',
            'mp3'      => '',
            'ogg'      => '',
            'wav'      => '',
            'wma'      => '',
            'm4a'      => '',
            'loop'     => '',
            'autoplay' => '',
            'preload'  => 'none',
            'class'    => 'audio-shortcode',
            'style'    => '',
        ];
    }
}