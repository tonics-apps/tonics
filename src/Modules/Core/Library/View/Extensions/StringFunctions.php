<?php

namespace App\Modules\Core\Library\View\Extensions;

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
            'string_html_entity_decode' => ['min_arg' => 1, 'max_arg' => 3, 'name' => 'html_entity_decode', 'handle' => function ($args) use ($htmlFlag) {
                $flag = (isset($htmlFlag[$args[1]])) ? $htmlFlag[$args[1]] : ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401;
                return html_entity_decode($args[0], $flag, $args[2] ?? 'ISO-8859-1');
            }],
            'string_htmlentities' => ['min_arg' => 1, 'max_arg' => 4, 'name' => 'htmlentities', 'handle' => function ($args) use ($htmlFlag) {
                $flag = $htmlFlag;
                $flag[ENT_DISALLOWED] = ENT_DISALLOWED;
                $flag = (isset($flag[$args[1]])) ? $flag[$args[1]] : ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401;
                return htmlentities($args[0], $flag, $args[2] ?? 'ISO-8859-1', (bool)$args[3] ?? true);
            }],
            'string_htmlspecialchars_decode' => ['min_arg' => 1, 'max_arg' => 2, 'name' => 'htmlspecialchars_decode', 'handle' => function ($args) {
                $flag = (isset($htmlFlag[$args[1]])) ? $htmlFlag[$args[1]] : ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401;
                return htmlspecialchars_decode($args[0], $flag);
            }],
            'string_htmlspecialchars' => ['min_arg' => 1, 'max_arg' => 4, 'name' => 'htmlspecialchars', 'handle' => function ($args) use ($htmlFlag) {
                $flag = $htmlFlag;
                $flag[ENT_DISALLOWED] = ENT_DISALLOWED;
                $flag = (isset($flag[$args[1]])) ? $flag[$args[1]] : ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401;
                return htmlspecialchars($args[0], $flag, $args[2] ?? 'ISO-8859-1', (bool)$args[3] ?? true);
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

    public function resolveArgs($tagName, $tagArgs = [])
    {
        $mode = [
          'v[' => 'var',
          'var[' => 'var',
          'block[' => 'block',
        ];
        $args = [];
        foreach ($tagArgs as $k => $arg){
            $catch = false;
            foreach ($mode as $mk => $mv){
                if (str_starts_with($arg, $mk)){
                    preg_match('/\\[(.*?)]/', $arg, $matches);
                    if (isset($matches[1])){
                        $args[$k] = [
                            'mode' => $mv,
                            'value' => $matches[1]
                        ];
                        $catch = true;
                    }
                    break;
                }
            }

            if ($catch === false){
                $args[$k] = [
                    'value' => $arg
                ];
            }
        }

       return [$tagName => $args ];
    }

    public function expandArgs($args = [])
    {
        foreach ($args as $k => $arg){
            if (isset($arg['mode'])){
                if ($arg['mode'] === 'var'){
                    if (str_contains($arg['value'], '..')){
                        $variable = explode('..', $arg['value']);
                        if (is_array($variable)){
                            foreach ($variable as $var){
                                $variable = $this->getTonicsView()->accessArrayWithSeparator($var);
                                if (!empty($variable)){
                                    $args[$k] = $variable;
                                    break;
                                }
                            }
                        }
                    } else {
                        $args[$k] = $this->getTonicsView()->accessArrayWithSeparator($arg['value']);
                    }
                }

                if ($arg['mode'] === 'block'){
                    $args[$k] = $this->getTonicsView()->renderABlock($arg['value']);
                }
            } else {
                $args[$k] = $arg['value'];
            }
        }

        return $args;
    }
}