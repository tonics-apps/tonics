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

use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\States\UpdateMechanismState;
use Devsrealm\TonicsContainer\Container;

describe("Core::UpdateMechanismState", function () {
    beforeAll(function () {

        function container (): Container
        {
            return new Container();
        }
    });

    # Clean Up
    afterAll(function () {
        $cleanUps = [
            __DIR__ . '/UpdateMechanismState/modules/Comment',
            __DIR__ . '/UpdateMechanismState/apps/Tonics404Handler',
            __DIR__ . '/UpdateMechanismState/apps/TonicsAI',
        ];

        foreach ($cleanUps as $cleanUp) {
            if ($this->helper->fileExists($cleanUp)) {
                $this->helper->deleteDirectory($cleanUp);
            }
        }
    });


    describe("Modules", function () {

        it('Should upload comment module with a success message', function () {

            $update = newUpdateMechanismState();
            $update->setCollate(oldVersionCollate())
                ->setUpdates(['Comment'])
                ->setTypes([UpdateMechanismState::SettingsTypeModule])
                ->setAction(UpdateMechanismState::SettingsActionUpdate)
                ->setHandleOnUpdateSuccess(function () {});
            $update->runStates(false);

            expect($update->passes())->toBeTruthy();
            expect($update->getStateResult())->toContain(SimpleState::DONE);
            expect($update->getSuccessMessage())->toContain('[Comment] App Successfully Uploaded');

        });

        it('Should not update as the comment module version and release version are equal', function () {

            $update = newUpdateMechanismState();
            $update->setCollate(compatibleVersionCollate())
                ->setUpdates(['Comment'])
                ->setTypes([UpdateMechanismState::SettingsTypeModule])
                ->setAction(UpdateMechanismState::SettingsActionUpdate)
                ->setHandleOnUpdateSuccess(function () {});
            $update->runStates(false);

            expect($update->passes())->toBeTruthy();
            expect($update->getStateResult())->toContain(SimpleState::DONE);
            expect($update->getSuccessMessage())->toBeEmpty();

        });

    });

    describe("Apps", function () {

        it('Should upload Tonics404Handler App with a success message', function () {

            $update = newUpdateMechanismState();
            $update->setCollate(oldVersionCollate())
                ->setUpdates(['Tonics404Handler'])
                ->setTypes([UpdateMechanismState::SettingsTypeApp])
                ->setAction(UpdateMechanismState::SettingsActionUpdate)
                ->setHandleOnUpdateSuccess(function () {});

            $update->runStates(false);

            expect($update->passes())->toBeTruthy();
            expect($update->getStateResult())->toContain(SimpleState::DONE);
            expect($update->getSuccessMessage())->toContain('[Tonics404Handler] App Successfully Uploaded');

        });

        it('Should upload TonicsAI App with a success message', function () {

            $update = newUpdateMechanismState();
            $update->setCollate(oldVersionCollate())
                ->setUpdates(['TonicsAI'])
                ->setTypes([UpdateMechanismState::SettingsTypeApp])
                ->setAction(UpdateMechanismState::SettingsActionUpdate)
                ->setHandleOnUpdateSuccess(function () {});

            $update->runStates(false);

            expect($update->passes())->toBeTruthy();
            expect($update->getStateResult())->toContain(SimpleState::DONE);
            expect($update->getSuccessMessage())->toContain('[TonicsAI] App Successfully Uploaded');

        });

        it('Should not update as the Tonics404Handler App version and release version are equal', function () {

            $update = newUpdateMechanismState();
            $update->setCollate(compatibleVersionCollate())
                ->setUpdates(['Tonics404Handler'])
                ->setTypes([UpdateMechanismState::SettingsTypeApp])
                ->setAction(UpdateMechanismState::SettingsActionUpdate)
                ->setHandleOnUpdateSuccess(function () {});
            $update->runStates(false);

            expect($update->passes())->toBeTruthy();
            expect($update->getStateResult())->toContain(SimpleState::DONE);
            expect($update->getSuccessMessage())->toBeEmpty();

        });

    });

});


/**
 * @throws Exception
 */
function newUpdateMechanismState (): UpdateMechanismState
{
    return new UpdateMechanismState([
        UpdateMechanismState::SettingsKeyTempPath   => __DIR__ . '/UpdateMechanismState/temp',
        UpdateMechanismState::SettingsKeyAppPath    => __DIR__ . '/UpdateMechanismState/apps',
        UpdateMechanismState::SettingsKeyModulePath => __DIR__ . '/UpdateMechanismState/modules',
        UpdateMechanismState::SettingsKeyCollate    => [],
        UpdateMechanismState::SettingsKeyVerbosity  => false,
    ]);
}

function compatibleVersionCollate (): array
{
    return [
        'module' => [
            'App\Modules\Comment\CommentActivator' => [
                "name"                  => "Comment",
                "folder_name"           => "Comment",
                "hash"                  => "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx22222222",
                "version"               => "1-O-Ola.1714604528",
                "download_url"          => "...",
                "download_url_override" => dirname(__FILE__, 2) . '/Services/AppInstallationService/Comment.zip',
                "module_timestamp"      => "1714604528",
                "release_timestamp"     => "1714604528", // // from latest version or something
            ],
        ],
        'app'    => [
            "App\Apps\Tonics404Handler\Tonics404HandlerActivator" => [
                "name"                  => "Tonics404Handler",
                "folder_name"           => "Tonics404Handler",
                "hash"                  => "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx22222222",
                "version"               => "1-O-app.1714604528",
                "download_url"          => "...",
                // this would download from the local storage and bypass remote
                "download_url_override" => dirname(__FILE__, 2) . '/Services/AppInstallationService/Tonics404Handler.zip',
                "module_timestamp"      => "1714604528",
                "release_timestamp"     => "1714604528", // from latest version or something
            ],
        ],
    ];
}


function oldVersionCollate (): array
{
    return [
        'module' => [
            'App\Modules\Comment\CommentActivator' => [
                "name"                  => "Comment",
                "folder_name"           => "Comment",
                "hash"                  => "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx22222222",
                "version"               => "1-O-Ola.1714604528",
                "download_url"          => "...",
                "download_url_override" => dirname(__FILE__, 2) . '/Services/AppInstallationService/Comment.zip',
                "module_timestamp"      => "1714604527",
                "release_timestamp"     => "1714604528", // // from latest version or something
            ],
        ],
        'app'    => [
            "App\Apps\Tonics404Handler\Tonics404HandlerActivator" => [
                "name"                  => "Tonics404Handler",
                "folder_name"           => "Tonics404Handler",
                "hash"                  => "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx22222222",
                "version"               => "1-O-app.1714604528",
                "download_url"          => "...",
                // this would download from the local storage and bypass remote
                "download_url_override" => dirname(__FILE__, 2) . '/Services/AppInstallationService/Tonics404Handler.zip',
                "module_timestamp"      => "1714604527",
                "release_timestamp"     => "1714604528", // from latest version or something
            ],
            "App\Apps\TonicsAI\TonicsAIActivator"                 => [
                "name"                  => "TonicsAI",
                "folder_name"           => "TonicsAI",
                "hash"                  => "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx22222222",
                "version"               => "1-O-app.1714604528",
                "download_url"          => "...",
                // this would download from the local storage and bypass remote
                "download_url_override" => dirname(__FILE__, 2) . '/Services/AppInstallationService/TonicsAI.zip',
                "module_timestamp"      => "1714604527",
                "release_timestamp"     => "1714604528", // from latest version or something
            ],
        ],
    ];
}
