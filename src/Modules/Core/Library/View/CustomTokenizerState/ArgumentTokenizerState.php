<?php

namespace App\Modules\Core\Library\View\CustomTokenizerState;

use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateTokenizerStateAbstract;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class ArgumentTokenizerState extends TonicsTemplateTokenizerStateAbstract
{

    public static function InitialStateHandler(TonicsView $tv): void
    {
        $char = $tv->getChar();

        if ($tv->charIsEOF()){
            return;
        }

        if ($tv->stackOfOpenTagIsEmpty()){
            $tv->createNewTagInOpenStackTag("argument");
        }

        $tv->appendCharToArgValue($char);
    }

    public static function TonicsTagLeftSquareBracketStateHandler(TonicsView $tonicsView): void
    {
        // TODO: Implement TonicsTagLeftSquareBracketStateHandler() method.
    }

    public static function TonicsTagOpenStateHandler(TonicsView $view): void
    {
        // TODO: Implement TonicsTagOpenStateHandler() method.
    }

    public static function TonicsTagNameStateHandler(TonicsView $tonicsView): void
    {
        // TODO: Implement TonicsTagNameStateHandler() method.
    }

    public static function TonicsTagOpenArgValueSingleQuotedStateHandler(TonicsView $tonicsView): void
    {
        // TODO: Implement TonicsTagOpenArgValueSingleQuotedStateHandler() method.
    }

    public static function TonicsTagOpenArgValueDoubleQuotedStateHandler(TonicsView $tonicsView): void
    {
        // TODO: Implement TonicsTagOpenArgValueDoubleQuotedStateHandler() method.
    }
}