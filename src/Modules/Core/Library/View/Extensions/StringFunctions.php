<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library\View\Extensions;

use App\Modules\Core\Library\View\Extensions\Traits\TonicsTemplateSystemHelper;
use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRendererInterface;
use Devsrealm\TonicsTemplateSystem\Tokenizer\Token\Events\OnTagToken;


/**
 * You can use it as follows:
 *
 * ```
 *  [[string_strtolower("v[Variable.nested]")]]
 * ```
 *
 * for block, you do: `[[string_strtolower("block[blockname]")]]`
 *
 * for other arguments, just pass a string literal, and I'll handle the conversion, e.g. [[string_strtolower("String Literal")]].
 *
 * If a string function requires more argument, simply separate it by comma, e.g: `[[string_implode(",", "block[blockname]")]]`
 */
class StringFunctions extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{
    use TonicsTemplateSystemHelper;

    private string $error = '';

    public function validate(OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        $stringInfo = $this->stringInfo()[$tagToken->getTagName()];
        return $view->validateMaxArg($tagToken->getArg(), $tagToken->getTagName(), $stringInfo['max_arg'], $stringInfo['min_arg']);
    }

    public function stickToContent(OnTagToken $tagToken)
    {
        $view = $this->getTonicsView();
        $view->getContent()->addToContent($tagToken->getTagName(), '', $this->resolveArgs($tagToken->getTagName(), $tagToken->getArg()));
    }

    public function error(): string
    {
        return $this->error;
    }

    public function render(string $content, array $args, array $nodes = []): string
    {
        $stringFunction = $this->getTonicsView()->getCurrentRenderingContentMode();

        // probably called from a nested function
        if (!isset($args[$stringFunction])){
            $stringInfo = $this->stringInfo()[$stringFunction];
            $this->getTonicsView()->validateMaxArg($args, $stringFunction, $stringInfo['max_arg'], $stringInfo['min_arg']);
            $args = $this->resolveArgs($stringFunction, $args);
        }

        $args = $args[$stringFunction];
        $args = $this->expandArgs($args);
        return $this->stringInfo()[$stringFunction]['handle']($args);
    }

