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

namespace Loader;

describe("TonicsTemplateArrayLoader", function () {
    describe("->load()", function () {
        context("When Load", function () {
            it("expect exact string", function () {
                allow($this->arrayLoader)->toReceive('load')
                    ->with('main')
                    ->andReturn($this->arrayTemplates['main']);
                expect($this->arrayLoader->load('main'))->toBe($this->arrayTemplates['main']);
            });
        });
    });

    describe("->getTemplates()", function () {
        context("When Called", function () {
            it("return template array", function () {
                allow($this->arrayLoader)->toReceive('getTemplates')
                    ->andReturn($this->arrayTemplates);
                expect($this->arrayLoader->getTemplates())
                    ->toBe($this->arrayTemplates);
            });
        });
    });
});