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

use App\Modules\Customer\Controllers\CustomerSettingsController;
use App\Modules\Customer\EventHandlers\SpamProtections\GlobalVariablesCheck;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInputInterface;
use Devsrealm\TonicsRouterSystem\RequestInput;

describe("GlobalVariableCheck", function () {

    if (!function_exists('input')) {
        function input (): TonicsRouterRequestInputInterface
        {
            return new RequestInput();
        }
    }

    setUpGlobalVariables();


    /**
     * @throws Throwable
     */
    describe("isSpam()", function () {

        #-------------------------
        # CASES WHERE spam='1'
        #---------------------------

        it('Should mark as spam if key:HTTP_USER_AGENT, valueStartsWith, and valueContains matches', function () {
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[SERVER key='HTTP_USER_AGENT' valueStartsWith='Moz' valueContains='NT' spam='1']"];
            expect(getGlobalVariables()->isSpam($data))->toBeTruthy();
        });

        it('Should mark as spam if key:HTTP_USER_AGENT, and valueContains matches AppleWebKit', function () {
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[SERVER key='HTTP_USER_AGENT' valueContains='AppleWebKit' spam='1']"];
            expect(getGlobalVariables()->isSpam($data))->toBeTruthy();
        });

        it('Should mark as spam if there is no key:HTTP_USER_AGENT_NO_KEY', function () {
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[SERVER keyNot='HTTP_USER_AGENT_NO_KEY' spam='1']"];
            expect(getGlobalVariables()->isSpam($data))->toBeTruthy();
        });

        it('Should not mark as spam if key:HTTP_USER_AGENT, valueStartsWith does not match', function () {
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[SERVER key='HTTP_USER_AGENT' valueStartsWith='Opera' spam='1']"];
            expect(getGlobalVariables()->isSpam($data))->toBeFalsy();
        });

        it('Should mark as spam if key:HTTP_USER_AGENT, valueNotContains does not match', function () {
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[SERVER key='HTTP_USER_AGENT' valueNotContains='Firefox' spam='1']"];
            expect(getGlobalVariables()->isSpam($data))->toBeTruthy();
        });

        it('Should mark as spam if key:HTTP_USER_AGENT, valueEndsWith matches', function () {
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[SERVER key='HTTP_USER_AGENT' valueEndsWith='Edg/126.0.0.0' spam='1']"];
            expect(getGlobalVariables()->isSpam($data))->toBeTruthy();
        });

        it('Should not mark as spam if key:HTTP_USER_AGENT, valueNotEndsWith matches', function () {
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[SERVER key='HTTP_USER_AGENT' valueNotEndsWith='Chrome' spam='1']"];
            expect(getGlobalVariables()->isSpam($data))->toBeTruthy();
        });

        it('Should mark as spam if keyNot:HTTP_ACCEPT_LANGUAGE is not present', function () {
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[SERVER keyNot='HTTP_ACCEPT_LANGUAGE' spam='1']"];
            expect(getGlobalVariables()->isSpam($data))->toBeTruthy();
        });

        it('Should mark as spam if key:trap, valueEmpty is true', function () {
            setUpGlobalVariables([], ['trap' => '']);
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[POST key='trap' valueEmpty='1' spam='1']"];
            expect(getGlobalVariables()->isSpam($data))->toBeTruthy();
        });

        it('Should not mark as spam if key:trap, valueEmpty is false', function () {
            setUpGlobalVariables([], ['trap' => 'not-empty']);
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[POST key='trap' valueEmpty='1' spam='1']"];
            expect(getGlobalVariables()->isSpam($data))->toBeFalsy();
        });

        it('Should mark as spam if keyNot:trap is not present', function () {
            unset($_POST['trap']);
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[POST keyNot='trap' spam='1']"];
            expect(getGlobalVariables()->isSpam($data))->toBeTruthy();
        });

        it('Should not mark as spam if key:trap and value matches exactly', function () {
            setUpGlobalVariables([], ['trap' => 'I-spam-for-a-living']);
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[POST key='trap' value='I-spam-for-a-living' spam='1']"];
            expect(getGlobalVariables()->isSpam($data))->toBeTruthy();
        });

        #-------------------------
        # CASES WHERE spam='0'
        #---------------------------

        it('Should not mark as spam if key:HTTP_USER_AGENT, valueStartsWith and valueContains matches but spam=0', function () {
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[SERVER key='HTTP_USER_AGENT' valueStartsWith='Moz' valueContains='NT' spam='0']"];
            expect(getGlobalVariables()->isSpam($data))->toBeFalsy();
        });

        it('Should not mark as spam if key:HTTP_USER_AGENT, and valueContains matches AppleWebKit but spam=0', function () {
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[SERVER key='HTTP_USER_AGENT' valueContains='AppleWebKit' spam='0']"];
            expect(getGlobalVariables()->isSpam($data))->toBeFalsy();
        });

        it('Should not mark as spam if there is no key:HTTP_USER_AGENT_NO_KEY but spam=0', function () {
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[SERVER keyNot='HTTP_USER_AGENT_NO_KEY' spam='0']"];
            expect(getGlobalVariables()->isSpam($data))->toBeFalsy();
        });

        it('Should not mark as spam if key:trap, valueEmpty is true but spam=0', function () {
            setUpGlobalVariables([], ['trap' => '']);
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[POST key='trap' valueEmpty='1' spam='0']"];
            expect(getGlobalVariables()->isSpam($data))->toBeFalsy();
        });

        it('Should not mark as spam if key:trap, valueEmpty is false but spam=0', function () {
            setUpGlobalVariables([], ['trap' => '']);
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[POST key='trap' valueEmpty='0' spam='0']"];
            expect(getGlobalVariables()->isSpam($data))->toBeFalsy();
        });

        it('Should not mark as spam if keyNot:trap is not present but spam=0', function () {
            unset($_POST['trap']);
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[POST keyNot='trap' spam='0']"];
            expect(getGlobalVariables()->isSpam($data))->toBeFalsy();
        });

        #-------------------------
        # FOR MORE COMPLEX TEST CASES
        #----------------------------------

        it('Should mark as spam if key:HTTP_USER_AGENT starts with Moz and ends with Edg/126.0.0.0', function () {
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[SERVER key='HTTP_USER_AGENT' valueStartsWith='Moz' valueEndsWith='Edg/126.0.0.0' spam='1']"];
            expect(getGlobalVariables()->isSpam($data))->toBeTruthy();
        });

        it('Should mark as spam if key:HTTP_USER_AGENT starts with Moz, contains NT, and ends with Edg/126.0.0.0', function () {
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[SERVER key='HTTP_USER_AGENT' valueStartsWith='Moz' valueContains='NT' valueEndsWith='Edg/126.0.0.0' spam='1']"];
            expect(getGlobalVariables()->isSpam($data))->toBeTruthy();
        });

        it('Should mark as spam if key:HTTP_USER_AGENT does not start with Opera and does not end with Gecko', function () {
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[SERVER key='HTTP_USER_AGENT' valueNotStartsWith='Opera' valueNotEndsWith='Gecko' spam='1']"];
            expect(getGlobalVariables()->isSpam($data))->toBeTruthy();
        });

        it('Should not mark as spam if key:HTTP_USER_AGENT starts with Moz but does not end with Chrome', function () {
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[SERVER key='HTTP_USER_AGENT' valueStartsWith='Moz' valueNotEndsWith='Chrome' spam='1']"];
            expect(getGlobalVariables()->isSpam($data))->toBeTruthy();
        });

        it('Should mark as spam if key:HTTP_USER_AGENT contains AppleWebKit and does not contain Firefox', function () {
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[SERVER key='HTTP_USER_AGENT' valueContains='AppleWebKit' valueNotContains='Firefox' spam='1']"];
            expect(getGlobalVariables()->isSpam($data))->toBeTruthy();
        });

        it('Should mark as spam if key:trap is not empty and keyNot:another_key is not present', function () {
            setUpGlobalVariables([], ['trap' => 'not-empty']);
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[POST key='trap' valueEmpty='0' keyNot='another_key' spam='1']"];
            expect(getGlobalVariables()->isSpam($data))->toBeTruthy();
        });

        it('Should not mark as spam if key:trap is not empty but spam=0', function () {
            setUpGlobalVariables([], ['trap' => 'not-empty']);
            $data = [CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput => "[POST key='trap' valueEmpty='0' spam='0']"];
            expect(getGlobalVariables()->isSpam($data))->toBeFalsy();
        });

    });

});

/**
 * @return GlobalVariablesCheck
 */
function getGlobalVariables (): GlobalVariablesCheck
{
    return new GlobalVariablesCheck();
}

function setUpGlobalVariables ($overrideServer = [], $overridePost = []): void
{
    $_SERVER = [
        ...[
            "USER"            => "www-data",
            "HOME"            => "/var/www",
            "HTTP_ACCEPT"     => "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7",
            "HTTP_USER_AGENT" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36 Edg/126.0.0.0",
        ],
        ...$overrideServer,
    ];

    $_POST = [
        ...[
            "trap"     => "I-spam-for-a-living",
            "trap-not" => "spam-for-a-living",
            "username" => "www-data",
        ],
        ...$overridePost,
    ];
}