    public function stringInfo(): array
    {
        $htmlFlag = [
            'ENT_COMPAT' => ENT_COMPAT,
            'ENT_QUOTES' => ENT_QUOTES,
            'ENT_NOQUOTES' => ENT_NOQUOTES,
            'ENT_SUBSTITUTE' => ENT_SUBSTITUTE,
            'ENT_HTML401' => ENT_HTML401,
            'ENT_XML1' => ENT_XML1,
            'ENT_XHTML' => ENT_XHTML,
            'ENT_HTML5' => ENT_HTML5,
        ];

        return [
            'string_addslashes' => ['min_arg' => 1, 'max_arg' => 1, 'name' => 'addslashes', 'handle' => function ($args) {
                return addslashes($args[0]);
            }],
            'string_chop' => ['min_arg' => 1, 'max_arg' => 2, 'name' => 'chop', 'handle' => function ($args) {
                return rtrim($args[0], $args[1] ?? " \t\n\r\0\x0B");
            }],
            'string_contain' => ['min_arg' => 2, 'max_arg' => 2, 'name' => 'contain', 'handle' => function ($args) {
                return str_contains($args[0], $args[1]);
            }],
            'string_str_starts_with' => ['min_arg' => 2, 'max_arg' => 2, 'name' => 'str_starts_with', 'handle' => function ($args) {
                return str_starts_with($args[0], $args[1]);
            }],
            'string_str_ends_with' => ['min_arg' => 2, 'max_arg' => 2, 'name' => 'str_ends_with', 'handle' => function ($args) {
                return str_ends_with($args[0], $args[1]);
            }],
            'string_html_entity_decode' => ['min_arg' => 1, 'max_arg' => 3, 'name' => 'html_entity_decode', 'handle' => function ($args) use ($htmlFlag) {
                $flag = (isset($htmlFlag[$args[1]])) ? $htmlFlag[$args[1]] : ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401;
                $string = (is_null($args[0])) ? '' : $args[0];
                return html_entity_decode($string, $flag, $args[2] ?? 'ISO-8859-1');
            }],
            'string_htmlentities' => ['min_arg' => 1, 'max_arg' => 4, 'name' => 'htmlentities', 'handle' => function ($args) use ($htmlFlag) {
                $flag = $htmlFlag;
                $flag[ENT_DISALLOWED] = ENT_DISALLOWED;
                $flag = (isset($flag[$args[1]])) ? $flag[$args[1]] : ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401;
                $string = (is_null($args[0])) ? '' : $args[0];
                return htmlentities($string, $flag, $args[2] ?? 'ISO-8859-1', (bool)$args[3] ?? true);
            }],
            'string_htmlspecialchars_decode' => ['min_arg' => 1, 'max_arg' => 2, 'name' => 'htmlspecialchars_decode', 'handle' => function ($args) {
                $flag = (isset($htmlFlag[$args[1]])) ? $htmlFlag[$args[1]] : ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401;
                $string = (is_null($args[0])) ? '' : $args[0];
                return htmlspecialchars_decode($string, $flag);
            }],
            'string_htmlspecialchars' => ['min_arg' => 1, 'max_arg' => 4, 'name' => 'htmlspecialchars', 'handle' => function ($args) use ($htmlFlag) {
                $flag = $htmlFlag;
                $flag[ENT_DISALLOWED] = ENT_DISALLOWED;
                $flag = (isset($flag[$args[1]])) ? $flag[$args[1]] : ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401;
                $string = (is_null($args[0])) ? '' : $args[0];
                return htmlspecialchars($string, $flag, $args[2] ?? 'ISO-8859-1', (bool)$args[3] ?? true);
            }],
            'string_implode' => ['min_arg' => 1, 'max_arg' => 2, 'name' => 'implode', 'handle' => function ($args) {
                return implode($args[0] ?? '', $args[1] ?? null);
            }],
            'string_join' => ['min_arg' => 1, 'max_arg' => 2, 'name' => 'join', 'handle' => function ($args) {
                return implode($args[0] ?? '', $args[1] ?? null);
            }],
            'string_lcfirst' => ['min_arg' => 1, 'max_arg' => 1, 'name' => 'lcfirst', 'handle' => function ($args) {
                return lcfirst($args[0]);
            }],
            'string_trim' => ['min_arg' => 1, 'max_arg' => 2, 'name' => 'trim', 'handle' => function ($args) {
                return trim($args[0], $args[1] ?? " \t\n\r\0\x0B");
            }],
            'string_ltrim' => ['min_arg' => 1, 'max_arg' => 2, 'name' => 'ltrim', 'handle' => function ($args) {
                return ltrim($args[0], $args[1] ?? " \t\n\r\0\x0B");
            }],
            'string_rtrim' => ['min_arg' => 1, 'max_arg' => 2, 'name' => 'rtrim', 'handle' => function ($args) {
                return rtrim($args[0], $args[1] ?? " \t\n\r\0\x0B");
            }],
            'string_nl2br' => ['min_arg' => 1, 'max_arg' => 2, 'name' => 'nl2br', 'handle' => function ($args) {
                return nl2br($args[0], (bool)$args[1] ?? true);
            }],
            'string_number_format' => ['min_arg' => 1, 'max_arg' => 4, 'name' => 'number_format', 'handle' => function ($args) {
                return number_format(floatval($args[0]), intval($args[1] ?? 0), $args[2] ?? '.', $args[3] ?? ',');
            }],
            'string_sprintf' => ['min_arg' => 1, 'max_arg' => 1000, 'name' => 'sprintf', 'handle' => function ($args) {
                $sprintFArgs = $args;
                unset($sprintFArgs[0]);
                return sprintf($args[0], ...$sprintFArgs);
            }],
            'string_str_ireplace' => ['min_arg' => 3, 'max_arg' => 3, 'name' => 'str_ireplace', 'handle' => function ($args) {
                return str_ireplace($args[0] ?? '', $args[1] ?? '', $args[2] ?? '');
            }],
            'string_str_replace' => ['min_arg' => 3, 'max_arg' => 3, 'name' => 'str_replace', 'handle' => function ($args) {
                return str_replace($args[0] ?? '', $args[1] ?? '', $args[2] ?? '');
            }],
            'string_str_pad' => ['min_arg' => 2, 'max_arg' => 4, 'name' => 'str_pad', 'handle' => function ($args) {
                $flag = [
                    STR_PAD_RIGHT => STR_PAD_RIGHT,
                    STR_PAD_LEFT => STR_PAD_LEFT,
                ];
                $flag = (isset($flag[$args[1]])) ? $flag[$args[1]] : STR_PAD_RIGHT;
                return str_pad($args[0] ?? '', intval($args[1] ?? 0), $args[2] ?? " ", $flag);
            }],
            'string_str_repeat' => ['min_arg' => 2, 'max_arg' => 2, 'name' => 'str_repeat', 'handle' => function ($args) {
                return str_repeat($args[0] ?? '', intval($args[1] ?? 0));
            }],
            'string_str_shuffle' => ['min_arg' => 1, 'max_arg' => 1, 'name' => 'str_shuffle', 'handle' => function ($args) {
                return str_shuffle($args[0] ?? '');
            }],
            'string_strip_tags' => ['min_arg' => 1, 'max_arg' => 2, 'name' => 'strip_tags', 'handle' => function ($args) {
                return strip_tags($args[0] ?? '', $args[0] ?? null);
            }],
            'string_stripcslashes' => ['min_arg' => 1, 'max_arg' => 1, 'name' => 'stripcslashes', 'handle' => function ($args) {
                return stripcslashes($args[0] ?? '');
            }],
            'string_strrev' => ['min_arg' => 1, 'max_arg' => 1, 'name' => 'strrev', 'handle' => function ($args) {
                return strrev($args[0] ?? '');
            }],
            'string_strtolower' => ['min_arg' => 1, 'max_arg' => 1, 'name' => 'strtolower', 'handle' => function ($args) {
                return strtolower($args[0] ?? '');
            }],
            'string_strtoupper' => ['min_arg' => 1, 'max_arg' => 1, 'name' => 'strtoupper', 'handle' => function ($args) {
                return strtoupper($args[0] ?? '');
            }],
            'string_substr' => ['min_arg' => 2, 'max_arg' => 3, 'name' => 'substr', 'handle' => function ($args) {
                $len = (isset($args[2])) ? intval($args[2]): null;
                return substr($args[0] ?? '', intval($args[1]), $len);
            }],
            'string_ucfirst' => ['min_arg' => 1, 'max_arg' => 1, 'name' => 'ucfirst', 'handle' => function ($args) {
                return ucfirst($args[0] ?? '');
            }],
            'string_ucwords' => ['min_arg' => 1, 'max_arg' => 1, 'name' => 'ucwords', 'handle' => function ($args) {
                return ucwords($args[0] ?? '', $args[1] ?? " \t\r\n\f\v");
            }],
            'string_wordwrap' => ['min_arg' => 1, 'max_arg' => 4, 'name' => 'wordwrap', 'handle' => function ($args) {
                return wordwrap($args[0] ?? '',  intval($args[1]),$args[2] ?? "\n", (bool)$args[3] ?? false);
            }],
        ];

    }
}