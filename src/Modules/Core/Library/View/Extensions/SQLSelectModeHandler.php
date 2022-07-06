<?php

namespace App\Modules\Core\Library\View\Extensions;

use App\Modules\Core\Library\View\Extensions\Traits\ArgResolverAndExpander;
use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeInterface;
use Devsrealm\TonicsTemplateSystem\Node\Tag;
use Devsrealm\TonicsTemplateSystem\Tokenizer\Token\Events\OnTagToken;


class SQLSelectModeHandler extends TonicsTemplateViewAbstract implements TonicsModeInterface
{
    use ArgResolverAndExpander;

    private string $error = '';

    public function validate(OnTagToken $tagToken): bool
    {
        if (strtolower($tagToken->getTagName()) !== 'sql'){
            $this->error = "SQL Statements should start with an SQL Tag, i.e [[SQl(..)...]]";
            return false;
        }
        // foreach ()
        $view = $this->getTonicsView();
        return $view->validateMaxArg($tagToken->getArg(), $tagToken->getTagName(), 10000);
    }

    public function stickToContent(OnTagToken $tagToken)
    {
        $sql_key_name = $tagToken->getFirstArgChild();
        $storage = $this->getTonicsView()->getModeStorage('sql');
        $storage[$sql_key_name] = '';
        $this->getTonicsView()->storeDataInModeStorage('sql', $storage);

        /** @var Tag $tag */
        foreach ($tagToken->getChildren(true, function (Tag $onBeforeRecurseTag) {
            return $this->isSQLMode($onBeforeRecurseTag);
        }) as $tag){
            if ($this->isSQLMode($tag)){
                $args = $this->resolveArgs($tag->getTagName(), $tag->getArgs());
                $args = $this->expandArgs($args[$tag->getTagName()]);
                dd($args);
            }
            dd($tag);
        }

        dd($tagToken, $this->getTonicsView());
    }

    public function error(): string
    {
        return $this->error;
    }

    public function isSQLMode(Tag $tag): bool
    {
        $name = strtolower($tag->getTagName());
        $hd = $this->getTonicsView()->getModeHandler()[$name];
        if (is_object($hd)){$hd = get_class($hd); }

        return $hd === $this::class;
    }
}