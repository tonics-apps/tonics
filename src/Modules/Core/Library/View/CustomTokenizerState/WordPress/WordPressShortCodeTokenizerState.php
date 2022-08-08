<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library\View\CustomTokenizerState\WordPress;

use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateTokenizerStateAbstract;
use Devsrealm\TonicsTemplateSystem\Node\Tag;
use Devsrealm\TonicsTemplateSystem\Tokenizer\Token\Token;
use Devsrealm\TonicsTemplateSystem\TonicsView;

/**
 * NOTE: IN State that test against ASCII-ALPHA, EOF should always be above, this is because I use 'EOF' to signify we are at the bottom of the
 * character stack, failure to do so, would disrupt the state.
 *
 * Note: The following are list of known quirks (would be updated as long as more is found):
 *
 * - No quirks found for now...
 *
 */
class WordPressShortCodeTokenizerState extends TonicsTemplateTokenizerStateAbstract
{

    private static string $currentArgKey = '';
    private static string $rawTagName = '';
    private static string $nestedTagName = '';
    private static string $invalidTagName = '';
    private static string $tagNameInOpenTagState = '';
    private static string $tagNameInCloseTagState = '';

    const WordPressShortCodeOpenTagStateHandler = 'WordPressShortCodeOpenTagStateHandler';
    const WordPressShortCodeRawStateHandler = 'WordPressShortCodeRawStateHandler';
    const WordPressShortCodeTagNameStateHandler = 'WordPressShortCodeTagNameStateHandler';
    const WordPressShortCodeClosingOpenTagStateHandler = 'WordPressShortCodeClosingOpenTagStateHandler';
    const WordPressShortCodeSelfCloseTagStateHandler = 'WordPressShortCodeSelfCloseTagStateHandler';
    const WordPressShortCodeOpenAttributeStateHandler = 'WordPressShortCodeOpenAttributeStateHandler';
    const WordPressShortCodeOpenAttributeValueStateHandler = 'WordPressShortCodeOpenAttributeValueStateHandler';
    const WordPressShortCodeSingleQuotedValueStateHandler = 'WordPressShortCodeSingleQuotedValueStateHandler';
    const WordPressShortCodeDoubleQuotedValueStateHandler = 'WordPressShortCodeDoubleQuotedValueStateHandler';
    const WordPressShortCodeArgValueStateHandler = 'WordPressShortCodeArgValueStateHandler';
    const WordPressShortCodeArgKeyStateHandler = 'WordPressShortCodeArgKeyStateHandler';
    const WordPressShortCodeAfterArgValueStateHandler = 'WordPressShortCodeAfterArgValueStateHandler';
    const WordPressShortCodeClosingCloseTagStateHandler = 'WordPressShortCodeClosingCloseTagStateHandler';
    const WordPressShortCodeClosingCloseTagNameStateHandler = 'WordPressShortCodeClosingCloseTagNameStateHandler';
    const WordPressShortCodeNoQuoteValueStateHandler = 'WordPressShortCodeNoQuoteValueStateHandler';
    const WordPressShortCodeCanTagBeNestedStateHandler = 'WordPressShortCodeCanTagBeNestedStateHandler';
    const WordPressShortCodeRawContentsStateHandler = 'WordPressShortCodeRawContentsStateHandler';
    const WordPressShortCodeRawEncounterRightSquareBracketStateHandler = 'WordPressShortCodeRawEncounterRightSquareBracketStateHandler';
    const WordPressShortCodeRawAfterEncounteringRightSquareBracketStateHandler = 'WordPressShortCodeRawAfterEncounteringRightSquareBracketStateHandler';


    public static function InitialStateHandler(TonicsView $tv): void
    {
        if ($tv->charIsEOF()) {
            return;
        }

        if ($tv->charIsLeftSquareBracket()) {
            # E.G. [b
            if ($nextChar = $tv->nextCharHypothetical()){
                if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscore($nextChar)){
                    self::handleEmission(Token::Character, $tv);
                    $tv->switchState(self::WordPressShortCodeOpenTagStateHandler);
                    return;
                }
                # E.G. [/
                if ($tv->charIsForwardSlash($nextChar)) {
                    $tv->switchState(self::WordPressShortCodeClosingCloseTagNameStateHandler);
                    return;
                }
                # E.G. [[
                if ($tv->charIsLeftSquareBracket($nextChar)) {
                    self::handleEmission(Token::Character, $tv);
                    $tv->switchState(self::WordPressShortCodeRawStateHandler);
                    return;
                }
            }
        }

