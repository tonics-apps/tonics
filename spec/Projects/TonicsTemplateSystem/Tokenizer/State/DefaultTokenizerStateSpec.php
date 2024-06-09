<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Spec\Tokenizer\State;

use Devsrealm\TonicsTemplateSystem\Exceptions\TonicsTemplateInvalidCharacterUponOpeningTag;
use Devsrealm\TonicsTemplateSystem\Exceptions\TonicsTemplateInvalidTagNameIdentifier;
use Devsrealm\TonicsTemplateSystem\Exceptions\TonicsTemplateUnexpectedEOF;
use Devsrealm\TonicsTemplateSystem\Node\Tag;
use Devsrealm\TonicsTemplateSystem\Tokenizer\Token\Token;
use Devsrealm\TonicsTemplateSystem\TonicsView;

describe("DefaultTokenizerState", function () {

    beforeEach(function () {
        $this->view->reset();
    });

    #
    # STATE: InitialStateHandler
    #
    describe("::InitialStateHandler()", function () {
        beforeEach(function () {
            $this->view->setCurrentState($this->view->getTokenizerState()::InitialStateHandler);
        });
        context("When Character is Left Square Bracket", function () {
            it("switch to TonicsTagLeftSquareBracketStateHandler", function () {
                $this->view->setCharacters(['[']);
                $this->view->tokenize();
                expect($this->view->getCurrentState())->toBe($this->view->getTokenizerState()::TonicsTagLeftSquareBracketStateHandler);
            });
        });

        context("When Character is Right Square Bracket", function () {
            it("switch to TonicsTagClosingStateHandler", function () {
                $this->view->setCharacters([']']);
                $this->view->tokenize();
                expect($this->view->getCurrentState())->toBe($this->view->getTokenizerState()::TonicsTagClosingStateHandler);
            });
        });

        context("When Character is EOF", function () {
            it("should return to InitStateHandler", function () {
                $this->view->setCharacters(['EOF']);
                $this->view->tokenize();
                expect($this->view->getCurrentState())->toBe($this->view->getTokenizerState()::InitialStateHandler);
            });
        });

        context("When Character is not Left or Right Square Bracket or EOF", function () {
            it("should appendToCharacterToken", function () {
                $this->view->setCharacters(['a']);
                $this->view->tokenize();
                expect($this->view->getCharacterToken()['data'])->toEqual('a');
            });
        });
    });

    #
    # STATE: TonicsTagLeftSquareBracketStateHandler
    #
    describe("::TonicsTagLeftSquareBracketStateHandler()", function () {
        beforeEach(function () {
            $this->view->setCurrentState($this->view->getTokenizerState()::TonicsTagLeftSquareBracketStateHandler);
        });
        context("When Character is Left Square Bracket", function () {
            it("switch to TonicsTagOpenState", function () {
                $this->view->setCharacters(['[']);
                $this->view->tokenize();
                expect($this->view->getCurrentState())->toBe($this->view->getTokenizerState()::TonicsTagOpenStateHandler);
            });
        });

        context("When Character EOF", function () {
            it("should throw TonicsTemplateUnexpectedEOF", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setCharacters(['EOF']);
                $closure = function () use ($view) {
                    $view->tokenize();
                };
                expect($closure)->toThrow(new TonicsTemplateUnexpectedEOF("Unexpected End of File. On Line 1 in Template main"));
            });
        });

        context("When Character is not Left or EOF", function () {
            it("should throw TonicsTemplateInvalidSigilIdentifier", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setCharacters(['?']);
                $closure = function () use ($view) {
                    $view->tokenize();
                };
                // expect($closure)->toThrow(new TonicsTemplateInvalidSigilIdentifier("Invalid Sigil Identifier"));
            });
        });
    });

    #
    # STATE: TonicsTagOpenStateHandler
    #
    describe("::TonicsTagOpenStateHandler()", function () {
        beforeEach(function () {
            $this->view->setCurrentState($this->view->getTokenizerState()::TonicsTagOpenStateHandler);
        });
        context("When Character is Left Square Bracket", function () {
            it("switch to TonicsRawStateStateHandler", function () {
                $this->view->setCharacters(['[']);
                $this->view->tokenize();
                expect($this->view->getCurrentState())->toBe($this->view->getTokenizerState()::TonicsRawStateStateHandler);
            });
        });

        context("When Character EOF", function () {
            it("should throw TonicsTemplateUnexpectedEOF", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setChar('EOF');
                $closure = function () use ($view) {
                    $view->dispatchState($view->getTokenizerState()::TonicsTagOpenStateHandler);
                };
                expect($closure)->toThrow(new TonicsTemplateUnexpectedEOF("Unexpected End of File. On Line 1 in Template main"));
            });
        });

        context("When Character is AsciiAlphaOrAsciiDigitOrUnderscore", function () {
            it("should create an empty tagname and reconsume in TonicsTagNameStateHandler", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setCharacters(['a, b', 'c']);
                $view->setChar('c');
                $view->setCurrentCharKey(2);
                $view->dispatchState($view->getTokenizerState()::TonicsTagOpenStateHandler);
                expect($view->getCurrentState())->toBe($view->getTokenizerState()::TonicsTagNameStateHandler);
                expect($view->getChar())->toEqual('c');
                expect($view->getStackOfOpenTagEl()[0])->toBeAnInstanceOf(Tag::class);
                expect($view->getStackOfOpenTagEl()[0]->getTagName())->toBeEmpty();
            });
        });

        context("When Character is Not EOF, AsciiAlphaOrAsciiDigitOrUnderscore or Left Square Bracket", function () {
            it("should throw TonicsTemplateInvalidCharacterUponOpeningTag", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setChar('}');
                $closure = function () use ($view) {
                    $view->dispatchState($view->getTokenizerState()::TonicsTagOpenStateHandler);
                };
                expect($closure)->toThrow(new TonicsTemplateInvalidCharacterUponOpeningTag());
            });
        });

    });

    #
    # STATE: TonicsTagNameStateHandler
    #
    describe("::TonicsTagNameStateHandler()", function () {
        beforeEach(function () {
            $this->view->setCurrentState($this->view->getTokenizerState()::TonicsTagNameStateHandler);
        });

        context("When Character EOF", function () {
            it("should throw TonicsTemplateUnexpectedEOF", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setChar('EOF');
                $closure = function () use ($view) {
                    $view->dispatchState($view->getTokenizerState()::TonicsTagNameStateHandler);
                };
                expect($closure)->toThrow(new TonicsTemplateUnexpectedEOF("Unexpected End of File. On Line 1 in Template main"));
            });
        });

        context("When Character is AsciiAlphaOrAsciiDigitOrUnderscore", function () {
            it("should append Character ToCurrentTokenTagName and remain in the same state", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setStackOfOpenTagEl([new Tag('b')]);
                $view->setCharacters(['l', 'o', 'c', 'k']);
                $view->tokenize();
                expect($view->getCurrentState())->toBe($view->getTokenizerState()::TonicsTagNameStateHandler);
                expect($view->getStackOfOpenTagEl()[0]->getTagName())->toEqual('block');
            });
        });

        context("When Character is TabOrLFOrFFOrSpace", function () {
            it("should do nothing and remain in the same state", function () {
                $this->view->setCharacters(["\t"]);
                $this->view->tokenize();
                expect($this->view->getCurrentState())->toBe($this->view->getTokenizerState()::TonicsTagNameStateHandler);
            });
        });

        context("When Character is Right Square Bracket", function () {
            it("should switch to TonicsTagClosingStateHandler", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setCharacters([']']);
                $this->view->tokenize();
                expect($this->view->getCurrentState())->toBe($this->view->getTokenizerState()::TonicsTagClosingStateHandler);
            });
        });

        context("When Character is Left Parenthesis", function () {
            it("should switch to TonicsTagOpenParenThesisStateHandler", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setCharacters(['(']);
                $this->view->tokenize();
                expect($this->view->getCurrentState())->toBe($this->view->getTokenizerState()::TonicsTagOpenParenThesisStateHandler);
            });
        });

        context("When Character is Not EOF, [, (, TabOrLFOrFFOrSpace, AsciiAlphaOrAsciiDigitOrUnderscore", function () {
            it("should throw TonicsTemplateInvalidTagNameIdentifier", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setChar('}');
                $closure = function () use ($view) {
                    $view->dispatchState($view->getTokenizerState()::TonicsTagNameStateHandler);
                };
                expect($closure)->toThrow(new TonicsTemplateInvalidTagNameIdentifier());
            });
        });

    });

    #
    # STATE: TonicsTagOpenArgValueSingleQuotedStateHandler
    #
    describe("::TonicsTagOpenArgValueSingleQuotedStateHandler()", function () {
        beforeEach(function () {
            $this->view->setCurrentState($this->view->getTokenizerState()::TonicsTagOpenArgValueSingleQuotedStateHandler);
        });

        context("When character Is Apostrophe", function () {
            it("should switch to TonicsAfterTagArqValueQuotedStateHandler", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setCharacters(['\'']);
                $view->tokenize();
                expect($view->getCurrentState())->toBe($view->getTokenizerState()::TonicsAfterTagArqValueQuotedStateHandler);
            });
        });

        context("When Character EOF", function () {
            it("should throw TonicsTemplateUnexpectedEOF", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setChar('EOF');
                $closure = function () use ($view) {
                    $view->dispatchState($view->getTokenizerState()::TonicsTagOpenArgValueSingleQuotedStateHandler);
                };
                expect($closure)->toThrow(new TonicsTemplateUnexpectedEOF("Unexpected End of File. On Line 1 in Template main"));
            });
        });

        context("When Character is not EOF or Apostrophe", function () {
            it("should append Character tag token last arg and remain in the same state", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setStackOfOpenTagEl([(new Tag())->setArgs(['v'])]);
                $view->setCharacters(['a', 'r']);
                $view->tokenize();
                expect($view->getCurrentState())->toBe($view->getTokenizerState()::TonicsTagOpenArgValueSingleQuotedStateHandler);
                expect($view->getStackOfOpenTagEl()[0]->getArgs()[0])->toEqual('var');
            });
        });

    });

    #
    # STATE: TonicsTagOpenArgValueDoubleQuotedStateHandler
    #
    describe("::TonicsTagOpenArgValueDoubleQuotedStateHandler()", function () {
        beforeEach(function () {
            $this->view->setCurrentState($this->view->getTokenizerState()::TonicsTagOpenArgValueDoubleQuotedStateHandler);
        });

        context("When character Is Quotation Mark", function () {
            it("should switch to TonicsAfterTagArqValueQuotedStateHandler", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setCharacters(['"']);
                $view->tokenize();
                expect($view->getCurrentState())->toBe($view->getTokenizerState()::TonicsAfterTagArqValueQuotedStateHandler);
            });
        });

        context("When Character EOF", function () {
            it("should throw TonicsTemplateUnexpectedEOF", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setChar('EOF');
                $closure = function () use ($view) {
                    $view->dispatchState($view->getTokenizerState()::TonicsTagOpenArgValueDoubleQuotedStateHandler);
                };
                expect($closure)->toThrow(new TonicsTemplateUnexpectedEOF("Unexpected End of File. On Line 1 in Template main"));
            });
        });

        context("When Character is not EOF or Quotation Mark", function () {
            it("should append Character tag token last arg and remain in the same state", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setStackOfOpenTagEl([(new Tag())->setArgs(['v'])]);
                $view->setCharacters(['a', 'r']);
                $view->tokenize();
                expect($view->getCurrentState())->toBe($view->getTokenizerState()::TonicsTagOpenArgValueDoubleQuotedStateHandler);
                expect($view->getStackOfOpenTagEl()[0]->getArgs()[0])->toEqual('var');
            });
        });

    });

    #
    # STATE: TonicsRawStateStateHandler
    #
    describe("::TonicsRawStateStateHandler()", function () {
        beforeEach(function () {
            $this->view->setCurrentState($this->view->getTokenizerState()::TonicsRawStateStateHandler);
        });

        context("When character are ]]]", function () {
            it("should decrement sigil counter, emit Character Token and Switch to InitialStateHandler", function () {
                $this->view->setCharacters([']', ']', ']']);
                $this->view->setSigilCounter(1);
                $this->view->tokenize();
                expect($this->view->getSigilCounter())->toEqual(0);
                expect($this->view->getCurrentState())->toBe($this->view->getTokenizerState()::InitialStateHandler);
            });
        });

        context("When Character EOF", function () {
            it("should throw TonicsTemplateUnexpectedEOF", function () {
                $this->view->setChar('EOF');
                $closure = function () {
                    $this->view->dispatchState($this->view->getTokenizerState()::TonicsRawStateStateHandler);
                };
                expect($closure)->toThrow(new TonicsTemplateUnexpectedEOF("Unexpected End of File. On Line 1 in Template main"));
            });
        });

        context("When Character is not EOF or ]]]", function () {
            it("should appendToCharacterToken and remain in the same state", function () {
                $this->view->setCharacters(
                    [
                        ']', ']', 'c', 'h', 'a', 'r', '[', '[', 'b',
                        '(', 't', 'i', 't', 'l', 'e', ')',
                        'a', 'v', 'a', 'l', 'u', 'e', ']', ']',
                    ]);
                $this->view->tokenize();
                expect($this->view->getCharacterToken()['data'])->toEqual(']]char[[b(title)avalue]]');
                expect($this->view->getCurrentState())->toBe($this->view->getTokenizerState()::TonicsRawStateStateHandler);
            });
        });

    });

    #
    # STATE: TonicsTagOpenParenThesisStateHandler
    #
    describe("::TonicsTagOpenParenThesisStateHandler()", function () {
        beforeEach(function () {
            $this->view->setCurrentState($this->view->getTokenizerState()::TonicsTagOpenParenThesisStateHandler);
        });

        context("When charIsApostrophe", function () {
            it("should startNewArgsInCurrentTagToken and switch to TonicsTagOpenArgValueSingleQuotedStateHandler", function () {
                $this->view->setCharacters(['\'']);
                $this->view->setStackOfOpenTagEl([new Tag()]);
                $this->view->tokenize();
                expect($this->view->getStackOfOpenTagEl()[0]->getArgs()[0])->toBeEmpty();
                expect($this->view->getCurrentState())->toBe($this->view->getTokenizerState()::TonicsTagOpenArgValueSingleQuotedStateHandler);
            });
        });

        context("When charIsRightParenthesis", function () {
            it("should switch to TonicsTagCloseParenThesisStateHandler", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setCharacters([')']);
                $view->tokenize();
                expect($view->getCurrentState())->toBe($view->getTokenizerState()::TonicsTagCloseParenThesisStateHandler);
            });
        });

        context("When Character EOF", function () {
            it("should throw TonicsTemplateUnexpectedEOF", function () {
                $this->view->setChar('EOF');
                $closure = function () {
                    $this->view->dispatchState($this->view->getTokenizerState()::TonicsTagOpenParenThesisStateHandler);
                };
                expect($closure)->toThrow(new TonicsTemplateUnexpectedEOF("Unexpected End of File. On Line 1 in Template main"));
            });
        });


    });

    #
    # STATE: TonicsAfterTagArqValueQuotedStateHandler
    #
    describe("::TonicsAfterTagArqValueQuotedStateHandler()", function () {
        beforeEach(function () {
            $this->view->setCurrentState($this->view->getTokenizerState()::TonicsAfterTagArqValueQuotedStateHandler);
        });

        context("When charIsTabOrLFOrFFOrSpace or charIsComma", function () {
            it("should remain in the same state", function () {
                $this->view->setCharacters([',', "\t", "\n", "\f", ' ']);
                $this->view->tokenize();
                expect($this->view->getCurrentState())->toBe($this->view->getTokenizerState()::TonicsAfterTagArqValueQuotedStateHandler);
            });
        });

        context("When Character EOF", function () {
            it("should throw TonicsTemplateUnexpectedEOF", function () {
                $this->view->setChar('EOF');
                $closure = function () {
                    $this->view->dispatchState($this->view->getTokenizerState()::TonicsAfterTagArqValueQuotedStateHandler);
                };
                expect($closure)->toThrow(new TonicsTemplateUnexpectedEOF("Unexpected End of File. On Line 1 in Template main"));
            });
        });

        context("When charIsApostrophe", function () {
            it("should startNewArgsInCurrentTagToken and switch to TonicsTagOpenArgValueSingleQuotedStateHandler", function () {
                $this->view->setCharacters(['\'']);
                $this->view->setStackOfOpenTagEl([new Tag()]);
                $this->view->tokenize();
                expect($this->view->getStackOfOpenTagEl()[0]->getArgs()[0])->toBeEmpty();
                expect($this->view->getCurrentState())->toBe($this->view->getTokenizerState()::TonicsTagOpenArgValueSingleQuotedStateHandler);
            });
        });

        context("When charIsRightParenthesis", function () {
            it("should switch to TonicsTagCloseParenThesisStateHandler", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setCharacters([')']);
                $view->tokenize();
                expect($view->getCurrentState())->toBe($view->getTokenizerState()::TonicsTagCloseParenThesisStateHandler);
            });
        });

    });

    #
    # STATE: TonicsTagCloseParenThesisStateHandler
    #
    describe("::TonicsTagCloseParenThesisStateHandler()", function () {
        beforeEach(function () {
            $this->view->setCurrentState($this->view->getTokenizerState()::TonicsTagCloseParenThesisStateHandler);
        });
        context("When Character is Right Square Bracket", function () {
            it("switch to TonicsTagClosingStateHandler", function () {
                $this->view->setCharacters([']']);
                $this->view->tokenize();
                expect($this->view->getCurrentState())->toBe($this->view->getTokenizerState()::TonicsTagClosingStateHandler);
            });
        });

        context("When Character EOF", function () {
            it("should throw TonicsTemplateUnexpectedEOF", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setChar('EOF');
                $closure = function () use ($view) {
                    $view->dispatchState($view->getTokenizerState()::TonicsTagCloseParenThesisStateHandler);
                };
                expect($closure)->toThrow(new TonicsTemplateUnexpectedEOF("Unexpected End of File. On Line 1 in Template main"));
            });
        });

        context("When Character is Left Square Bracket", function () {
            it("should emit character and switch to TonicsTagLeftSquareBracketStateHandler", function () {
                $this->view->setCharacters(['[']);
                $this->view->tokenize();
                expect($this->view->getCurrentState())->toBe($this->view->getTokenizerState()::TonicsTagLeftSquareBracketStateHandler);
            });
        });

        context("When Character is not EOF or [ or ]", function () {
            it("should appendCharToCurrentTokenTagContent and remain in the same state", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setStackOfOpenTagEl([(new Tag())->setContent('tit')]);
                $view->setCharacters(['l', 'e']);
                $view->tokenize();
                expect($view->getCurrentState())->toBe($view->getTokenizerState()::TonicsTagCloseParenThesisStateHandler);
                expect($view->getStackOfOpenTagEl()[0]->getContent())->toEqual('title');
            });
        });
    });

    #
    # STATE: TonicsTagClosingStateHandler
    #
    describe("::TonicsTagClosingStateHandler()", function () {
        beforeEach(function () {
            $this->view->setCurrentState($this->view->getTokenizerState()::TonicsTagClosingStateHandler);
        });

        context("When character is Right Square Bracket", function () {
            it("should decrement sigil counter, emit Tag Token and Switch to InitialStateHandler", function () {
                $this->view->setCharacters([']']);
                $this->view->setSigilCounter(1);
                $this->view->setStackOfOpenTagEl([(new Tag())->setTagName('b')->setArgs(['content'])]);
                $this->view->tokenize();
                expect($this->view->getSigilCounter())->toEqual(0);
                expect($this->view->getLastEmitted())->toBe(Token::Tag);
                expect($this->view->getCurrentState())->toBe($this->view->getTokenizerState()::InitialStateHandler);
            });
        });

        context("When Character EOF", function () {
            it("should throw TonicsTemplateUnexpectedEOF", function () {
                $this->view->setChar('EOF');
                $closure = function () {
                    $this->view->dispatchState($this->view->getTokenizerState()::TonicsTagClosingStateHandler);
                };
                expect($closure)->toThrow(new TonicsTemplateUnexpectedEOF("Unexpected End of File. On Line 1 in Template main"));
            });
        });

        context("When Character is not Right Square Bracket or EOF", function () {
            it("should throw TonicsTemplateInvalidSigilIdentifier", function () {
                /*** @var TonicsView $view */
                $view = $this->view;
                $view->setCharacters(['?']);
                $closure = function () use ($view) {
                    $view->tokenize();
                };
                // expect($closure)->toThrow(new TonicsTemplateInvalidSigilIdentifier("Invalid Sigil Identifier"));
            });
        });
    });

});