<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library\View\CustomTokenizerState\Expression;

use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateTokenizerStateAbstract;
use Devsrealm\TonicsTemplateSystem\TonicsView;

/**
 * DEPRECATED, USE ConditionalTokenizerState instead
 */
class ExpressionTokenizerState extends TonicsTemplateTokenizerStateAbstract
{
    const ExpressionNumericStateHandler = 'ExpressionNumericStateHandler';
    const ExpressionOperatorStateHandler = 'ExpressionOperatorStateHandler';
    private static array $stack;
    /**
     * @var array[]
     */
    private static array $operators = [];
    private static string $topOp = '';
    private static string $bottomOp = '';

    public function __construct()
    {
        self::$stack = [];
        self::$topOp = '';
        self::$bottomOp = '';
        self::$operators = [
            '+' => ['precedence' => 0, 'associativity' => 'left'],
            '-' => ['precedence' => 0, 'associativity' => 'left'],
            '*' => ['precedence' => 1, 'associativity' => 'left'],
            '/' => ['precedence' => 1, 'associativity' => 'left'],
            '%' => ['precedence' => 1, 'associativity' => 'left'],
            '^' => ['precedence' => 2, 'associativity' => 'right'],
        ];
    }

    // "1 + 2 * 3 + 4 OR 8 + 4 + 5 == v[variable.nested.nested]"

    public static function InitialStateHandler(TonicsView $tv): void
    {
        if ($tv->charIsTabOrLFOrFFOrSpace()) {
            return;
        }

        $char = $tv->getChar();
        if ($tv->charIsAsciiDigit()){
            self::consumeUntilDigit($tv);
            return;
        }

        if (self::charIsMinusOrAddition($char) && ($tv->charIsAsciiDigit($tv->nextCharHypothetical()))){
            self::consumeUntilDigit($tv);
            return;
        }

        if ($tv->charIsOperator()){
            if (empty(self::$topOp)){
                self::$topOp = $char;
                $last = array_key_last(self::$stack);
                self::$stack[$last]['nextOperator'] = self::$topOp;
                return;
            }

            // -1 + 1 + 1 * -2
            if (empty(self::$bottomOp)){
                self::$bottomOp = $char;
                $last = array_key_last(self::$stack);
                self::$stack[$last]['nextOperator'] = $char;
                if (self::hasSamePrecedence(self::$topOp, self::$bottomOp)){
                    self::$topOp = self::$bottomOp;
                    self::$bottomOp = '';
                    return;
                }

                if (self::hasLowerPrecedence(self::$topOp, self::$bottomOp)){
                    $dig1 = array_pop(self::$stack);
                    $dig2 = '';
                    $tv->consumeUntil(function ($c) use (&$dig2, $tv){
                        if (self::charIsMinusOrAddition($c)){
                            $dig2 = $c;
                            if ($tv->charIsAsciiDigit($tv->nextCharHypothetical())){
                                $dig2 .= self::consumeUntilDigit($tv, false);
                                dd($dig2);
                                return false;
                            }
                        }
                        if ($tv->charIsTabOrLFOrFFOrSpace($c)){
                            return true;
                        }
                        if (!$tv->charIsAsciiDigit($c)){
                            return false;
                        }
                        $dig2 .= $c;
                        return true;
                    });
                    self::$stack[] = [
                        'value' => self::expExecute($dig1['value'], $dig2, $dig1['nextOperator']),
                        'nextOperator' => '',
                        'type' => 'digit',
                    ];
                    self::$bottomOp = '';
                    return;
                }
            }

        }

        if ($tv->charIsEOF()){
            dd($tv, self::$stack);
        }
    }

    /**
     * @param $char1
     * @param $char2
     * @param string $op
     * @return float|int|string|null
     */
    public static function expExecute($char1, $char2, string $op = '+'): float|int|string|null
    {
        (int)$digit1 = $char1;
        (int)$digit2 = $char2;

        return match ($op) {
            '+' => $digit1 + $digit2,
            '-' => $digit1 - $digit2,
            '*' => $digit1 * $digit2,
            '/' => $digit1 / $digit2,
            '%' => $digit1 % $digit2,
            '^' => $digit1 ^ $digit2,
            default => null,
        };
    }

    public static function consumeUntilDigit(TonicsView $tv, bool $addToStack = true): bool|string
    {
        $charDigit = $tv->getChar();
        $tv->consumeUntil(function ($c) use (&$charDigit, $tv){
            if (!$tv->charIsAsciiDigit($c)){
                return false;
            }
            $charDigit .= $c;
            return true;
        });
        if ($addToStack){
            self::$stack[] = [
                'type' => 'digit',
                'value' => $charDigit,
                'nextOperator' => ''
            ];
        } else {
            return $charDigit;
        }
        return true;
    }

    public static function charIsMinusOrAddition(string $char): bool
    {
        return $char === "+" || $char === "-";
    }


    public static function hasSamePrecedence($op1, $op2): bool|array
    {
        $operators = self::$operators;
        $op1 = $operators[$op1];
        $op2 = $operators[$op2];
        if ($op1['precedence'] === $op2['precedence']){
            return $op1;
        }
        return false;
    }


    /**
     * @param $topOp
     * @param $bottomOp
     * @return bool
     */
    public static function hasLowerPrecedence($topOp, $bottomOp): bool
    {
        $operators = self::$operators;
        $topOp = $operators[$topOp];
        $bottomOp = $operators[$bottomOp];
        return $topOp['precedence'] < $bottomOp['precedence'];
    }

    /**
     * @param $topOp
     * @param $bottomOp
     * @return bool
     */
    public static function hasHigherPrecedence($topOp, $bottomOp): bool
    {
        $operators = self::$operators;
        $topOp = $operators[$topOp];
        $bottomOp = $operators[$bottomOp];
        return $topOp['precedence'] > $bottomOp['precedence'];
    }

    public static function ExpressionNumericStateHandler(TonicsView $tv): void
    {
        // TODO: Implement TonicsTagNameStateHandler() method.
    }

    public static function ExpressionOperatorStateHandler(TonicsView $tv): void
    {
        // TODO: Implement TonicsTagNameStateHandler() method.
    }

    public static function TonicsTagLeftSquareBracketStateHandler(TonicsView $tv): void
    {
        // TODO: Implement TonicsTagLeftSquareBracketStateHandler() method.
    }

    public static function TonicsTagOpenStateHandler(TonicsView $view): void
    {
        // TODO: Implement TonicsTagOpenStateHandler() method.
    }

    public static function TonicsTagNameStateHandler(TonicsView $tv): void
    {
        // TODO: Implement TonicsTagNameStateHandler() method.
    }

    public static function TonicsTagOpenArgValueSingleQuotedStateHandler(TonicsView $tv): void
    {
        // TODO: Implement TonicsTagOpenArgValueSingleQuotedStateHandler() method.
    }

    public static function TonicsTagOpenArgValueDoubleQuotedStateHandler(TonicsView $tv): void
    {
        // TODO: Implement TonicsTagOpenArgValueDoubleQuotedStateHandler() method.
    }

}