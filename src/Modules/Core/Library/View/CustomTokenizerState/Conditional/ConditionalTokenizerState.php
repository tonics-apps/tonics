<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Modules\Core\Library\View\CustomTokenizerState\Conditional;

use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateTokenizerStateAbstract;
use Devsrealm\TonicsTemplateSystem\Exceptions\TonicsTemplateRuntimeException;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class ConditionalTokenizerState extends TonicsTemplateTokenizerStateAbstract
{
    const ExpressionModeState = 'ExpressionModeState';
    const ExpressionBottomOpLowerPrecedenceFindDigitState = 'ExpressionBottomOpLowerPrecedenceFindDigitState';
    const EndOfExpressionState = 'EndOfExpressionState';

    const InitConditionalOperatorState = 'InitConditionalOperatorState';

    const BooleanModeState = 'BooleanModeState';
    const EndOfBooleanState = 'EndOfBooleanState';

    const VariableModeState = 'VariableModeState';
    const EndOfVariableState = 'EndOfVariableState';

    const BlockModeState = 'BlockModeState';
    const EndOfBlockState = 'EndOfBlockState';

    const StringModeState = 'StringModeState';
    const EndOfStringState = 'EndOfStringState';

    const LogicalOperatorState = 'LogicalOperatorState';

    const InitEvaluateState = "InitEvaluateState";
    # The very first two operands
    const EvaluateFirstTypeState = "EvaluateFirstTypeState";

    const AfterEvaluationState = "AfterEvaluationState";

    private static array $stack;
    /**
     * @var array[]
     */
    private static array $operators = [];
    /**
     * @var string[]
     */
    private static array $allowedModeInConditional = [];
    private static string $identifier;
    /**
     * @var null
     */
    private static $evaluationResult;
    /**
     * @var null
     */
    private static $operandA;
    /**
     * @var null
     */
    private static $operandB;
    /**
     * @var null
     */
    private static $operandOperator;

    public static function setupInit()
    {
        self::$stack = [];
        self::$identifier = '';
        self::$evaluationResult = null;
        self::$operandA = null;
        self::$operandOperator = null;
        self::$operandB = null;
        self::$allowedModeInConditional = [
            'exp' => 'exp',
            'expression' => 'expression',
            'e' => 'e',
            'bool' => 'bool',
            'boolean' => 'boolean',
            'block' => 'block',
            'string' => 'string',
            's' => 's',
            'v' => 'v',
            'var' => 'var'
        ];
        self::$operators = [
            '+' => ['precedence' => 0, 'associativity' => 'left'],
            '-' => ['precedence' => 0, 'associativity' => 'left'],
            '*' => ['precedence' => 1, 'associativity' => 'left'],
            '/' => ['precedence' => 1, 'associativity' => 'left'],
            '%' => ['precedence' => 1, 'associativity' => 'left'],
            '^' => ['precedence' => 2, 'associativity' => 'right'],
        ];
    }

    public static function InitialStateHandler(TonicsView $tv): void
    {
        if (empty(self::$allowedModeInConditional)){
            self::setupInit();
        }

        if ($tv->charIsEOF()){
            return;
        }

        if ($tv->stackOfOpenTagIsEmpty()){
            $tv->createNewTagInOpenStackTag("conditional");
        }

        if ($tv->charIsTabOrLFOrFFOrSpace()) {
            return;
        }

        $char = $tv->getChar();
        if ($tv->charIsAsciiAlpha()) {
            $conditionalMode = $char;
            $tv->consumeUntil(function ($c) use (&$conditionalMode, $tv) {
                if ($tv->charIsLeftSquareBracket($c)) {
                    return false;
                }
                $conditionalMode .= $c;
                return true;
            });
            $conditionalMode = strtolower($conditionalMode);

            if (key_exists($conditionalMode, self::$allowedModeInConditional)) {
                $tv->nextCharacterKey(); // skip the leftSquareBracket
                switch (true) {
                    case ($conditionalMode == 'e' || $conditionalMode ==  'exp' || $conditionalMode ==  'expression');
                        $tv->switchState(self::ExpressionModeState);
                        break;
                    case ($conditionalMode ==  'bool' || $conditionalMode ==  'boolean');
                        $tv->switchState(self::BooleanModeState);
                        break;
                    case ($conditionalMode ==  'v' || $conditionalMode ==  'var');
                        $tv->switchState(self::VariableModeState);
                        break;
                    case ($conditionalMode ==  'block');
                        $tv->switchState(self::BlockModeState);
                        break;
                    case ($conditionalMode ==  's' || $conditionalMode ==  'string');
                        $tv->switchState(self::StringModeState);
                        break;
                }
            }
        }
    }

    public static function ExpressionModeState(TonicsView $tv): void
    {
        $char = $tv->getChar();
        if ($tv->charIsAsciiDigit() || $tv->charIsDot()) {
            self::consumeUntilDigit($tv);
        }

        if (self::charIsMinusOrAddition($char)) {
            $nextChar = $tv->nextCharHypothetical();
            if ($tv->charIsAsciiDigit($nextChar)) {
                self::consumeUntilDigit($tv);
            }
        }

        if ($tv->charIsOperator()) {
            $last = array_key_last(self::$stack);
            self::$stack[$last]['nextOperator'] = $char;
            self::$stack[$last]['nextOperatorPrecedence'] = self::$operators[$char]['precedence'];
            return;
        }

        if (count(self::$stack) > 1){
            $keyLast = array_key_last(self::$stack);
            $keyPrev = $keyLast - 1;
            if (!key_exists($keyLast, self::$stack) && !key_exists($keyPrev, self::$stack)){
                return;
            }

            if (isset(self::$stack[$keyPrev]['nextOperatorPrecedence']) && isset(self::$stack[$keyLast]['nextOperatorPrecedence'])){

                if (self::$stack[$keyLast]['nextOperatorPrecedence'] > self::$stack[$keyPrev]['nextOperatorPrecedence']){
                    $tv->switchState(self::ExpressionBottomOpLowerPrecedenceFindDigitState);
                    return;
                }

                if (self::$stack[$keyPrev]['nextOperatorPrecedence'] > self::$stack[$keyLast]['nextOperatorPrecedence']){
                    $dig2 = array_pop(self::$stack);
                    $dig1 = array_pop(self::$stack);
                    self::$stack[] = [
                        'value' => self::expExecute($dig1['value'], $dig2['value'], $dig1['nextOperator']),
                        'nextOperator' => $dig2['nextOperator'],
                        'type' => 'digit',
                        'nextOperatorPrecedence' => self::$operators[$dig2['nextOperator']]['precedence']
                    ];
                    return;
                }

            }
        }

        if ($tv->charIsRightSquareBracket()) {
           $tv->switchState(self::EndOfExpressionState);
        }
    }

    public static function ExpressionBottomOpLowerPrecedenceFindDigitState(TonicsView $tv): void
    {
        if ($tv->charIsTabOrLFOrFFOrSpace()) {
            return;
        }

        if ($tv->charIsRightSquareBracket()) {
            $tv->switchState(self::EndOfExpressionState);
        }

        $char = $tv->getChar();
        if ($tv->charIsAsciiDigit()) {
            self::consumeUntilDigit($tv);
            $dig2 = array_pop(self::$stack);
            $dig1 = array_pop(self::$stack);
            self::$stack[] = [
                'value' => self::expExecute($dig1['value'], $dig2['value'], $dig1['nextOperator']),
                'nextOperator' => '',
                'type' => 'digit',
            ];
            $tv->switchState(self::ExpressionModeState);
            return;
        }

        if (self::charIsMinusOrAddition($char)) {
            if ($tv->charIsAsciiDigit($nextChar = $tv->nextCharHypothetical())) {
                self::consumeUntilDigit($tv);
                $dig2 = array_pop(self::$stack);
                $dig1 = array_pop(self::$stack);
                self::$stack[] = [
                    'value' => self::expExecute($dig1['value'], $dig2['value'], $dig1['nextOperator']),
                    'nextOperator' => '',
                    'type' => 'digit',
                ];
                $tv->switchState(self::ExpressionModeState);
                return;
            }

            throw new TonicsTemplateRuntimeException("Invalid Expression `$char$nextChar`, Expected a Digit After `$char`, e.g `$char`10`");
        }
    }

    public static function EndOfExpressionState(TonicsView $tv): void
    {
        while ($dig1 = array_shift(self::$stack)) {
            $dig2 = array_shift(self::$stack);
            if ($dig2 !== null) {
                array_unshift(self::$stack, [
                    'value' => self::expExecute($dig1['value'], $dig2['value'], $dig1['nextOperator']),
                    'nextOperator' => $dig2['nextOperator'],
                    'type' => 'digit',
                ]);
            } else {
                self::$stack = $dig1;
                break;
            }
        }

        if (!empty(self::$stack)){
            $tv->getLastOpenTag()->addArgs([self::$stack]);
            self::$stack = [];
        }

        $tv->reconsumeIn(self::InitConditionalOperatorState);
    }

    public static function InitConditionalOperatorState(TonicsView $tv): void
    {
        if ($tv->charIsTabOrLFOrFFOrSpace()) {
            return;
        }

        if ($tv->getChar() === '|'){
            $tv->reconsumeIn(self::LogicalOperatorState);
            return;
        }

        if ($tv->getChar() === '&'){
            $tv->reconsumeIn(self::LogicalOperatorState);
            return;
        }

        if ($tv->getChar() === '='){
            if ($tv->consumeMultipleCharactersIf('==')){
                $log = [
                    'type' => 'comparison',
                    'value' => '=='
                ];
                $tv->getLastOpenTag()->addArgs([$log]);
                $tv->switchState(self::InitialStateHandler);
                return;
            }
            if ($tv->consumeMultipleCharactersIf('===')){
                $log = [
                    'type' => 'comparison',
                    'value' => '==='
                ];
                $tv->getLastOpenTag()->addArgs([$log]);
                $tv->switchState(self::InitialStateHandler);
                return;
            }
            $tv->exception(TonicsTemplateRuntimeException::class, ["Invalid Comparison Operator, Expected `==` or `===`"]);
        }

        if ($tv->getChar() === '>'){
            if ($tv->consumeMultipleCharactersIf('>=')){
                $log = [
                    'type' => 'comparison',
                    'value' => '>='
                ];
                $tv->getLastOpenTag()->addArgs([$log]);
                $tv->switchState(self::InitialStateHandler);
                return;
            }
            $log = [
                'type' => 'comparison',
                'value' => '>'
            ];
            $tv->getLastOpenTag()->addArgs([$log]);
            $tv->switchState(self::InitialStateHandler);
            return;
        }

        if ($tv->getChar() === '<'){
            if ($tv->consumeMultipleCharactersIf('>=')){
                $log = [
                    'type' => 'comparison',
                    'value' => '<='
                ];
                $tv->getLastOpenTag()->addArgs([$log]);
                $tv->switchState(self::InitialStateHandler);
                return;
            }
            $log = [
                'type' => 'comparison',
                'value' => '<'
            ];
            $tv->getLastOpenTag()->addArgs([$log]);
            $tv->switchState(self::InitialStateHandler);
            return;
        }

        if ($tv->getChar() === '!'){
            if ($tv->consumeMultipleCharactersIf('!==')){
                $log = [
                    'type' => 'comparison',
                    'value' => '!=='
                ];
                $tv->getLastOpenTag()->addArgs([$log]);
                $tv->switchState(self::InitialStateHandler);
                return;
            }
            if ($tv->consumeMultipleCharactersIf('!=')){
                $log = [
                    'type' => 'comparison',
                    'value' => '!=='
                ];
                $tv->getLastOpenTag()->addArgs([$log]);
                $tv->switchState(self::InitialStateHandler);
                return;
            }
            $tv->exception(TonicsTemplateRuntimeException::class, ["Invalid Comparison Operator, Expected `!=` or `!==`"]);
        }

    }

    public static function BooleanModeState(TonicsView $tv): void
    {
        $char = $tv->getChar();
        if ($tv->charIsTabOrLFOrFFOrSpace()){
            return;
        }

        if ($tv->charIsAsciiAlpha()) {
            self::$identifier .= $char;
            return;
        }

        if ($tv->charIsRightSquareBracket()) {
            $tv->switchState(self::EndOfBooleanState);
            return;
        }
        $tv->exception(TonicsTemplateRuntimeException::class, ["Invalid Boolean Character `$char` Expected `true, or t, or f, or false`"]);
    }

    public static function EndOfBooleanState(TonicsView $tv): void
    {
        $boolName = self::$identifier;
        self::$identifier = strtolower(self::$identifier);
        if (self::$identifier === 'f' || self::$identifier === 'false' || self::$identifier === 't' || self::$identifier === 'true'){
            $log = [
                'type' => 'boolean',
                'value' => !((self::$identifier === 'f' || self::$identifier === 'false'))
            ];
            $tv->getLastOpenTag()->addArgs([$log]);
            $tv->switchState(self::InitConditionalOperatorState);
            self::$identifier = '';
            return;
        }

        $tv->exception(TonicsTemplateRuntimeException::class, ["Invalid Boolean Name `$boolName` Expected `true, or t, or f, or false`"]);
    }

    public static function VariableModeState(TonicsView $tv): void
    {
        $char = $tv->getChar();
        if ($tv->charIsTabOrLFOrFFOrSpace()){
            return;
        }

        if ($tv->charIsRightSquareBracket()) {
            $tv->switchState(self::EndOfVariableState);
            return;
        }
        self::$identifier .= $char;
    }

    public static function EndOfVariableState(TonicsView $tv): void
    {
        $log = [
            'type' => 'mode',
            'modeType' => 'var',
            'value' => self::$identifier
        ];
        $tv->getLastOpenTag()->addArgs([$log]);
        $tv->switchState(self::InitConditionalOperatorState);
        self::$identifier = '';
    }

    public static function BlockModeState(TonicsView $tv): void
    {
        $char = $tv->getChar();
        if ($tv->charIsTabOrLFOrFFOrSpace()){
            return;
        }

        if ($tv->charIsRightSquareBracket()) {
            $tv->switchState(self::EndOfBlockState);
            return;
        }

        self::$identifier .= $char;
    }

    public static function EndOfBlockState(TonicsView $tv): void
    {
        $log = [
            'type' => 'mode',
            'modeType' => 'block',
            'value' => self::$identifier
        ];
        $tv->getLastOpenTag()->addArgs([$log]);
        $tv->switchState(self::InitConditionalOperatorState);
        self::$identifier = '';
    }

    public static function StringModeState(TonicsView $tv): void
    {
        $char = $tv->getChar();
        if ($tv->charIsTabOrLFOrFFOrSpace()){
            return;
        }

        if ($tv->charIsRightSquareBracket()) {
            $tv->switchState(self::EndOfStringState);
            return;
        }

        self::$identifier .= $char;
    }

    public static function EndOfStringState(TonicsView $tv): void
    {
        $log = [
            'type' => 'mode',
            'modeType' => 'string',
            'value' => self::$identifier
        ];
        $tv->getLastOpenTag()->addArgs([$log]);
        $tv->switchState(self::InitConditionalOperatorState);
        self::$identifier = '';
    }

    public static function LogicalOperatorState(TonicsView $tv): void
    {
        if ($tv->charIsTabOrLFOrFFOrSpace()){
            return;
        }

        $char = $tv->getChar();
        if ($tv->getChar() === '|'){
            if ($tv->consumeMultipleCharactersIf('||')){
                $log = [
                    'type' => 'logical',
                    'value' => '||'
                ];
                $tv->getLastOpenTag()->addArgs([$log]);
                $tv->switchState(self::InitialStateHandler);
                return;
            }
            $tv->exception(TonicsTemplateRuntimeException::class, ["Invalid Logical Operator, Expected `||`"]);
        }

        if ($tv->getChar() === '&'){
            if ($tv->consumeMultipleCharactersIf('&&')){
                $log = [
                    'type' => 'logical',
                    'value' => '&&'
                ];
                $tv->getLastOpenTag()->addArgs([$log]);
                $tv->switchState(self::InitialStateHandler);
                return;
            }
            $tv->exception(TonicsTemplateRuntimeException::class, ["Invalid Logical Operator, Expected `&&`"]);
        }

        $tv->exception(TonicsTemplateRuntimeException::class, ["Expected a logical operator, `$char` given"]);
    }

    ## Start Evaluation
    public static function InitEvaluateState(TonicsView $tv)
    {

        if ($tv->charIsEOF()){
           return;
        }

        if (empty(self::$allowedModeInConditional)){
            self::setupInit();
        }

        self::$operandA = null;
        self::$operandB = null;
        self::$operandOperator = null;

        $token = $tv->getChar();

        if ($token['type'] === 'digit'){
            self::$operandA = $token['value'];
            $tv->switchState(self::EvaluateFirstTypeState);
            return;
        }

        if ($token['type'] === 'boolean'){
            self::$operandA = $token['value'];
            $tv->switchState(self::EvaluateFirstTypeState);
            return;
        }

        if ($token['type'] === 'mode'){
            self::$operandA = self::getModeOperandData($token, $tv);
            $tv->switchState(self::EvaluateFirstTypeState);
        }

    }

    public static function EvaluateFirstTypeState(TonicsView $tv)
    {
        if ($tv->charIsEOF()){
            $storage = $tv->getModeStorage('if');
            $storage['result'] = (bool)self::$operandA;
            $tv->storeDataInModeStorage('if', $storage);
            return;
        }

        $token = $tv->getChar();
        $opA = self::$operandA;
        if ($token['type'] === 'comparison' || $token['type'] === 'logical'){
            self::$operandOperator = $token['value'];
            $nextChar = $tv->nextCharHypothetical(function () use ($opA, $tv) {
                $tv->exception(TonicsTemplateRuntimeException::class, ["No Operand Type After Digit `$opA` "]);
            });

            if (isset($nextChar['type'])){
                if ($nextChar['type'] === 'mode'){
                    self::$operandB = self::getModeOperandData($nextChar, $tv);
                } elseif ($nextChar['type'] === 'digit' || $nextChar['type'] === 'boolean'){
                    self::$operandB = $nextChar['value'];
                }
            } else {
                $nextChar = isset($nextChar['type']) ? $nextChar['type'] : $nextChar;
                $tv->exception(TonicsTemplateRuntimeException::class, ["Invalid Operand Type:  `$opA {$token['value']} {$nextChar}`"]);
            }

            $tv->nextCharacterKey();
            self::$evaluationResult = (bool)self::expExecute(self::$operandA, self::$operandB, self::$operandOperator);
            $tv->switchState(self::AfterEvaluationState);
        }
    }

    public static function AfterEvaluationState(TonicsView $tv)
    {
        if ($tv->charIsEOF()){
            $storage = $tv->getModeStorage('if');
            $storage['result'] = self::$evaluationResult;
            $tv->storeDataInModeStorage('if', $storage);
            return;
        }

        $token = $tv->getChar();

        ## Short-Circuit
        if ($token['type'] === 'logical' && $token['value'] === '||' && self::$evaluationResult){
            $storage = $tv->getModeStorage('if');
            $storage['result'] = self::$evaluationResult;
            $tv->storeDataInModeStorage('if', $storage);
            $tv->setEndTokenization(true);
            return;
        }

        if ($token['type'] === 'logical' || $token['type'] === 'comparison'){
            $nextChar = $tv->nextCharHypothetical(function () use ($token, $tv) {
                $tv->exception(TonicsTemplateRuntimeException::class, ["No Operand Type After `{$token['value']}` "]);
            });


            self::$operandA = self::$evaluationResult;
            $opA = self::$evaluationResult;
            if ($nextChar['type'] === 'mode'){
                self::$operandB = self::getModeOperandData($nextChar, $tv);
            }elseif ($nextChar['type'] === 'digit' || $nextChar['type'] === 'boolean'){
                self::$operandB =  $nextChar['value'];
            } else {
                $tv->exception(TonicsTemplateRuntimeException::class, ["Invalid Operand Type:  `$opA {$token['value']} {$nextChar['type']}`"]);
            }

            self::$evaluationResult = (bool) self::expExecute(self::$operandA, self::$operandB, $token['value']);
            $tv->nextCharacterKey();
            $tv->switchState(self::AfterEvaluationState);
            return;
        }

        $tv->exception(TonicsTemplateRuntimeException::class, ["Invalid Operand Type After"]);
    }

    /**
     * @param $token
     * @param TonicsView $tv
     * @return mixed|string|void
     */
    public static function getModeOperandData($token, TonicsView $tv)
    {
        if ($token['modeType'] === 'block'){
            return self::getBlockData($token['value'], $tv);
        }

        if ($token['modeType'] === 'string'){
            return $token['value'];
        }

        if ($token['modeType'] === 'var'){
            return  self::getVariableData($token['value'], $tv);
        }
    }


    /**
     * @param string $blockName
     * @param TonicsView $tv
     * @return string
     */
    public static function getBlockData(string $blockName, TonicsView $tv): string
    {
        return $tv->renderABlock($blockName);
    }

    /**
     * @param string $variable
     * @param TonicsView $tv
     * @return mixed
     */
    public static function getVariableData(string $variable, TonicsView $tv): mixed
    {
        return $tv->accessArrayWithSeparator($variable);
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
            '==' => $digit1 == $digit2,
            '===' => $digit1 === $digit2,
            '>=' => $digit1 >= $digit2,
            '>' => $digit1 > $digit2,
            '<=' => $digit1 <= $digit2,
            '<' => $digit1 < $digit2,
            '!==' => $digit1 !== $digit2,
            '||' => $digit1 || $digit2,
            '&&' => $digit1 && $digit2,
            default => null,
        };
    }

    public static function consumeUntilDigit(TonicsView $tv, bool $addToStack = true): bool|string
    {
        $charDigit = $tv->getChar();
        $tv->consumeUntil(function ($c) use (&$charDigit, $tv) {
            if ($tv->charIsDot($c)){
                $charDigit .= $c;
                return true;
            }
            if (!$tv->charIsAsciiDigit($c)) {
                return false;
            }
            $charDigit .= $c;
            return true;
        });
        if ($addToStack) {
            ## Replace multiple dot with one
            $charDigit = preg_replace('/\.{2,}/', '.', $charDigit);
            // (str_contains($charDigit, '.')) ? (float)$charDigit : (int)$charDigit
            $array = [
                'type' => 'digit',
                'value' => ($charDigit === '.') ? null : $charDigit,
                'nextOperator' => ''
            ];
            $array['value'] = str_contains( $array['value'], '.') ? (float) $array['value'] : (int) $array['value'];
            self::$stack[] = $array;
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
        if ($op1['precedence'] === $op2['precedence']) {
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