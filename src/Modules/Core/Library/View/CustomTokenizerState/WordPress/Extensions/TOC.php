<?php

namespace App\Modules\Core\Library\View\CustomTokenizerState\WordPress\Extensions;

use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRenderWithTagInterface;
use Devsrealm\TonicsTemplateSystem\Node\Tag;

class TOC extends TonicsTemplateViewAbstract implements TonicsModeRenderWithTagInterface
{

    public function render(string $content, array $args, Tag $tag): string
    {
       return $content;
    }

    public function defaultArgs(): array
    {
        return [];
    }
}