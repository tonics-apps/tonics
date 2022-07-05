<?php

namespace App\Modules\Core\Library\View\CustomTokenizerState\SQL;

use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateTokenizerStateAbstract;
use Devsrealm\TonicsTemplateSystem\TonicsView;


/**
 *
 */
class SQLSELECTokenizerState extends TonicsTemplateTokenizerStateAbstract
{

    public static function InitialStateHandler(TonicsView $tonicsView): void
    {
        // TODO: Implement InitialStateHandler() method.
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