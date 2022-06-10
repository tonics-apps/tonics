<?php

namespace App\Modules\Core\Library\View\CustomTokenizerState;

use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateTokenizerStateAbstract;
use Devsrealm\TonicsTemplateSystem\Node\Tag;
use Devsrealm\TonicsTemplateSystem\Tokenizer\Token\Token;
use Devsrealm\TonicsTemplateSystem\TonicsView;

/**
 * NOTE: IN State that test against ASCII-ALPHA, EOF should always be above, this is because I use 'EOF' to signify we are at the bottom of the
 * character stack, failure to do so, would disrupt the state.
 *
 * Note: Your tagname should never be named char, this is an inbuilt tag name.
 */
class WordPressShortCodeTokenizerState extends TonicsTemplateTokenizerStateAbstract
{

    private static string $currentArgKey = '';

    const InitialStateHandler = 'InitialStateHandler';
    const WordPressShortCodeOpenTagStateHandler = 'WordPressShortCodeOpenTagStateHandler';
    const WordPressShortCodeRawStateHandler = 'WordPressShortCodeRawStateHandler';
    const WordPressShortCodeTagNameStateHandler = 'WordPressShortCodeTagNameStateHandler';
    const WordPressShortCodeClosingOpenTagStateHandler = 'WordPressShortCodeClosingOpenTagStateHandler';
    const WordPressShortCodeSelfCloseTagStateHandler = 'WordPressShortCodeSelfCloseTagStateHandler';
    const WordPressShortCodeOpenAttributeStateHandler = 'WordPressShortCodeOpenAttributeStateHandler';
    const WordPressShortCodeOpenAttributeValue = 'WordPressShortCodeOpenAttributeValue';
    const WordPressShortCodeSingleQuotedValueStateHandler = 'WordPressShortCodeSingleQuotedValueStateHandler';
    const WordPressShortCodeDoubleQuotedValueStateHandler = 'WordPressShortCodeDoubleQuotedValueStateHandler';
    const WordPressShortCodeArgValueStateHandler = 'WordPressShortCodeArgValueStateHandler';
    const WordPressShortCodeAfterArgValueQuotedStateHandler = 'WordPressShortCodeAfterArgValueQuotedStateHandler';
    const WordPressShortCodeClosingCloseTagStateHandler = 'WordPressShortCodeClosingCloseTagStateHandler';
    const WordPressShortCodeClosingCloseTagNameStateHandler = 'WordPressShortCodeClosingCloseTagNameStateHandler';
    const WordPressShortCodeNoQuoteValueStateHandler = 'WordPressShortCodeNoQuoteValueStateHandler';

    public static function InitialStateHandler(TonicsView $tv): void
    {
        if ($tv->charIsLeftSquareBracket()) {
            $tv->emit(Token::Character, false, self::handleEmission(Token::Character, $tv));
            $tv->switchState(self::WordPressShortCodeOpenTagStateHandler);
            return;
        }

        if ($tv->charIsEOF()) {
            self::finalEOFStackSort($tv);
            return;
        }

        $tv->appendToCharacterToken($tv->getChar());
    }

    public static function WordPressShortCodeOpenTagStateHandler(TonicsView $tv)
    {
        if ($tv->charIsLeftSquareBracket()) {
            $tv->reconsumeIn(self:: WordPressShortCodeRawStateHandler);
            return;
        }

        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscore()) {
            $tv->createNewTagInOpenStackTag('')
                ->reconsumeIn(self::WordPressShortCodeTagNameStateHandler);
        }

