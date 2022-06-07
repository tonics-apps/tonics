<?php

namespace App\Library\View\CustomTokenizerState\WordPress\Extensions;

use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRenderWithTagInterface;
use Devsrealm\TonicsTemplateSystem\Node\Tag;

class Video extends TonicsTemplateViewAbstract implements TonicsModeRenderWithTagInterface
{

    /**
     * @param string $content
     * @param array $args
     * @param TOC $tag
     * @return string
     * @throws \Exception
     */
    public function render(string $content, array $args, Tag $tag): string
    {
        $args = helper()->htmlSpecialCharsOnArrayValues($args);

        $source = '';
        $updateOptSrc = false;
        ## Orders are not preserved, so, if mp4 comes last, this src of mp4
        ## would be the one used in $args['src'] regardless of its position (when it is empty, it uses webm, etc)
        foreach ($args as $k => $src){
            $k = strtolower($k);

            if (empty($src)){
                continue;
            }

            if ($k === 'mp4'){
                $updateOptSrc = true;
                $source .= "<source src=$src type='video/mp4'>";
            }
            if ($k === 'webm'){
                $source .= "<source src=$src type='video/webm'>";
            }
            if ($k === 'm4v'){
                $updateOptSrc = true;
                $source .= "<source src=$src type='audio/wav'>";
            }
            if ($k === 'ogv'){
                $updateOptSrc = true;
                $source .= "<source src=$src>";
            }
            if ($k === 'wmv'){
                $updateOptSrc = true;
                $source .= "<source src=$src>";
            }
            if ($k === 'flv'){
                $updateOptSrc = true;
                $source .= "<source src='$src'>";
            }
            if ($updateOptSrc && empty($args['src'])){
                $args['src'] = $src;
            }
        }

        $autoPlay = (strtolower($args['autoplay']) === 'on') ? 'autoplay' : '';
        $loop = (strtolower($args['loop']) === 'on') ? 'loop' : '';
        $height = empty($args['height']) ? '' : "height=". (int)$args['height'] .'px';
        $width = empty($args['width']) ? '' : "width=". (int)$args['width'] .'px';

        return <<<HTML
<figure class="video-shortcode">
        <video $width $height poster="{$args['poster']}" style="{$args['style']}" $autoPlay $loop preload="{$args['preload']}"
        class="{$args['class']}"
        controls
        src="{$args['src']}">
        $source
            Your browser does not support the
            <code>audio</code> element.
    </video>
</figure>
HTML;
    }

    public function defaultArgs(): array
    {
        return  [
            'src'      => '',
            'mp4'      => '',
            'webm'      => '',
            'm4v'      => '',
            'wmv'      => '',
            'flv'      => '',
            'loop'     => '',
            'autoplay' => '',
            'preload'  => 'none',
            'class'    => 'audio-shortcode',
            'style'    => '',
            'height'    => '',
            'width'    => '',
            'poster'    => '',
        ];
    }
}