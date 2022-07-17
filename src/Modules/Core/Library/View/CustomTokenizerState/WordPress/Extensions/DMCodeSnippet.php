<?php

namespace App\Modules\Core\Library\View\CustomTokenizerState\WordPress\Extensions;

use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRenderWithTagInterface;
use Devsrealm\TonicsTemplateSystem\Node\Tag;

class DMCodeSnippet extends TonicsTemplateViewAbstract implements TonicsModeRenderWithTagInterface
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
        return $content;
    }

    public function defaultArgs(): array
    {
        return [];
    }
}