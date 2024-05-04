<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
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