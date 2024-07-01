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

namespace App\Modules\Core\Library\View\CustomTokenizerState\SimpleShortCode;

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
class TonicsSimpleShortCodeTokenizerState extends TonicsTemplateTokenizerStateAbstract
{

    const TonicsSimpleShortCodeOpenTagStateHandler                                = 'TonicsSimpleShortCodeOpenTagStateHandler';
    const TonicsSimpleShortCodeRawStateHandler                                    = 'TonicsSimpleShortCodeRawStateHandler';
    const TonicsSimpleShortCodeTagNameStateHandler                                = 'TonicsSimpleShortCodeTagNameStateHandler';
    const TonicsSimpleShortCodeClosingOpenTagStateHandler                         = 'TonicsSimpleShortCodeClosingOpenTagStateHandler';
    const TonicsSimpleShortCodeSelfCloseTagStateHandler                           = 'TonicsSimpleShortCodeSelfCloseTagStateHandler';
    const TonicsSimpleShortCodeOpenAttributeStateHandler                          = 'TonicsSimpleShortCodeOpenAttributeStateHandler';
    const TonicsSimpleShortCodeOpenAttributeValueStateHandler                     = 'TonicsSimpleShortCodeOpenAttributeValueStateHandler';
    const TonicsSimpleShortCodeSingleQuotedValueStateHandler                      = 'TonicsSimpleShortCodeSingleQuotedValueStateHandler';
    const TonicsSimpleShortCodeDoubleQuotedValueStateHandler                      = 'TonicsSimpleShortCodeDoubleQuotedValueStateHandler';
    const TonicsSimpleShortCodeArgValueStateHandler                               = 'TonicsSimpleShortCodeArgValueStateHandler';
    const TonicsSimpleShortCodeArgKeyStateHandler                                 = 'TonicsSimpleShortCodeArgKeyStateHandler';
    const TonicsSimpleShortCodeAfterArgValueStateHandler                          = 'TonicsSimpleShortCodeAfterArgValueStateHandler';
    const TonicsSimpleShortCodeClosingCloseTagStateHandler                        = 'TonicsSimpleShortCodeClosingCloseTagStateHandler';
    const TonicsSimpleShortCodeClosingCloseTagNameStateHandler                    = 'TonicsSimpleShortCodeClosingCloseTagNameStateHandler';
    const TonicsSimpleShortCodeNoQuoteValueStateHandler                           = 'TonicsSimpleShortCodeNoQuoteValueStateHandler';
    const TonicsSimpleShortCodeCanTagBeNestedStateHandler                         = 'TonicsSimpleShortCodeCanTagBeNestedStateHandler';
    const TonicsSimpleShortCodeRawContentsStateHandler                            = 'TonicsSimpleShortCodeRawContentsStateHandler';
    const TonicsSimpleShortCodeRawEncounterRightSquareBracketStateHandler         = 'TonicsSimpleShortCodeRawEncounterRightSquareBracketStateHandler';
    const TonicsSimpleShortCodeRawAfterEncounteringRightSquareBracketStateHandler = 'TonicsSimpleShortCodeRawAfterEncounteringRightSquareBracketStateHandler';
    private static string $currentArgKey          = '';
    private static string $rawTagName             = '';
    private static string $nestedTagName          = '';
    private static string $invalidTagName         = '';
    private static string $tagNameInOpenTagState  = '';
    private static string $tagNameInCloseTagState = '';

    public static function InitialStateHandler (TonicsView $tv): void
    {
        if ($tv->charIsEOF()) {
            return;
        }

        if ($tv->charIsLeftSquareBracket()) {
            # E.G. [b
            if ($nextChar = $tv->nextCharHypothetical()) {
                if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscore($nextChar)) {
                    self::handleEmission(Token::Character, $tv);
                    $tv->switchState(self::TonicsSimpleShortCodeOpenTagStateHandler);
                    return;
                }
                # E.G. [/
                if ($tv->charIsForwardSlash($nextChar)) {
                    $tv->switchState(self::TonicsSimpleShortCodeClosingCloseTagNameStateHandler);
                    return;
                }
                # E.G. [[
                if ($tv->charIsLeftSquareBracket($nextChar)) {
                    self::handleEmission(Token::Character, $tv);
                    $tv->switchState(self::TonicsSimpleShortCodeRawStateHandler);
                    return;
                }
            }
        }

