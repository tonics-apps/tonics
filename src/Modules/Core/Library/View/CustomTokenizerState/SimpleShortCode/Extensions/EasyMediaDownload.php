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

namespace App\Modules\Core\Library\View\CustomTokenizerState\SimpleShortCode\Extensions;

use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRenderWithTagInterface;
use Devsrealm\TonicsTemplateSystem\Node\Tag;

class EasyMediaDownload extends TonicsTemplateViewAbstract implements TonicsModeRenderWithTagInterface
{

    /**
     * @param string $content
     * @param array $args
     * @param Tag $tag
     *
     * @return string
     * @throws \Exception
     */
    public function render (string $content, array $args, Tag $tag): string
    {
        $args = helper()->htmlSpecialCharsOnArrayValues($args);
        $noFollow = (isset($args['nofollow'])) ? 'rel=' . $args['nofollow'] : '';
        if (isset($args['window']) && $args['window'] !== 'new') {
            $args['target'] = "_self";
        }
        $contentForMax = <<<HTML
<a href="{$args['url']}" 
target="{$args['target']}" $noFollow
style="margin: 0 auto"
class="easy_media_download d:flex justify-content:center text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer">{$args['text']}</a>
HTML;

        return $contentForMax . $content;
    }

    public function defaultArgs (): array
    {
        return [
            'url'      => '',
            'text'     => 'Download',
            'force_dl' => '1',
            'width'    => '200',
            'height'   => '40',
            'target'   => '_blank',
            'color'    => '_blank',

            // for maxbutton
            'nofollow' => 'true',
            'window'   => '_self',
        ];
    }
}