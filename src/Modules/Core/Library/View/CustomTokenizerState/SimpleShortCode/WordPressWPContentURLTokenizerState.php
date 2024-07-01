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
use Devsrealm\TonicsTemplateSystem\TonicsView;

class WordPressWPContentURLTokenizerState extends TonicsTemplateTokenizerStateAbstract
{
    const WordPressWPContentURLMatchSiteURLStateHandler     = 'WordPressWPContentURLMatchSiteURLStateHandler';
    const WordPressWPContentURLMatchUploadsPathStateHandler = 'WordPressWPContentURLMatchUploadsPathStateHandler';
    const WordPressWPContentURLUploadsPathFileStateHandler  = 'WordPressWPContentURLUploadsPathFileStateHandler';
    private static array  $splitURL             = [];
    private static string $capturedSiteURLChar  = '';
    private static string $capturedFilePathChar = '';
    private static array  $uploadsPath          = [
        'w', 'p', '-', 'c', 'o', 'n', 't', 'e', 'n', 't', '/', 'u', 'p', 'l', 'o', 'a', 'd', 's', '/',
    ];

    public static function InitialStateHandler (TonicsView $tv): void
    {
        $char = $tv->getChar();
        if ($tv->charIsEOF()) {
            return;
        }

        $url = $tv->getModeStorage('url')['siteURL'];
        if (!empty($url) && empty(self::$splitURL)) {
            self::$splitURL = mb_str_split($url);
        }

        if (key_exists(0, self::$splitURL)) {
            if ($char === self::$splitURL[0]) {
                $tv->reconsumeIn(self::WordPressWPContentURLMatchSiteURLStateHandler);
                return;
            }
        }

        if ($char === '<') {
            ## If img tag can be matched, add lazy loading attributes, it doesn't matter if it's a duplicate
            ## browser would handle it just fine...
            if ($tv->consumeMultipleCharactersIf('<img ')) {
                $tv->appendToCharacterToken("<img loading='lazy' decoding='async' ");
                return;
            }
        }

        $tv->appendToCharacterToken($char);
    }

    public static function TonicsTagLeftSquareBracketStateHandler (TonicsView $tonicsView): void
    {
        // TODO: Implement TonicsTagLeftSquareBracketStateHandler() method.
    }

    public static function TonicsTagOpenStateHandler (TonicsView $view): void
    {
        // TODO: Implement TonicsTagOpenStateHandler() method.
    }

    public static function TonicsTagNameStateHandler (TonicsView $tonicsView): void
    {
        // TODO: Implement TonicsTagNameStateHandler() method.
    }

    public static function TonicsTagOpenArgValueSingleQuotedStateHandler (TonicsView $tonicsView): void
    {
        // TODO: Implement TonicsTagOpenArgValueSingleQuotedStateHandler() method.
    }

    public static function TonicsTagOpenArgValueDoubleQuotedStateHandler (TonicsView $tonicsView): void
    {
        // TODO: Implement TonicsTagOpenArgValueDoubleQuotedStateHandler() method.
    }

    public static function WordPressWPContentURLMatchSiteURLStateHandler (TonicsView $tv): void
    {
        $char = $tv->getChar();
        foreach (self::$splitURL as $charURL) {
            if ($char === $charURL) {
                self::$capturedSiteURLChar .= $char;
                if ($tv->isDebug()) {
                    $tv->appendToDebugChars($char);
                }
                $tv->nextCharacterKey();
                $tv->setChar($tv->getCharacters()[$tv->getCurrentCharKey()]);
                $char = $tv->getChar();
            } else {
                $tv->appendToCharacterToken(self::$capturedSiteURLChar);
                self::$capturedSiteURLChar = '';
                self::$capturedFilePathChar = '';
                $tv->reconsumeIn(self::InitialStateHandler);
                return;
            }
        }

        $tv->setDontWriteDebugChar(true);
        $tv->reconsumeIn(self::WordPressWPContentURLMatchUploadsPathStateHandler);
    }

    public static function WordPressWPContentURLMatchUploadsPathStateHandler (TonicsView $tv)
    {
        $char = $tv->getChar();
        foreach (self::$uploadsPath as $charURL) {
            if ($char === $charURL) {
                self::$capturedFilePathChar .= $char;
                if ($tv->isDebug()) {
                    $tv->appendToDebugChars($char);
                }
                $tv->nextCharacterKey();
                $tv->setChar($tv->getCharacters()[$tv->getCurrentCharKey()]);
                $char = $tv->getChar();
            } else {
                $tv->appendToCharacterToken(self::$capturedSiteURLChar . self::$capturedFilePathChar);
                self::$capturedSiteURLChar = '';
                self::$capturedFilePathChar = '';
                $tv->reconsumeIn(self::InitialStateHandler);
                return;
            }
        }

        if ($tv->charIsTabOrLFOrFFOrSpace()) {
            $tv->reconsumeIn(self::InitialStateHandler);
            return;
        }

        $tv->reconsumeIn(self::WordPressWPContentURLUploadsPathFileStateHandler);
    }

    public static function WordPressWPContentURLUploadsPathFileStateHandler (TonicsView $tv): void
    {
        if ($tv->charIsTabOrLFOrFFOrSpace() || $tv->charIsQuotationMark() || $tv->charIsApostrophe()) {
            $tv->createNewTagInOpenStackTag('char');
            $tv->appendCharToCurrentTokenTagContent($tv->getCharacterToken()['data']);
            $tv->clearCharacterTokenData();

            $tv->createNewTagInOpenStackTag('url');
            $tv->startNewArgsInCurrentTagToken(self::$capturedSiteURLChar);
            $tv->replaceCurrentTokenTagArgKey('url');

            $tv->startNewArgsInCurrentTagToken(self::$capturedFilePathChar);
            $tv->replaceCurrentTokenTagArgKey('path');
            self::$capturedFilePathChar = '';
            self::$capturedSiteURLChar = '';
            $tv->reconsumeIn(self::InitialStateHandler);
            return;
        }

        self::$capturedFilePathChar .= $tv->getChar();
    }

    public static function finalEOFStackSort (TonicsView $tv)
    {
        $tv->createNewTagInOpenStackTag('char');
        $tv->appendCharToCurrentTokenTagContent($tv->getCharacterToken()['data'] ?? '');
        $tv->appendCharToCurrentTokenTagContent(self::$capturedSiteURLChar);
        $tv->appendCharToCurrentTokenTagContent(self::$capturedFilePathChar);
        $tv->clearCharacterTokenData();
    }
}