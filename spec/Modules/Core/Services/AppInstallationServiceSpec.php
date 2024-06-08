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

use App\Modules\Core\Services\AppInstallationService;
use Devsrealm\TonicsContainer\Container;
use Devsrealm\TonicsHelpers\TonicsHelpers;

describe("Core::AppInstallationService", function () {


    beforeAll(function () {
        function route (string $name, array $parameters = []): string
        {
            return '';
        }

        function helper (): TonicsHelpers
        {
            return new Devsrealm\TonicsHelpers\TonicsHelpers();
        }

    });

    # Clean Up
    afterAll(function () {
        $appInstallationService = newInstanceOfAppInstallationService($this);
        $dirPath = __DIR__ . '/AppInstallationService/apps/Tonics404Handler';
        if ($appInstallationService->getHelpers()->fileExists($dirPath)) {
            $appInstallationService->getHelpers()->deleteDirectory($dirPath);
        }
    });


    describe("->uploadApp();", function () {

        $settings = [
            'AppType'             => 2,
            'AppPath'             => __DIR__ . '/AppInstallationService/apps',
            'ModulePath'          => __DIR__ . '/AppInstallationService/modules',
            'TempPath'            => __DIR__ . '/AppInstallationService/temp',
            'TempPathFolderName'  => 'apps',
            'DownloadURLOverride' => __DIR__ . '/AppInstallationService/Tonics404Handler.zip',
            'ForceSigning'        => false,
        ];

        it('should fail if AppType is invalid', function () use ($settings) {

            $appTypes = [
                3,
                0,
                -1,
                '',
            ];

            $appInstallationService = newInstanceOfAppInstallationService($this);

            foreach ($appTypes as $appType) {
                $settings['AppType'] = $appType;
                $appInstallationService->uploadApp('', $settings);
                expect($appInstallationService->fails())->toBeTruthy();
                expect($appInstallationService->getErrorsAsString())->toContain("App Type Should Either Be 1 for Module or 2 for Apps");
            }

        });

        it('should upload app successfully with valid parameters', function () use ($settings) {
            $appInstallationService = newInstanceOfAppInstallationService($this);
            $settings['AppType'] = 2;
            $appInstallationService->uploadApp('', $settings);
            expect($appInstallationService->fails())->toBeFalsy();
            expect($appInstallationService->getMessage())->toContain('App Successfully Uploaded');
        });

        it('should fail as app has a different namespace', function () use ($settings) {
            $appInstallationService = newInstanceOfAppInstallationService($this);
            $settings['AppType'] = 1;
            $appInstallationService->uploadApp('', $settings);

            expect($appInstallationService->fails())->toBeTruthy();
            expect($appInstallationService->getErrorsAsString())->toContain('The AppType is Module and Should be a Valid Module Namespace');
        });

        it('should fail as the authenticity cant be verified', function () use ($settings) {
            $appInstallationService = newInstanceOfAppInstallationService($this);
            $settings['ForceSigning'] = true;
            $appInstallationService->uploadApp('', $settings);
            expect($appInstallationService->fails())->toBeTruthy();
            expect($appInstallationService->getErrorsAsString())->toContain("The authenticity of Tonics404Handler.zip could not be verified as no valid signature was found.");
        });

        it('The authenticity can be verified, upload app success', function () use ($settings) {
            $appInstallationService = newInstanceOfAppInstallationService($this);
            $settings['ForceSigning'] = true;
            $settings['Signature'] = 'I3YtrvQLE9+iqM9EUQEtQdn3eLiB3Mh0mUU5zkaGqsqsotC9v+GljtK8Jh9rYB0uWI35punoRbWl+47U2Cw7DQ';
            $appInstallationService->uploadApp('', $settings);
            expect($appInstallationService->fails())->toBeFalsy();
            expect($appInstallationService->getMessage())->toContain('App Successfully Uploaded');
        });

        it('should fail as the signature is incorrect and as such the authenticity cant be verified', function () use ($settings) {
            $appInstallationService = newInstanceOfAppInstallationService($this);
            $settings['ForceSigning'] = true;
            $settings['Signature'] = 'I3YtrvQLE9+xxxxxxxxx+GljtK8Jh9rYB0uWI35punoRbWl+47U2Cw7DQ';
            $appInstallationService->uploadApp('', $settings);
            expect($appInstallationService->fails())->toBeTruthy();
            expect($appInstallationService->getErrorsAsString())->toContain('The authenticity of Tonics404Handler.zip could not be verified as no valid signature was found.');
        });

        /*        it('should upload remote module successfully with valid parameters', function () use ($settings) {
                    $appInstallationService = newInstanceOfAppInstallationService($this);
                    $settings['AppType'] = 1;
                    unset($settings['DownloadURLOverride']);
                    $appInstallationService->uploadApp('https://github.com/tonics-apps/tonics-customer-module/releases/download/1-O-Ola.1715951400/Customer.zip', $settings);
                    expect($appInstallationService->fails())->toBeFalsy();
                    expect($appInstallationService->getMessage())->toContain('App Successfully Uploaded');
                });*/

        // Testing OneDrive Link
        /* it('should upload remote module successfully with valid parameters', function () use ($settings) {
             $appInstallationService = newInstanceOfAppInstallationService($this);
             $settings['AppType'] = 1;
             unset($settings['DownloadURLOverride']);
             $appInstallationService->uploadApp('https://faruqa-my.sharepoint.com/:u:/g/personal/olayemi_faruqa_onmicrosoft_com/ETJ_L3_KdIdBg-l7h2sQ9GUBFcHVmGjVf51GgP8U7opV4w?e=GBZOn0&download=1', $settings);
             expect($appInstallationService->fails())->toBeFalsy();
             expect($appInstallationService->getMessage())->toContain('App Successfully Uploaded');
         });*/

    });

});

/**
 * @param $scope
 *
 * @return AppInstallationService
 * @throws Exception
 */
function newInstanceOfAppInstallationService ($scope): AppInstallationService
{
    return new App\Modules\Core\Services\AppInstallationService([
        'TonicsHelper' => $scope->helper,
        'Container'    => new Container(),
    ]);
}