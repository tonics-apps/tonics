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

namespace App\Modules\Core\States;

use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Jobs\HandleOnUpdate;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Services\AppInstallationService;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class UpdateMechanismState extends SimpleState
{
    use ConsoleColor;

    # States For ExtractFileState
    const InitialState = 'InitialState';

    const ModuleUpdateState = 'ModuleUpdateState';
    const AppUpdateState    = 'AppUpdateState';

    const DownloadModulesState = 'DownloadModulesState';
    const DownloadAppsState    = 'DownloadAppsState';

    const ExamineCollation      = 'ExamineCollation';
    const DiscoveredFromConsole = 'console';
    const DiscoveredFromBrowser = 'browser';
    public static array $TYPES      = [
        'module' => self::ModuleUpdateState,
        'app'    => self::AppUpdateState,
    ];
    public static array $DOWNLOADER = [
        'module' => self::DownloadModulesState,
        'app'    => self::DownloadAppsState,
    ];
    private array       $updates;
    private array       $types;
    private string      $action;
    private string      $discoveredFrom;
    private array       $collate;

    public function __construct (array $updates = [], array $types = [], string $action = 'discover', string $discoveredFrom = self::DiscoveredFromConsole)
    {
        $this->updates = $updates;
        $this->types = $types;
        $this->action = match ($action) {
            'update' => 'update',
            default => 'discover',
        };
        $this->collate = [];
        $this->discoveredFrom = $discoveredFrom;
        $this->setCurrentState(self::InitialState);
    }

    public function isDiscover (): bool
    {
        return $this->action === 'discover';
    }

    public function isDiscoveredFromBrowser (): bool
    {
        return $this->discoveredFrom === self::DiscoveredFromBrowser;
    }

    /**
     * Reset data and set state to InitialState
     * @return UpdateMechanismState
     */
    public function reset (): static
    {
        $this->collate = [];
        $this->updates = [];
        $this->types = [];
        $this->action = 'discover';
        $this->setCurrentState(self::InitialState);
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function InitialState (): string
    {
        ## Require at-least a GB
        ini_set('memory_limit', '1024M');
        $globalTable = Tables::getTable(Tables::GLOBAL);

        foreach ($this->types as $type) {
            $type = strtolower($type);
            if (isset(self::$TYPES[$type])) {
                # This switch doesn't actually switch any state, it only changes the current state property
                $this->switchState(self::$TYPES[$type]);
                # We are manually doing the dispatching ourselves cos if one update types fails, we want to continue with the next type regardless...
                $this->dispatchState(self::$TYPES[$type]);
            }
        }

        $oldCollate = AppConfig::getAppUpdatesObject();
        if (is_array($oldCollate)) {
            foreach ($oldCollate as $type => $collate) {
                $type = strtolower($type);
                if (key_exists($type, self::$TYPES) && is_array($oldCollate[$type])) {
                    if (isset($this->collate[$type]) && is_array($this->collate[$type])) {
                        $this->collate[$type] = [...$oldCollate[$type], ...$this->collate[$type]];
                    } else {
                        $this->collate[$type] = $oldCollate[$type];
                    }
                }
            }
        }

        if (!empty($this->collate)) {
            db(onGetDB: function (TonicsQuery $db) use ($globalTable) {
                $db->insertOnDuplicate(
                    $globalTable,
                    [
                        'key'   => 'updates',
                        'value' => json_encode($this->collate),
                    ],
                    ['value'],
                );
            });

            if ($this->action === 'update') {
                return $this->switchState(self::ExamineCollation, self::NEXT);
            }
        }

        return self::DONE;
    }

    /**
     * @throws \Exception
     */
    public function ModuleUpdateState (): void
    {
        $tonicsHelper = helper();
        # Discover Module Releases...
        $modules = $tonicsHelper->getModuleActivators([ExtensionConfig::class]);
        helper()->sendMsg(self::getCurrentState(), "Discovering Module Update URLS");
        $this->discover('module', $modules);
    }

    /**
     * @throws \Exception
     */
    public function AppUpdateState (): void
    {
        $tonicsHelper = helper();
        # Discover Applications Releases...
        $appsActivators = $tonicsHelper->getAppsActivator([ExtensionConfig::class], installed: false);
        helper()->sendMsg(self::getCurrentState(), "Discovering Apps Update URLS");
        $this->discover('app', $appsActivators);
    }

    /**
     * @throws \Exception
     */
    public function ExamineCollation (): string
    {
        helper()->updateMaintainanceMode(1);
        foreach ($this->types as $type) {
            $type = strtolower($type);
            if (isset(self::$DOWNLOADER[$type])) {
                # This switch doesn't actually switch any state, it only changes the current state property
                $this->switchState(self::$DOWNLOADER[$type]);
                $this->dispatchState(self::$DOWNLOADER[$type]);
                # If we had an error from the above dispatch, break it immediately
                if ($this->getStateResult() === self::ERROR) {
                    break;
                }
            }
        }

        helper()->updateMaintainanceMode();
        # If we had an error from the above dispatch, return it
        if ($this->getStateResult() === self::ERROR) {
            return self::ERROR;
        }

        $globalTable = Tables::getTable(Tables::GLOBAL);
        if (!empty($this->collate)) {
            db(onGetDB: function (TonicsQuery $db) use ($globalTable) {
                $db->insertOnDuplicate(
                    $globalTable,
                    [
                        'key'   => 'updates',
                        'value' => json_encode($this->collate),
                    ],
                    ['value'],
                );
            });
        }

        return self::DONE;
    }

    /**
     * @throws \Exception
     */
    public function DownloadAppsState (): void
    {
        try {
            $this->downloadExtractCopy('app', AppConfig::getAppsPath());
        } catch (\Throwable $throwable) {
            $this->setErrorMessage($throwable->getMessage());
            $this->setStateResult(SimpleState::ERROR);
            // log..
        }
    }

    /**
     * @throws \Exception
     */
    public function DownloadModulesState (): void
    {
        try {
            $this->downloadExtractCopy('module', AppConfig::getModulesPath());
        } catch (\Throwable $throwable) {
            $this->setErrorMessage($throwable->getMessage());
            $this->setStateResult(SimpleState::ERROR);
            // log..
        }
    }

    /**
     * @param $type
     * @param array $modulesOrApps
     *
     * @return void
     * @throws \Exception
     */
    private function discover ($type, array $modulesOrApps): void
    {
        $tonicsHelper = helper();
        $updates = [];
        foreach ($this->updates as $update) {
            $updates[strtolower($update)] = $update;
        }
        $appInstallationService = $this->getAppInstallationService();
        $this->updates = $updates;
        foreach ($modulesOrApps as $module) {
            if (!$this->isValidModuleApp($module, false)) {
                continue;
            }

            if (isset($module->info()['slug_id'])) {
                $appInstallationService->setAppSlug($module->info()['slug_id']);
                $data = $appInstallationService->getUpdateDiscoveryData($tonicsHelper->getTimeStampFromVersion($module->info()['version'] ?? ''));
                if (!empty($data)) {
                    $this->collate[$type][$module::class] = $data;
                    $tonicsHelper->sendMsg(self::getCurrentState(), "Discovered {$data['name']} - {$data['version']}");
                }
            }
        }
    }


    /**
     * @param $type
     * @param string $dirPath
     *
     * @return void
     * @throws \ReflectionException
     * @throws \Throwable
     */
    private function downloadExtractCopy ($type, string $dirPath): void
    {
        $tonicsHelper = helper();
        $modules = $this->collate[$type] ?? [];
        foreach ($modules as $classString => $module) {

            if (!$this->isValidModuleApp($classString)) {
                continue;
            }

            /** @var ExtensionConfig $object */
            $object = container()->get($classString);
            $moduleOrAppTimeStamp = helper()->getTimeStampFromVersion($object->info()['version'] ?? '');
            $updateTimeStamp = $module['release_timestamp'] ?? '';
            $canUpdate = $updateTimeStamp > $moduleOrAppTimeStamp;

            if ($canUpdate && !empty($module['download_url'])) {
                $appInstallationService = $this->getAppInstallationService();
                $appInstallationService->uploadApp($module['download_url'], [
                    'AppType'   => ($type === 'module') ? 1 : 2,
                    'Signature' => $module['hash'] ?? '',
                ]);

                $appModulePathFolder = $dirPath . DIRECTORY_SEPARATOR . $module['folder_name'];

                if ($appInstallationService->fails()) {
                    $this->setErrorMessage($appInstallationService->getErrorsAsString());
                    $this->setStateResult(SimpleState::ERROR);
                    $tonicsHelper->sendMsg($this->getCurrentState(), $appInstallationService->getErrorsAsString(), 'issue');
                    break;
                } else {
                    # Fire OnUpdate App/Module
                    $onUpdateMigration = new HandleOnUpdate($classString, self::getBinRestartServiceTimestamp());
                    job()->enqueue($onUpdateMigration);

                    $this->collate[$type][$classString]['can_update'] = false;
                    $tonicsHelper->tonicsChmodRecursive($appModulePathFolder);
                }
            }
        }
    }

    /**
     * Return true if...
     *
     * - If classString implements teh ExtensionConfig interface
     * - The updates property is not empty
     * - there is an update
     *
     * @param string|object $objectOrClass
     *
     * @return bool
     * @throws \ReflectionException
     * @throws \Exception
     */
    private function isValidModuleApp (string|object $objectOrClass, bool $checkNameInUpdates = true): bool
    {
        $string = $objectOrClass::class;
        $ref = new \ReflectionClass($objectOrClass);
        $dir = dirname($ref->getFileName());
        $dirName = helper()->getFileName($dir);

        $result = helper()->classImplements($string, [ExtensionConfig::class]);
        if ($checkNameInUpdates) {
            return $result && key_exists(strtolower($dirName), $this->updates);
        }

        return $result;
    }

    /**
     * @throws \Exception
     */
    public static function getBinRestartServiceTimestamp ()
    {
        $json = file_get_contents(AppConfig::getBinRestartServiceJSONFile());
        if (helper()->isJSON($json)) {
            $json = json_decode($json);
            if (isset($json->timestamp)) {
                return $json->timestamp;
            }
        }

        return null;
    }

    /**
     * @param string $url
     *
     * @return mixed
     * @throws \Exception
     */
    private function getJSONFromURL (string $url): mixed
    {
        $siteKey = AppConfig::getAppSiteKey();
        // update_key could be used to identify a site in case of premium plugins and or themes
        $curl = curl_init($url . "?site_key=$siteKey");
        curl_setopt_array($curl, [
            CURLOPT_SSL_VERIFYHOST       => false,
            CURLOPT_PROXY_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYSTATUS     => false,
            CURLOPT_DNS_CACHE_TIMEOUT    => false,
            CURLOPT_DNS_USE_GLOBAL_CACHE => false,
            CURLOPT_FOLLOWLOCATION       => false,
            CURLOPT_RETURNTRANSFER       => true,
            CURLOPT_HTTPHEADER           => ['Accept: application/json'],
            CURLOPT_MAXREDIRS            => 1,
            CURLOPT_USERAGENT            => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36',
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, flags: JSON_PRETTY_PRINT);
    }

    /**
     * @return AppInstallationService
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getAppInstallationService (): AppInstallationService
    {
        return container()->get(AppInstallationService::class);
    }

    /**
     * @return array
     */
    public function getUpdates (): array
    {
        return $this->updates;
    }

    /**
     * @param array $updates
     *
     * @return UpdateMechanismState
     */
    public function setUpdates (array $updates): UpdateMechanismState
    {
        $this->updates = $updates;
        return $this;
    }

    /**
     * @return array
     */
    public function getTypes (): array
    {
        return $this->types;
    }

    /**
     * @param array $types
     *
     * @return UpdateMechanismState
     */
    public function setTypes (array $types): UpdateMechanismState
    {
        $this->types = $types;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction (): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     *
     * @return UpdateMechanismState
     */
    public function setAction (string $action): UpdateMechanismState
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return array
     */
    public function getCollate (): array
    {
        return $this->collate;
    }

    /**
     * @param array $collate
     */
    public function setCollate (array $collate): void
    {
        $this->collate = $collate;
    }
}