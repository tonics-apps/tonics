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

    const ExamineCollation = 'ExamineCollation';

    # Discovered From Wia
    const DiscoveredFromConsole = 'console';
    const DiscoveredFromBrowser = 'browser';

    # Settings Const
    const SettingsTypeApp        = 'app';
    const SettingsTypeModule     = 'module';
    const SettingsActionDiscover = 'discover';
    const SettingsActionUpdate   = 'update';

    # Settings Key For Constructor
    /**
     * `Accept: []`
     *
     * Array names of what you are updating or discovering, e.g `['Core', 'Post', ...]`
     */
    const SettingsKeyUpdates = 'Updates';
    /**
     * `Accept: []`
     *
     * The Update Type, can either be `['App' or 'Module' or both 'App' and 'Modules']`, if you are doing an update, you can only have one type,
     * however, you can have multiple types if you want to discover new releases
     */
    const SettingsKeyTypes = 'Types';
    /**
     * `Accept: STRING`
     *
     * 'Discover' or 'Update', Discover pulls the latest version while Update, discovers and update
     */
    const SettingsKeyAction = 'Action';
    /**
     * `Accept: []`
     *
     * The collation of the apps/modules.
     * Good, for testing, if there is collate, it would use that instead of accessing the collation in the database
     */
    const SettingsKeyCollate = 'Collate';
    /**
     * `Accept: STRING`
     *
     * State you want to start from, this defaults to initialState
     */
    const SettingsKeyCurrentState = 'CurrentState';
    /**
     * `Accept: STRING`
     *
     * Where the app should be uploaded to, default to: AppConfig::getAppsPath()
     */
    const SettingsKeyAppPath = 'AppPath';
    /**
     * `Accept: STRING`
     *
     * Where module should be uploaded to, default to: AppConfig::getModulesPath()
     */
    const SettingsKeyModulePath = 'ModulePath';
    /**
     * `Accept: STRING`
     *
     * Temp path to use when downloading apps, it uses a nice default if none is set
     */
    const SettingsKeyTempPath = 'TempPath';
    /**
     * `Accept: []`
     *
     * All App directories, it uses the default app locations
     */
    const SettingsKeyAppDirectories = 'AppDirectories';
    /**
     * `Accept: []`
     *
     * All Module directories, it uses the default app locations
     */
    const SettingsKeyModuleDirectories = 'ModuleDirectories';
    /**
     * `Accept: callable($classString)`
     *
     * Handle when app or module has successfully updated, it gives you the `$classString` of whatever got updated.
     *
     * If this is null, it would default to enqueuing HandleOnUpdate, use a voided function as callable to override that if you do not
     *  want to do anything.
     */
    const SettingsKeyHandleOnUpdateSuccess = 'HandleOnUpdateSuccess';

    /**
     * `Accept: Bool`
     *
     * Whether you want verbosity or not
     */
    const SettingsKeyVerbosity = 'Verbosity';

    # ALL AVAILABLE STATES
    public static array $STATES = [
        self::InitialState         => self::InitialState,
        self::ModuleUpdateState    => self::ModuleUpdateState,
        self::AppUpdateState       => self::AppUpdateState,
        self::DownloadModulesState => self::DownloadModulesState,
        self::DownloadAppsState    => self::DownloadAppsState,
        self::ExamineCollation     => self::ExamineCollation,
    ];

    public static array $TYPES      = [
        'module' => self::ModuleUpdateState,
        'app'    => self::AppUpdateState,
    ];
    public static array $DOWNLOADER = [
        'module' => self::DownloadModulesState,
        'app'    => self::DownloadAppsState,
    ];
    private array       $settings;
    private array       $updates;
    private array       $types;
    private string      $action;
    private string      $discoveredFrom;
    private array       $collate;
    private array       $appDirectories;
    private array       $moduleDirectories;
    private string      $appPath;
    private string      $modulePath;
    private ?string     $tempPath;
    private bool        $verbosity  = true;
    private ?\Closure   $handleOnUpdateSuccess;

    /**
     * Settings can contain the following, all the below as a default, except Updates, Types and Action:
     *
     * ```
     * [
     *      UpdateMechanismState::SettingsKeyUpdates => ['...'],
     *      UpdateMechanismState::SettingsKeyTypes => => ['...'],
     *      UpdateMechanismState::SettingsKeyAction => 'Discover' or 'Update',
     *      UpdateMechanismState::SettingsKeyCollate => [], // If there is collate, it would use that instead of accessing the collation in the database
     *      UpdateMechanismState::SettingsKeyCurrentState => '...',
     *      UpdateMechanismState::SettingsKeyAppPath => '...',
     *      UpdateMechanismState::SettingsKeyModulePath => '...',
     *      UpdateMechanismState::SettingsKeyAppDirectories => '[...]',
     *      UpdateMechanismState::SettingsKeyModuleDirectories => '[...]',
     *      UpdateMechanismState::SettingsKeyTempPath => '...',
     *      UpdateMechanismState::SettingsKeyVerbosity => true or false,
     * ]
     * ```
     *
     * @param array $settings
     *
     * @return void
     * @throws \Exception
     */
    public function __construct (array $settings = [])
    {
        $this->settings = $settings;
        $this->setUpdates($settings[self::SettingsKeyUpdates] ?? []);
        $this->setTypes($settings[self::SettingsKeyTypes] ?? []);
        $this->action = match (strtolower($settings[self::SettingsKeyAction] ?? '')) {
            'update' => 'update',
            default => 'discover',
        };

        $this->appPath = $settings[self::SettingsKeyAppPath] ?? AppConfig::getAppsPath();
        $this->modulePath = $settings[self::SettingsKeyModulePath] ?? AppConfig::getModulesPath();

        $this->appDirectories = $settings[self::SettingsKeyAppDirectories] ?? helper()->getAllAppsDirectory();
        $this->moduleDirectories = $settings[self::SettingsKeyModuleDirectories] ?? helper()->getAllModulesDirectory();

        $this->tempPath = $settings[self::SettingsKeyTempPath] ?? null;

        $this->setCollate($settings[self::SettingsKeyCollate] ?? []);

        $this->discoveredFrom = (helper()->isCLI()) ? self::DiscoveredFromConsole : self::DiscoveredFromBrowser;

        $currentState = $settings[self::SettingsKeyCurrentState] ?? self::InitialState;

        if (!isset(self::$STATES[$currentState])) {
            throw new \Exception("There is No State Such as $currentState");
        }

        $this->setCurrentState($currentState);
        $this->setHandleOnUpdateSuccess($settings[self::SettingsKeyHandleOnUpdateSuccess] ?? null);
        $this->setVerbosity($settings[self::SettingsKeyVerbosity] ?? true);
        helper()->updateActivateEventStreamMessage($this->isVerbosity());
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

        # If this is empty then, we can do the DB operation, otherwise, it could only mean user want to bypass the DB as it have collation
        if (empty($this->settings['Collate'])) {
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
        } else {
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
        $modules = $tonicsHelper->getModuleActivators([ExtensionConfig::class], $this->moduleDirectories);
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
        $appsActivators = $tonicsHelper->getAppsActivator([ExtensionConfig::class], $this->appDirectories, installed: false);
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

        # If this is empty, then, we can do the DB operation, otherwise, it could only mean user want to bypass the DB as it have collation
        if (empty($this->settings['Collate'])) {
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
        }

        return self::DONE;
    }

    /**
     * @throws \Exception
     */
    public function DownloadAppsState (): void
    {
        try {
            $this->downloadExtractCopy('app', $this->appPath);
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
            $this->downloadExtractCopy('module', $this->modulePath);
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

            $moduleInfo = $module->info();
            if (isset($moduleInfo['slug_id']) || isset($moduleInfo['name'])) {
                $slug = $moduleInfo['slug_id'] ?? $moduleInfo['name'];
                if (empty($slug)) {
                    continue;
                }

                $appInstallationService->setAppSlug($slug);
                $data = $appInstallationService->getUpdateDiscoveryData($tonicsHelper->getTimeStampFromVersion($module->info()['version'] ?? ''));
                if (!empty($data)) {
                    if (isset($data['name'])) {
                        $this->collate[$type][$module::class] = $data;
                        $tonicsHelper->sendMsg(self::getCurrentState(), "Discovered {$data['name']} - {$data['version']}");
                    }
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

            if (isset($module['module_timestamp'])) {
                $moduleOrAppTimeStamp = $module['module_timestamp'];
            } else {
                /** @var ExtensionConfig $object */
                $object = container()->get($classString);
                $moduleOrAppTimeStamp = helper()->getTimeStampFromVersion($object->info()['version'] ?? '');
            }

            $updateTimeStamp = $module['release_timestamp'] ?? '';
            $canUpdate = $updateTimeStamp > $moduleOrAppTimeStamp;

            if ($canUpdate && !empty($module['download_url'])) {
                $appInstallationService = $this->getAppInstallationService();
                $settings = [
                    AppInstallationService::SettingsKeyUploadAppAppType    => ($type === 'module') ? 1 : 2,
                    AppInstallationService::SettingsKeyUploadAppSignature  => $module['hash'] ?? '',
                    AppInstallationService::SettingsKeyUploadAppAppPath    => $this->appPath,
                    AppInstallationService::SettingsKeyUploadAppModulePath => $this->modulePath,
                    AppInstallationService::SettingsKeyUploadAppTempPath   => $this->tempPath,
                    AppInstallationService::SettingsKeyUploadAppVerbosity  => $this->isVerbosity(),
                ];

                # Override remote download and download from the local storage instead
                if (isset($module['download_url_override'])) {
                    $settings[AppInstallationService::SettingsKeyUploadAppDownloadURLOverride] = $module['download_url_override'];
                }

                $appInstallationService->uploadApp($module['download_url'], $settings);
                $appModulePathFolder = $dirPath . DIRECTORY_SEPARATOR . $module['folder_name'];

                if ($appInstallationService->fails()) {
                    $this->setErrorMessage($appInstallationService->getErrorsAsString());
                    $this->setStateResult(SimpleState::ERROR);
                    $tonicsHelper->sendMsg($this->getCurrentState(), $appInstallationService->getErrorsAsString(), 'issue');
                    break;
                } else {
                    # Fire OnUpdate App/Module
                    $handleCall = $this->getHandleOnUpdateSuccess();
                    $handleCall($classString);
                    $this->setSuccessMessage($appInstallationService->getMessage());
                    $this->collate[$type][$classString]['can_update'] = false;
                    $tonicsHelper->tonicsChmodRecursive($appModulePathFolder);
                }
            }
        }
    }

    /**
     * Return true if...
     *
     * - If classString implements the ExtensionConfig interface
     * - The updates property is not empty
     * - there is an update
     *
     * @param string|object $objectOrClass
     * @param bool $checkNameInUpdates
     *
     * @return bool
     * @throws \ReflectionException
     * @throws \Exception
     */
    private function isValidModuleApp (string|object $objectOrClass, bool $checkNameInUpdates = true): bool
    {
        if (is_object($objectOrClass)) {
            $objectOrClass = $objectOrClass::class;
        }

        if (!AppConfig::nameSpaceExistPath($objectOrClass)) {
            return false;
        }

        $ref = new \ReflectionClass($objectOrClass);
        $dir = dirname($ref->getFileName());
        $dirName = helper()->getFileName($dir);

        $result = helper()->classImplements($objectOrClass, [ExtensionConfig::class]);
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
        $this->settings[self::SettingsKeyUpdates] = $updates;
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
        $this->settings[self::SettingsKeyTypes] = $types;
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
        $this->settings[self::SettingsKeyAction] = $action;
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
     *
     * @return UpdateMechanismState
     */
    public function setCollate (array $collate): UpdateMechanismState
    {
        $this->collate = $collate;
        $this->settings[self::SettingsKeyCollate] = $collate;
        return $this;
    }

    public function getHandleOnUpdateSuccess (): ?\Closure
    {
        return $this->handleOnUpdateSuccess;
    }

    /**
     * If this is null, it would default to enqueuing HandleOnUpdate, use a voided function as callable to override that if you do not
     * want to do anything.
     *
     * @param \Closure|null $handleOnUpdateSuccess
     *
     * @return $this
     */
    public function setHandleOnUpdateSuccess (?\Closure $handleOnUpdateSuccess): UpdateMechanismState
    {
        if ($handleOnUpdateSuccess === null) {
            $handleOnUpdateSuccess = function ($classString) {
                job()->enqueue(new HandleOnUpdate($classString, self::getBinRestartServiceTimestamp()));
            };
        }

        $this->handleOnUpdateSuccess = $handleOnUpdateSuccess;
        $this->settings[self::SettingsKeyHandleOnUpdateSuccess] = $handleOnUpdateSuccess;
        return $this;
    }

    public function isVerbosity (): bool
    {
        return $this->verbosity;
    }

    /**
     * @param bool $verbosity
     *
     * @return $this
     */
    public function setVerbosity (bool $verbosity): UpdateMechanismState
    {
        $this->verbosity = $verbosity;
        $this->settings[self::SettingsKeyVerbosity] = $verbosity;
        return $this;
    }
}