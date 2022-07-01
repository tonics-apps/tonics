<?php

namespace App\Modules\Core\Library\View\CustomTokenizerState;

use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateTokenizerStateAbstract;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class ArgumentTokenizerState extends TonicsTemplateTokenizerStateAbstract
{

    /**
     * @var string[]
     */
    private static array $allowedModeInArgument = [];

    public static function setupInit()
    {
        self::$allowedModeInArgument = [
            'block' => 'block',
            'v' => 'v',
            'var' => 'var'
        ];
    }

    public static function InitialStateHandler(TonicsView $tv): void
    {
        if (empty(self::$allowedModeInArgument)){
            self::setupInit();
        }

        if ($tv->charIsEOF()){
            return;
        }

        if ($tv->stackOfOpenTagIsEmpty()){
            $tv->createNewTagInOpenStackTag("argument");
        }

        if ($tv->charIsTabOrLFOrFFOrSpace()) {
            return;
        }

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

    /**
     * @return array
     */
    public static function getAllowedModeInArgument(): array
    {
        return self::$allowedModeInArgument;
    }
}