        if ($tv->charIsForwardSlash()) {
            $tv->switchState(self::WordPressShortCodeClosingCloseTagNameStateHandler);
        }
    }

    public static function WordPressShortCodeRawStateHandler(TonicsView $tv)
    {
        if ($tv->charIsRightSquareBracket()) {
            $totalOpenRawTag = 1 + $tv->getNumberOfOpenRawSigilFoundInBeforeEncounteringAChar();
            if ($tv->consumeMultipleCharactersIf(str_repeat(']', $totalOpenRawTag))) {
                $tv->setNumberOfOpenRawSigilFoundInBeforeEncounteringAChar(0);
                $tv->setStopIncrementingOpenTagSigilCharacter(false);
                $tv->emit(Token::Character, false, self::handleEmission(Token::Character, $tv));
                $tv->switchState(self::InitialStateHandler);
                return;
            }
        }

        if ($tv->charIsLeftSquareBracket()) {
            if ($tv->hasStoppedIncrementingOpenTagSigilCharacter() === false) {
                $tv->incrementNumberOfOpenRawSigilFoundInBeforeEncounteringAChar();
                return;
            }
        }

        $tv->appendToCharacterToken($tv->getChar());
        $tv->setStopIncrementingOpenTagSigilCharacter(true);
    }

    public static function WordPressShortCodeTagNameStateHandler(TonicsView $tv)
    {
        $char = $tv->getChar();
        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscore()) {
            $tv->appendCharToCurrentTokenTagName($char);
            return;
        }

        if ($tv->charIsTabOrLFOrFFOrSpace()) {
            $tv->switchState(self::WordPressShortCodeOpenAttributeStateHandler);
            return;
        }

        if ($tv->charIsRightSquareBracket()) {
            $tv->incrementSigilCounter();
            $tv->switchState(self::WordPressShortCodeClosingOpenTagStateHandler);
            return;
        }

        if ($tv->charIsForwardSlash()) {
            $tv->switchState(self::WordPressShortCodeSelfCloseTagStateHandler);
        }
    }

    public static function WordPressShortCodeOpenAttributeStateHandler(TonicsView $tv)
    {
        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscore()) {
            self::appendCurrentTokenTagArgKey($tv->getChar());
            return;
        }

        if ($tv->charIsTabOrLFOrFFOrSpace()) {
            $tv->switchState(self::WordPressShortCodeArgValueStateHandler);
            return;
        }

        if ($tv->charIsRightSquareBracket()) {
            $tv->appendCharToArgValue(self::$currentArgKey);
            self::$currentArgKey = '';
            $tv->incrementSigilCounter();
            $tv->switchState(self::WordPressShortCodeClosingOpenTagStateHandler);
            return;
        }

        if ($tv->charIsEqual()) {
            $tv->switchState(self::WordPressShortCodeOpenAttributeValue);
        }
    }

    public static function WordPressShortCodeArgValueStateHandler(TonicsView $tv)
    {
        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscore()) {
            $tv->appendCharToArgValue(self::$currentArgKey);
            self::$currentArgKey = '';
            $tv->startNewArgsInCurrentTagToken();
            $tv->reconsumeIn(self::WordPressShortCodeOpenAttributeStateHandler);
            return;
        }

        if ($tv->charIsTabOrLFOrFFOrSpace()) {
            return;
        }
    }

    public static function WordPressShortCodeOpenAttributeValue(TonicsView $tv)
    {
        if ($tv->charIsTabOrLFOrFFOrSpace()) {
            return;
        }

        if ($tv->charIsApostrophe()) {
            $tv->startNewArgsInCurrentTagToken();
            $tv->switchState(self::WordPressShortCodeSingleQuotedValueStateHandler);
            return;
        }

        if ($tv->charIsQuotationMark()) {
            $tv->startNewArgsInCurrentTagToken();
            $tv->switchState(self::WordPressShortCodeDoubleQuotedValueStateHandler);
            return;
        }

        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscore()) {
            $tv->startNewArgsInCurrentTagToken();
            $tv->reconsumeIn(self::WordPressShortCodeNoQuoteValueStateHandler);
        }
    }

    public static function WordPressShortCodeNoQuoteValueStateHandler(TonicsView $tv)
    {
        if ($tv->charIsRightSquareBracket()) {
            $tv->replaceCurrentTokenTagArgKey(self::$currentArgKey);
            self::$currentArgKey = '';
            $tv->incrementSigilCounter();
            $tv->switchState(self::WordPressShortCodeClosingOpenTagStateHandler);
            return;
        }

        if ($tv->charIsTabOrLFOrFFOrSpace()) {
            $tv->replaceCurrentTokenTagArgKey(self::$currentArgKey);
            self::$currentArgKey = '';
            $tv->switchState(self::WordPressShortCodeOpenAttributeStateHandler);
            return;
        }

        $tv->appendCharToArgValue($tv->getChar());
    }

    public static function WordPressShortCodeSingleQuotedValueStateHandler(TonicsView $tv)
    {
        if ($tv->charIsApostrophe()) {
            $tv->replaceCurrentTokenTagArgKey(self::$currentArgKey);
            self::$currentArgKey = '';
            $tv->switchState(self::WordPressShortCodeAfterArgValueQuotedStateHandler);
            return;
        }

        $tv->appendCharToArgValue($tv->getChar());
    }

    public static function WordPressShortCodeDoubleQuotedValueStateHandler(TonicsView $tv)
    {
        if ($tv->charIsQuotationMark()) {
            $tv->replaceCurrentTokenTagArgKey(self::$currentArgKey);
            self::$currentArgKey = '';
            $tv->switchState(self::WordPressShortCodeAfterArgValueQuotedStateHandler);
            return;
        }

        $tv->appendCharToArgValue($tv->getChar());
    }

    public static function WordPressShortCodeAfterArgValueQuotedStateHandler(TonicsView $tv)
    {
        if ($tv->charIsTabOrLFOrFFOrSpace()) {
            $tv->switchState(self::WordPressShortCodeOpenAttributeStateHandler);
            return;
        }

        if ($tv->charIsForwardSlash()) {
            $tv->switchState(self::WordPressShortCodeSelfCloseTagStateHandler);
            return;
        }

        if ($tv->charIsRightSquareBracket()) {
            $tv->incrementSigilCounter();
            $tv->switchState(self::WordPressShortCodeClosingOpenTagStateHandler);
        }
    }

    public static function WordPressShortCodeSelfCloseTagStateHandler(TonicsView $tv)
    {
        if ($tv->charIsRightSquareBracket()) {
            $tv->decrementSigilCounter();
            $tv->switchState(self::InitialStateHandler);
            self::closeLastCurrentTokenTagState($tv);
            $tv->emit(Token::Tag, false, self::handleEmission(Token::Tag, $tv));
        }
    }

    public static function WordPressShortCodeClosingOpenTagStateHandler(TonicsView $tv)
    {
        if ($tv->consumeMultipleCharactersIf('[/')) {
            $tv->switchState(self::WordPressShortCodeClosingCloseTagNameStateHandler);
            return;
        }

        if ($tv->charIsLeftSquareBracket()) {
            $tv->reconsumeIn(self::InitialStateHandler);
            return;
        }

        if ($tv->charIsEOF()) {
            self::finalEOFStackSort($tv);
            return;
        }

        $tv->appendCharToCurrentTokenTagContent($tv->getChar());
    }

    /**
     * We are at the end of the tag, we do not really care if the opening tagname
     * is same as the closing tagName, e.g [one][/oneeee] is fine.
     *
     * Checking for the opening and closing name is fine if tags are not nested, but since they
     * might be nested, we just ignore saving us time and shielding us from a bit of complexity
     * @param TonicsView $tv
     */
    public static function WordPressShortCodeClosingCloseTagNameStateHandler(TonicsView $tv)
    {
        // we are at the end of the tag, we don't really care if the openin
        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscore()){
            return;
        }

        if ($tv->charIsRightSquareBracket()){
            $tv->reconsumeIn(self::WordPressShortCodeClosingCloseTagStateHandler);
        }
    }

    public static function WordPressShortCodeClosingCloseTagStateHandler(TonicsView $tv)
    {
        if ($tv->charIsRightSquareBracket()) {
            $tv->decrementSigilCounter();
            self::closeLastCurrentTokenTagState($tv);
            $tv->emit(Token::Tag, false, self::handleEmission(Token::Tag, $tv));
            $tv->switchState(self::InitialStateHandler);
        }
    }

    private static function appendCurrentTokenTagArgKey(string $character): void
    {
        self::$currentArgKey .= $character;
    }

    private static function handleEmission(string $toEmit, TonicsView $tv): void
    {
        if ($toEmit === Token::Character) {
            if (!empty($tv->getCharacterToken()['data'])) {
                $tv->createNewTagInOpenStackTag('char');
                $tv->appendCharToCurrentTokenTagContent($tv->getCharacterToken()['data']);
                $tv->clearCharacterTokenData();
                self::closeLastCurrentTokenTagState($tv);
                $tv->setLastEmitted($toEmit);
                self::sortStackOfOpenTagEl($tv);
            }
        }

        if ($toEmit === Token::Tag) {
            self::sortStackOfOpenTagEl($tv);
            $tv->setLastEmitted($toEmit);
        }
    }

    public static function sortStackOfOpenTagEl(TonicsView $tv)
    {
        if ($tv->getSigilCounter() > 0) {
            $revs = $tv->reverseStackOfOpenTagEl();
            /**@var Tag $tag */
            $closed = [];
            foreach ($revs as $key => &$tag) {
                if ($tag->isCloseState()) {
                    array_unshift($closed, $tag);
                    $tv->unsetKeyInStackOfOpenEl($key);
                }

                if ($tag->isOpenState() && !empty($closed)) {
                    $tag->appendChildren($closed);
                    $tv->addTagInStackOfOpenElKey($key, $tag);
                    /**@var Tag $t */
                    foreach ($closed as $t) {
                        $t->setParentNode($tag);
                    }
                    break;
                }
            }
        }
    }

    private static function finalEOFStackSort(TonicsView $tv)
    {
        foreach ($tv->getStackOfOpenTagEl() as $key => $tag){
            /**@var Tag $tag */
            if ($tag->hasNoChildren()){
                continue;
            }

            if ($tag->isCloseState() === false && $tag->hasChildren()){
                $children = $tag->childNodes();
                if (!empty($children)){
                    $children[0]->setParentNode(null);
                }
                $tag->clearNodes();
                $tv->addElementInStackOfOpenPosition($children, $key + 1);
            }
        }
        dd($tv);
    }

    private static function closeLastCurrentTokenTagState(TonicsView $tv): void
    {
        $tv->getLastOpenTag()->setCloseState(true);
    }

    public static function TonicsTagLeftSquareBracketStateHandler(TonicsView $tonicsView): void
    {
        return;
    }

    public static function TonicsTagOpenStateHandler(TonicsView $view): void
    {
        return;
    }

    public static function TonicsTagNameStateHandler(TonicsView $tonicsView): void
    {
        return;
    }

    public static function TonicsTagOpenArgValueSingleQuotedStateHandler(TonicsView $tonicsView): void
    {
        return;
    }

    public static function TonicsTagOpenArgValueDoubleQuotedStateHandler(TonicsView $tonicsView): void
    {
        return;
    }
}