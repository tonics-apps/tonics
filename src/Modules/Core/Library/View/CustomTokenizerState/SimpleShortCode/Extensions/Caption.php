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

class Caption extends TonicsTemplateViewAbstract implements TonicsModeRenderWithTagInterface
{

    /**
     * @throws \Exception
     */
    public function render (string $content, array $args, Tag $tag): string
    {
        $args = helper()->htmlSpecialCharsOnArrayValues($args);

        $id = (empty($args['id'])) ? '' : 'id="' . $args['id'] . '" ';
        $class = trim('caption ' . $args['align'] . ' ' . $args['class']);
        $style = '';
        // $style = 'style="width: ' . (int) $args['width'] . 'px" ';
        $describedby = (empty($args['caption_id'])) ? '' : 'aria-describedby="' . $args['caption_id'] . '" ';
        $caption_id = (empty($args['caption_id'])) ? '' : 'id="' . $args['caption_id'] . '" ';
        $caption = $args['caption'];

        return <<<HTML
<figure $id $describedby $style class="$class">
    $content
    <figcaption $caption_id class="caption-text">$caption</figcaption>
</figure>
HTML;
    }

    public function defaultArgs (): array
    {
        return [
            'id'         => '',
            'caption_id' => '',
            'align'      => 'alignnone',
            'width'      => '',
            'caption'    => '',
            'class'      => '',
        ];
    }
}