        $tv->appendToCharacterToken($tv->getChar());
    }

    public static function TonicsTagLeftSquareBracketStateHandler (TonicsView $tonicsView): void {}

    public static function TonicsTagOpenStateHandler (TonicsView $view): void {}

    public static function TonicsTagNameStateHandler (TonicsView $tonicsView): void {}

    public static function TonicsTagOpenArgValueSingleQuotedStateHandler (TonicsView $tonicsView): void {}

    public static function TonicsTagOpenArgValueDoubleQuotedStateHandler (TonicsView $tonicsView): void {}

    public static function TonicsSimpleShortCodeOpenTagStateHandler (TonicsView $tv)
    {
        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscoreOrDash()) {
            self::$tagNameInOpenTagState = '';
            $tv->reconsumeIn(self::TonicsSimpleShortCodeTagNameStateHandler);
        }
    }

    public static function TonicsSimpleShortCodeRawStateHandler (TonicsView $tv)
    {
        if ($tv->charIsEOF()) {
            return;
        }

        if (key_exists(strtolower(self::$rawTagName), $tv->getModeHandler())) {
            self::$rawTagName = '';
            $tv->appendToCharacterToken(self::$rawTagName);
            $tv->reconsumeIn(self::TonicsSimpleShortCodeRawContentsStateHandler);
            return;
        }

        if ($tv->charIsRightSquareBracket()) {
            $tv->appendToCharacterToken($tv->getChar());
            $tv->switchState(self::TonicsSimpleShortCodeRawEncounterRightSquareBracketStateHandler);
            return;
        }

        # We do not and should not return here, we let it get appended to characterTokenData too
        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscoreOrDash()) {
            self::appendCurrentCharToRawTagName($tv->getChar());
        }

        $tv->appendToCharacterToken($tv->getChar());
    }

    public static function TonicsSimpleShortCodeRawEncounterRightSquareBracketStateHandler (TonicsView $tv)
    {
        if ($tv->charIsRightSquareBracket()) {
            $tv->prependAndAppendToCharacterToken('[', ']');
            $tv->switchState(self::TonicsSimpleShortCodeRawAfterEncounteringRightSquareBracketStateHandler);
            return;
        }

        $tv->appendToCharacterToken($tv->getChar());
    }

    public static function TonicsSimpleShortCodeRawAfterEncounteringRightSquareBracketStateHandler (TonicsView $tv)
    {
        if ($tv->charIsLeftSquareBracket()) {
            if ($next = $tv->nextCharHypothetical()) {
                if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscore($next)) {
                    $tv->reconsumeIn(self::InitialStateHandler);
                }
            }
        }

        $tv->appendToCharacterToken($tv->getChar());
    }

    public static function TonicsSimpleShortCodeRawContentsStateHandler (TonicsView $tv)
    {
        if ($tv->consumeMultipleCharactersIf(']]')) {
            $tv->appendToCharacterToken($tv->getChar());
            $tv->switchState(self::InitialStateHandler);
            return;
        }
        $tv->appendToCharacterToken($tv->getChar());
    }

    public static function TonicsSimpleShortCodeTagNameStateHandler (TonicsView $tv)
    {
        if ($tv->charIsEOF()) {
            return;
        }

        $char = $tv->getChar();
        self::$tagNameInOpenTagState .= $char;
        if (key_exists(strtolower(self::$tagNameInOpenTagState), $tv->getModeHandler())) {
            $tv->createNewTagInOpenStackTag(self::$tagNameInOpenTagState)
                ->switchState(self::TonicsSimpleShortCodeOpenAttributeStateHandler);
            return;
        }

        if ($tv->charIsLeftSquareBracket()) {
            if ($char = $tv->nextCharHypothetical()) {
                if (!$tv->charIsAsciiAlphaOrAsciiDigitOrUnderscoreOrDash($char)) {
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
            $tv->reconsumeIn(self::TonicsSimpleShortCodeCanTagBeNestedStateHandler);
            return;
        }

        if ($tv->charIsRightSquareBracket()) {
            if (empty($tv->getStackOfOpenTagEl())) {
                $tv->appendToCharacterToken('[' . self::$tagNameInOpenTagState);
            } else {
                $tv->getLastCreateTag()->appendCharacterToContent('[' . self::$tagNameInOpenTagState);
            }
            $tv->switchState(self::InitialStateHandler);
            return;
        }

        if ($tv->charIsForwardSlash()) {
            if ($char = $tv->nextCharHypothetical()) {
                if ($tv->charIsRightSquareBracket($char)) {
                    $tv->switchState(self::TonicsSimpleShortCodeSelfCloseTagStateHandler);
                }
            }
        }
    }

    public static function TonicsSimpleShortCodeCanTagBeNestedStateHandler (TonicsView $tv)
    {
        if ($tv->charIsEOF()) {
            $tv->getLastCreateTag()->appendCharacterToContent('[' . self::$nestedTagName);
            return;
        }

        $char = $tv->getChar();
        self::appendNestedTagName($char);

        if (key_exists(strtolower(self::$nestedTagName), $tv->getModeHandler())) {
            $tv->createNewTagInOpenStackTag(self::$nestedTagName)
                ->switchState(self::TonicsSimpleShortCodeOpenAttributeStateHandler);
            self::$nestedTagName = '';
            return;
        }

        if ($tv->charIsRightSquareBracket()) {
            $tv->appendCharToCurrentTokenTagContent('[' . self::$nestedTagName);
            $tv->switchState(self::TonicsSimpleShortCodeClosingOpenTagStateHandler);
        }

    }

    public static function TonicsSimpleShortCodeOpenAttributeStateHandler (TonicsView $tv)
    {
        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscoreOrDash()) {
            $tv->reconsumeIn(self::TonicsSimpleShortCodeArgKeyStateHandler);
            return;
        }

        if ($tv->charIsTabOrLFOrFFOrSpace()) {
            return;
        }

        if ($tv->charIsRightSquareBracket()) {
            $tv->appendCharToArgValue(self::$currentArgKey);
            self::$currentArgKey = '';
            $tv->incrementSigilCounter();
            $tv->switchState(self::TonicsSimpleShortCodeClosingOpenTagStateHandler);
        }
    }

    public static function TonicsSimpleShortCodeArgKeyStateHandler (TonicsView $tv)
    {
        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscoreOrDash()) {
            self::appendCurrentTokenTagArgKey($tv->getChar());
            return;
        }

        if ($tv->charIsRightSquareBracket()) {
            $tv->startNewArgsInCurrentTagToken(self::$currentArgKey);
            self::$currentArgKey = '';
            $tv->incrementSigilCounter();
            $tv->switchState(self::TonicsSimpleShortCodeClosingOpenTagStateHandler);
            return;
        }

        if ($tv->charIsEqual()) {
            $tv->switchState(self::TonicsSimpleShortCodeOpenAttributeValueStateHandler);
        }

        if ($tv->charIsTabOrLFOrFFOrSpace()) {
            $tv->switchState(self::TonicsSimpleShortCodeArgValueStateHandler);
        }
    }

    public static function TonicsSimpleShortCodeArgValueStateHandler (TonicsView $tv)
    {
        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscoreOrDash()) {
            $tv->startNewArgsInCurrentTagToken(self::$currentArgKey);
            self::$currentArgKey = '';
            $tv->reconsumeIn(self::TonicsSimpleShortCodeOpenAttributeStateHandler);
            return;
        }
    }

    public static function TonicsSimpleShortCodeOpenAttributeValueStateHandler (TonicsView $tv)
    {
        if ($tv->charIsTabOrLFOrFFOrSpace()) {
            return;
        }

        if ($tv->charIsApostrophe()) {
            $tv->startNewArgsInCurrentTagToken();
            $tv->switchState(self::TonicsSimpleShortCodeSingleQuotedValueStateHandler);
            return;
        }

        if ($tv->charIsQuotationMark()) {
            $tv->startNewArgsInCurrentTagToken();
            $tv->switchState(self::TonicsSimpleShortCodeDoubleQuotedValueStateHandler);
            return;
        }

        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscoreOrDash()) {
            $tv->startNewArgsInCurrentTagToken();
            $tv->reconsumeIn(self::TonicsSimpleShortCodeNoQuoteValueStateHandler);
        }
    }

    public static function TonicsSimpleShortCodeNoQuoteValueStateHandler (TonicsView $tv)
    {
        if ($tv->charIsRightSquareBracket()) {
            $tv->replaceCurrentTokenTagArgKey(self::$currentArgKey);
            self::$currentArgKey = '';
            $tv->reconsumeIn(self::TonicsSimpleShortCodeAfterArgValueStateHandler);
            return;
        }

        if ($tv->charIsTabOrLFOrFFOrSpace()) {
            $tv->replaceCurrentTokenTagArgKey(self::$currentArgKey);
            self::$currentArgKey = '';
            $tv->switchState(self::TonicsSimpleShortCodeOpenAttributeStateHandler);
            return;
        }

        $tv->appendCharToArgValue($tv->getChar());
    }

    public static function TonicsSimpleShortCodeSingleQuotedValueStateHandler (TonicsView $tv)
    {
        if ($tv->charIsApostrophe()) {
            $tv->replaceCurrentTokenTagArgKey(self::$currentArgKey);
            self::$currentArgKey = '';
            $tv->switchState(self::TonicsSimpleShortCodeAfterArgValueStateHandler);
            return;
        }

        $tv->appendCharToArgValue($tv->getChar());
    }

    public static function TonicsSimpleShortCodeDoubleQuotedValueStateHandler (TonicsView $tv)
    {
        if ($tv->charIsQuotationMark()) {
            $tv->replaceCurrentTokenTagArgKey(self::$currentArgKey);
            self::$currentArgKey = '';
            $tv->switchState(self::TonicsSimpleShortCodeAfterArgValueStateHandler);
            return;
        }

        $tv->appendCharToArgValue($tv->getChar());
    }

    public static function TonicsSimpleShortCodeAfterArgValueStateHandler (TonicsView $tv)
    {
        if ($tv->charIsTabOrLFOrFFOrSpace()) {
            $tv->switchState(self::TonicsSimpleShortCodeOpenAttributeStateHandler);
            return;
        }

        if ($tv->charIsForwardSlash()) {
            $tv->switchState(self::TonicsSimpleShortCodeSelfCloseTagStateHandler);
            return;
        }

        if ($tv->charIsRightSquareBracket()) {
            $tv->incrementSigilCounter();
            $tv->switchState(self::TonicsSimpleShortCodeClosingOpenTagStateHandler);
        }
    }

    public static function TonicsSimpleShortCodeSelfCloseTagStateHandler (TonicsView $tv)
    {
        if ($tv->charIsRightSquareBracket()) {
            $tv->decrementSigilCounter();
            $tv->switchState(self::InitialStateHandler);
            self::closeLastCurrentTokenTagState($tv);
            self::handleEmission(Token::Tag, $tv);
        }
    }

    public static function TonicsSimpleShortCodeClosingOpenTagStateHandler (TonicsView $tv)
    {
        if ($tv->charIsEOF()) {
            return;
        }

        if ($tv->consumeMultipleCharactersIf('[/')) {
            $tv->switchState(self::TonicsSimpleShortCodeClosingCloseTagNameStateHandler);
            return;
        }

        if ($tv->charIsLeftSquareBracket()) {
            self::$nestedTagName = '';
            $tv->switchState(self::TonicsSimpleShortCodeCanTagBeNestedStateHandler);
            return;
        }

        $tv->appendCharToCurrentTokenTagContent($tv->getChar());
    }

    /**
     * We are at the end of the tag, on a good day we shouldn't care about the closing tag name,
     * but since some tags support no-closing name e.g [toc], we need to differentiate it
     * from tag that supports closing name e.g [caption]...[/caption], this way we can
     * specify if we should set its closeState to true or not
     *
     * @param TonicsView $tv
     */
    public static function TonicsSimpleShortCodeClosingCloseTagNameStateHandler (TonicsView $tv)
    {
        if ($tv->charIsAsciiAlphaOrAsciiDigitOrUnderscoreOrDash()) {
            self::$tagNameInCloseTagState .= $tv->getChar();
            return;
        }

        if ($tv->charIsRightSquareBracket()) {
            $tv->reconsumeIn(self::TonicsSimpleShortCodeClosingCloseTagStateHandler);
        }
    }

    public static function TonicsSimpleShortCodeClosingCloseTagStateHandler (TonicsView $tv)
    {
        if ($tv->charIsRightSquareBracket()) {
            if (self::$tagNameInCloseTagState === self::$tagNameInOpenTagState) {
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

    private static function appendCurrentTokenTagArgKey (string $character): void
    {
        self::$currentArgKey .= $character;
    }

    private static function appendCurrentCharToRawTagName (string $character): void
    {
        self::$rawTagName .= $character;
    }

    private static function appendNestedTagName (string $character): void
    {
        self::$nestedTagName .= $character;
    }

    private static function appendInvalidTagName (string $character): void
    {
        self::$invalidTagName .= $character;
    }

    private static function handleEmission (string $toEmit, TonicsView $tv): void
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

    public static function sortStackOfOpenTagEl (TonicsView $tv)
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

    public static function finalEOFStackSort (TonicsView $tv)
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

    private static function closeLastCurrentTokenTagState (TonicsView $tv): void
    {
        $tv->getLastOpenTag()->setCloseState(true);
    }
}