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

describe("TonicsView", function () {

    beforeEach(function () {
        $this->view->reset();
        $this->view->setVariableData([
            'title'  => 'Fancy Value 55344343',
            'title2' => 'Fancy Value 2',
            'title3' => 'Fancy Value 3',
            'new'    => 'This is the new',
            'vary'   => [
                'in' => [
                    'in' => 'this is a nested data',
                ],
            ],
        ]);
    });

    describe("->mb_str_split()", function () {
        context("When Split", function () {
            it("should match native mb_str_split", function () {
                $string = '浮生若梦حزينة حقاً. ... إنَّها تَبدو😒👌🤞😜🐱‍👓🛵🚜🦼🚟🚥🌓👛🥻👡 اليَومَ حَزينَةً حَقَّاً';
                expect($this->view->mb_str_split($string))->toBe(mb_str_split($string));
            });
        });
    });

    describe("->charIsTabOrLFOrFFOrSpace()", function () {

        context('When Char is \n, \f, \t, or space', function () {
            it("should return true", function () {
                expect($this->view->charIsTabOrLFOrFFOrSpace("\n"))->toBeTruthy();
                expect($this->view->charIsTabOrLFOrFFOrSpace("\f"))->toBeTruthy();
                expect($this->view->charIsTabOrLFOrFFOrSpace("\t"))->toBeTruthy();
                expect($this->view->charIsTabOrLFOrFFOrSpace(" "))->toBeTruthy();
            });
        });
        context('When Char is not \n, \f, \t, or space', function () {
            it("should return false", function () {
                expect($this->view->charIsTabOrLFOrFFOrSpace('ggxgfxg'))->toBeFalsy();
            });
        });

    });

    describe("->charIsAsciiAlphaOrAsciiDigitOrUnderscore()", function () {

        context('When Char is Ascii Alpha, Digit or Underscore', function () {
            it("should return true", function () {
                expect($this->view->charIsAsciiAlphaOrAsciiDigitOrUnderscore("A"))->toBeTruthy();
                expect($this->view->charIsAsciiAlphaOrAsciiDigitOrUnderscore("2"))->toBeTruthy();
                expect($this->view->charIsAsciiAlphaOrAsciiDigitOrUnderscore("_"))->toBeTruthy();
            });
        });

        context('When Char is not Ascii Alpha, Digit or Underscore', function () {
            it("should return false", function () {
                expect($this->view->charIsAsciiAlphaOrAsciiDigitOrUnderscore('&'))->toBeFalsy();
                expect($this->view->charIsAsciiAlphaOrAsciiDigitOrUnderscore('$'))->toBeFalsy();
                expect($this->view->charIsAsciiAlphaOrAsciiDigitOrUnderscore('若'))->toBeFalsy();
            });
        });
    });

    describe("->accessArrayWithSeparator()", function () {
        context('When given vary.in.in', function () {
            it("should return value in vary.in.in", function () {
                expect($this->view->accessArrayWithSeparator("vary.in.in"))->toBe('this is a nested data');
            });
        });
    });
});