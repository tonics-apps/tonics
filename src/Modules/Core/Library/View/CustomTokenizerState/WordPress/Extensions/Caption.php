<?php

namespace App\Modules\Core\Library\View\CustomTokenizerState\WordPress\Extensions;

use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRenderWithTagInterface;
use Devsrealm\TonicsTemplateSystem\Node\Tag;

class Caption extends TonicsTemplateViewAbstract implements TonicsModeRenderWithTagInterface
{

    /**
     * @throws \Exception
     */
    public function render(string $content, array $args, Tag $tag): string
    {
        $args = helper()->htmlSpecialCharsOnArrayValues($args);

        $id = (empty($args['id'])) ? '' : 'id="' . $args['id'] . '" ';
        $class = trim( 'caption ' . $args['align'] . ' ' . $args['class'] );
        $style = '';
       // $style = 'style="width: ' . (int) $args['width'] . 'px" ';
        $describedby = (empty($args['caption_id'])) ? '' : 'aria-describedby="' . $args['caption_id'] . '" ';
        $caption_id  = (empty($args['caption_id'])) ? '' : 'id="' . $args['caption_id'] . '" ';
        $caption = $args['caption'];

        return <<<HTML
<figure $id $describedby $style class="$class">
    $content
    <figcaption $caption_id class="caption-text">$caption</figcaption>
</figure>
HTML;
    }

    public function defaultArgs(): array
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