        $tv->appendToCharacterToken($tv->getChar());
    }

    public static function WordPressShortCodeOpenTagStateHandler(TonicsView $tv)
    {
        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscoreOrDash()) {
            self::$tagNameInOpenTagState = '';
            $tv->reconsumeIn(self::WordPressShortCodeTagNameStateHandler);
        }
    }

    public static function WordPressShortCodeRawStateHandler(TonicsView $tv)
    {
        if ($tv->charIsEOF()){
            return;
        }

        if (key_exists(self::$rawTagName, $tv->getModeHandler())) {
            self::$rawTagName = '';
            $tv->appendToCharacterToken(self::$rawTagName);
            $tv->reconsumeIn(self::WordPressShortCodeRawContentsStateHandler);
            return;
        }

        if ($tv->charIsRightSquareBracket()){
            $tv->appendToCharacterToken($tv->getChar());
            $tv->switchState(self::WordPressShortCodeRawEncounterRightSquareBracketStateHandler);
            return;
        }

        # We do not and should not return here, we let it get appended to characterTokenData too
        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscoreOrDash()) {
            self::appendCurrentCharToRawTagName($tv->getChar());
        }

        $tv->appendToCharacterToken($tv->getChar());
    }

    public static function WordPressShortCodeRawEncounterRightSquareBracketStateHandler(TonicsView $tv)
    {
        if ($tv->charIsRightSquareBracket()){
            $tv->prependAndAppendToCharacterToken('[', ']');
            $tv->switchState(self::WordPressShortCodeRawAfterEncounteringRightSquareBracketStateHandler);
            return;
        }

        $tv->appendToCharacterToken($tv->getChar());
    }

    public static function WordPressShortCodeRawAfterEncounteringRightSquareBracketStateHandler(TonicsView $tv)
    {
        if ($tv->charIsLeftSquareBracket()){
            if ($next = $tv->nextCharHypothetical()){
                if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscore($next)){
                    $tv->reconsumeIn(self::InitialStateHandler);
                }
            }
        }

        $tv->appendToCharacterToken($tv->getChar());
    }

    public static function WordPressShortCodeRawContentsStateHandler(TonicsView $tv)
    {
        if ($tv->consumeMultipleCharactersIf(']]')){
            $tv->appendToCharacterToken($tv->getChar());
            $tv->switchState(self::InitialStateHandler);
            return;
        }
        $tv->appendToCharacterToken($tv->getChar());
    }

    public static function WordPressShortCodeTagNameStateHandler(TonicsView $tv)
    {
        if ($tv->charIsEOF()) {
            return;
        }

        $char = $tv->getChar();
        self::$tagNameInOpenTagState .= $char;
        if (key_exists(self::$tagNameInOpenTagState, $tv->getModeHandler())) {
            $tv->createNewTagInOpenStackTag(self::$tagNameInOpenTagState)
                ->switchState(self::WordPressShortCodeOpenAttributeStateHandler);
            return;
        }

        if ($tv->charIsLeftSquareBracket()) {
            if ($char = $tv->nextCharHypothetical()){
                if (!$tv->charIsAsciiAlphaOrAsciiDigitOrUnderscoreOrDash($char)){
                    if (empty($tv->getStackOfOpenTagEl())) {
                        $tv->appendToCharacterToken('[' . self::$tagNameInOpenTagState);
                    } else {
                        $tv->getLastCreateTag()->appendCharacterToContent('[' . self::$tagNameInOpenTagState);
                    }
                    $tv->switchState(self::InitialStateHandler);
                    return;
                }
            }
            self::$nestedTagName = '';
            $tv->reconsumeIn(self::WordPressShortCodeCanTagBeNestedStateHandler);
            return;
        }

        if ($tv->charIsRightSquareBracket()){
            if (empty($tv->getStackOfOpenTagEl())) {
                $tv->appendToCharacterToken('[' . self::$tagNameInOpenTagState);
            } else {
                $tv->getLastCreateTag()->appendCharacterToContent('[' . self::$tagNameInOpenTagState);
            }
            $tv->switchState(self::InitialStateHandler);
            return;
        }

        if ($tv->charIsForwardSlash()) {
            if ($char = $tv->nextCharHypothetical()){
                if ($tv->charIsRightSquareBracket($char)){
                    $tv->switchState(self::WordPressShortCodeSelfCloseTagStateHandler);
                }
            }
        }
    }

    public static function WordPressShortCodeCanTagBeNestedStateHandler(TonicsView $tv)
    {
        if ($tv->charIsEOF()) {
            $tv->getLastCreateTag()->appendCharacterToContent('[' . self::$nestedTagName);
            return;
        }

        $char = $tv->getChar();
        self::appendNestedTagName($char);

        if (key_exists(self::$nestedTagName, $tv->getModeHandler())) {
            $tv->createNewTagInOpenStackTag(self::$nestedTagName)
                ->switchState(self::WordPressShortCodeOpenAttributeStateHandler);
            self::$nestedTagName = '';
            return;
        }

        if ($tv->charIsRightSquareBracket()) {
            $tv->appendCharToCurrentTokenTagContent('[' . self::$nestedTagName);
            $tv->switchState(self::WordPressShortCodeClosingOpenTagStateHandler);
        }

    }

    public static function WordPressShortCodeOpenAttributeStateHandler(TonicsView $tv)
    {
        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscoreOrDash()) {
            $tv->reconsumeIn(self::WordPressShortCodeArgKeyStateHandler);
            return;
        }

        if ($tv->charIsTabOrLFOrFFOrSpace()) {
            return;
        }

        if ($tv->charIsRightSquareBracket()) {
            $tv->appendCharToArgValue(self::$currentArgKey);
            self::$currentArgKey = '';
            $tv->incrementSigilCounter();
            $tv->switchState(self::WordPressShortCodeClosingOpenTagStateHandler);
        }
    }

    public static function WordPressShortCodeArgKeyStateHandler(TonicsView $tv)
    {
        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscoreOrDash()) {
            self::appendCurrentTokenTagArgKey($tv->getChar());
            return;
        }

        if ($tv->charIsRightSquareBracket()) {
            $tv->startNewArgsInCurrentTagToken(self::$currentArgKey);
            self::$currentArgKey = '';
            $tv->incrementSigilCounter();
            $tv->switchState(self::WordPressShortCodeClosingOpenTagStateHandler);
            return;
        }

        if ($tv->charIsEqual()) {
            $tv->switchState(self::WordPressShortCodeOpenAttributeValueStateHandler);
        }

        if ($tv->charIsTabOrLFOrFFOrSpace()) {
            $tv->switchState(self::WordPressShortCodeArgValueStateHandler);
        }
    }

    public static function WordPressShortCodeArgValueStateHandler(TonicsView $tv)
    {
        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscoreOrDash()) {
            $tv->startNewArgsInCurrentTagToken(self::$currentArgKey);
            self::$currentArgKey = '';
            $tv->reconsumeIn(self::WordPressShortCodeOpenAttributeStateHandler);
            return;
        }
    }

    public static function WordPressShortCodeOpenAttributeValueStateHandler(TonicsView $tv)
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

        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscoreOrDash()) {
            $tv->startNewArgsInCurrentTagToken();
            $tv->reconsumeIn(self::WordPressShortCodeNoQuoteValueStateHandler);
        }
    }

    public static function WordPressShortCodeNoQuoteValueStateHandler(TonicsView $tv)
    {
        if ($tv->charIsRightSquareBracket()) {
            $tv->replaceCurrentTokenTagArgKey(self::$currentArgKey);
            self::$currentArgKey = '';
            $tv->reconsumeIn(self::WordPressShortCodeAfterArgValueStateHandler);
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
            $tv->switchState(self::WordPressShortCodeAfterArgValueStateHandler);
            return;
        }

        $tv->appendCharToArgValue($tv->getChar());
    }

    public static function WordPressShortCodeDoubleQuotedValueStateHandler(TonicsView $tv)
    {
        if ($tv->charIsQuotationMark()) {
            $tv->replaceCurrentTokenTagArgKey(self::$currentArgKey);
            self::$currentArgKey = '';
            $tv->switchState(self::WordPressShortCodeAfterArgValueStateHandler);
            return;
        }

        $tv->appendCharToArgValue($tv->getChar());
    }

    public static function WordPressShortCodeAfterArgValueStateHandler(TonicsView $tv)
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
            self::handleEmission(Token::Tag, $tv);
        }
    }

    public static function WordPressShortCodeClosingOpenTagStateHandler(TonicsView $tv)
    {
        if ($tv->charIsEOF()) {
            return;
        }

        if ($tv->consumeMultipleCharactersIf('[/')) {
            $tv->switchState(self::WordPressShortCodeClosingCloseTagNameStateHandler);
            return;
        }

        if ($tv->charIsLeftSquareBracket()) {
            self::$nestedTagName = '';
            $tv->switchState(self::WordPressShortCodeCanTagBeNestedStateHandler);
            return;
        }

        $tv->appendCharToCurrentTokenTagContent($tv->getChar());
    }

    /**
     * We are at the end of the tag, on a good day we shouldn't care about the closing tag name,
     * but since some tags support no-closing name e.g [toc], we need to differentiate it
     * from tag that supports closing name e.g [caption]...[/caption], this way we can
     * specify if we should set its closeState to true or not
     * @param TonicsView $tv
     */
    public static function WordPressShortCodeClosingCloseTagNameStateHandler(TonicsView $tv)
    {
        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscoreOrDash()) {
            self::$tagNameInCloseTagState .= $tv->getChar();
            return;
        }

        if ($tv->charIsRightSquareBracket()) {
            $tv->reconsumeIn(self::WordPressShortCodeClosingCloseTagStateHandler);
        }
    }

    public static function WordPressShortCodeClosingCloseTagStateHandler(TonicsView $tv)
    {
        if ($tv->charIsRightSquareBracket()) {
            if (self::$tagNameInCloseTagState === self::$tagNameInOpenTagState){
                self::closeLastCurrentTokenTagState($tv);
            }
            self::$tagNameInOpenTagState = '';
            self::$tagNameInCloseTagState = '';
            $tv->clearCharacterTokenData();
            $tv->decrementSigilCounter();
            self::handleEmission(Token::Tag, $tv);
            $tv->switchState(self::InitialStateHandler);
        }
    }

    private static function appendCurrentTokenTagArgKey(string $character): void
    {
        self::$currentArgKey .= $character;
    }

    private static function appendCurrentCharToRawTagName(string $character): void
    {
        self::$rawTagName .= $character;
    }

    private static function appendNestedTagName(string $character): void
    {
        self::$nestedTagName .= $character;
    }

    private static function appendInvalidTagName(string $character): void
    {
        self::$invalidTagName .= $character;
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
                if ($tag->isOpenState() && !empty($closed)) {
                    $tag->appendChildren($closed);
                    $tv->addTagInStackOfOpenElKey($key, $tag);
                    /**@var Tag $t */
                    // useless as array is always empty
                    foreach ($closed as $t) {
                        $t->setParentNode($tag);
                    }
                    break;
                }
            }
        }
    }

    public static function finalEOFStackSort(TonicsView $tv)
    {
        self::handleEmission(Token::Character, $tv);
        foreach ($tv->getStackOfOpenTagEl() as $key => $tag) {
            /**@var Tag $tag */
            if ($tag->hasNoChildren()) {
                continue;
            }

            if ($tag->isCloseState() === false && $tag->hasChildren()) {
                $children = $tag->childNodes();
                if (!empty($children)) {
                    $children[0]->setParentNode(null);
                }
                $tag->clearNodes();
                $tv->addElementInStackOfOpenPosition($children, $key + 1);
            }
        }
    }

    private static function closeLastCurrentTokenTagState(TonicsView $tv): void
    {
        $tv->getLastOpenTag()->setCloseState(true);
    }

    public static function TonicsTagLeftSquareBracketStateHandler(TonicsView $tonicsView): void
    {
    }

    public static function TonicsTagOpenStateHandler(TonicsView $view): void
    {
    }

    public static function TonicsTagNameStateHandler(TonicsView $tonicsView): void
    {
    }

    public static function TonicsTagOpenArgValueSingleQuotedStateHandler(TonicsView $tonicsView): void
    {
    }

    public static function TonicsTagOpenArgValueDoubleQuotedStateHandler(TonicsView $tonicsView): void
    {
    }
}