<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\States;

use App\Library\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Media\FileManager\LocalDriver;

class UpdateMechanismState extends SimpleState
{
    use ConsoleColor;

    # States For ExtractFileState
    const InitialState = 'InitialState';

    const ModuleUpdateState = 'ModuleUpdateState';
    const AppUpdateState = 'AppUpdateState';

    const DownloadModulesState = 'DownloadModulesState';
    const DownloadAppsState = 'DownloadAppsState';

    const ExamineCollation = 'ExamineCollation';

    private array $updates;
    private array $types;
    private string $action;

    public static array $TYPES = [
        'module' => self::ModuleUpdateState,
        'app' => self::AppUpdateState,
    ];

    public static array $DOWNLOADER = [
        'module' => self::DownloadModulesState,
        'app' => self::DownloadAppsState,
    ];

    const DiscoveredFromConsole = 'console';
    const DiscoveredFromBrowser = 'browser';

    private string $discoveredFrom;
    private array $collate;

    public function __construct(array $updates = [], array $types = [], string $action = 'discover', string $discoveredFrom = self::DiscoveredFromConsole)
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

    public function isDiscover(): bool
    {
        return $this->action === 'discover';
    }

    public function isDiscoveredFromBrowser(): bool
    {
        return $this->discoveredFrom === self::DiscoveredFromBrowser;
    }

    /**
     * Reset data and set state to InitialState
     * @return UpdateMechanismState
     */
    public function reset(): static
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
    public function InitialState(): string
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
            db(true)->insertOnDuplicate(
                $globalTable,
                [
                    'key' => 'updates',
                    'value' => json_encode($this->collate)
                ],
                ['value']
            );

            if ($this->action === 'update') {
                return $this->switchState(self::ExamineCollation, self::NEXT);
            }
        }

        return self::DONE;
    }

    /**
     * @throws \Exception
     */
    public function ModuleUpdateState()
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
    public function AppUpdateState()
    {
        $tonicsHelper = helper();
        # Discover Applications Releases...
        $appsActivators = $tonicsHelper->getAppsActivator([ExtensionConfig::class]);
        helper()->sendMsg(self::getCurrentState(), "Discovering Apps Update URLS");
        $this->discover('app', $appsActivators);
    }

    /**
     * @throws \Exception
     */
    public function ExamineCollation(): string
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
            db(true)->insertOnDuplicate(
                $globalTable,
                [
                    'key' => 'updates',
                    'value' => json_encode($this->collate)
                ],
                ['value']
            );
        }

        return self::DONE;
    }

    /**
     * @throws \Exception
     */
    public function DownloadAppsState()
    {
        try {
            $this->downloadExtractCopy('app', DriveConfig::getTempPathForApps(), AppConfig::getAppsPath());
        } catch (\Throwable $throwable) {
            $this->setErrorMessage($throwable->getMessage());
            $this->setStateResult(SimpleState::ERROR);
            // log..
        }
    }

    /**
     * @throws \Exception
     */
    public function DownloadModulesState()
    {
        try {
            $this->downloadExtractCopy('module', DriveConfig::getTempPathForModules(), AppConfig::getModulesPath());
        } catch (\Throwable $throwable) {
            $this->setErrorMessage($throwable->getMessage());
            $this->setStateResult(SimpleState::ERROR);
            // log..
        }
    }


    /**
     * @throws \Exception
     */
    private function reActivate($directory, $folderName)
    {
        $tonicsHelper = helper();
        $file = $tonicsHelper->findFilesWithExtension(['php'], $directory);
        if (isset($file[0]) && $tonicsHelper->fileExists($file[0])) {
            $class = $tonicsHelper->getFullClassName(file_get_contents($file[0]));
            $implements = @class_implements($class);
            $implementors = [ExtensionConfig::class];

            foreach ($implementors as $implement) {
                if (is_array($implements) && key_exists($implement, $implements)) {
                    $moduleClass = new $class;
                    /**@var $moduleClass ExtensionConfig */
                    $moduleClass->onUpdate();
                    $this->setSucessMessage("$folderName Updated");
                }
            }
        } else {
            $error = "Update Was Successfully But Failed To Call onUpdate method";
            $this->setErrorMessage($error);
            $tonicsHelper->sendMsg($this->getCurrentState(), $error, 'issue');
        }
    }

    /**
     * @param $type
     * @param array $modulesOrApps
     * @return void
     * @throws \Exception
     */
    private function discover($type, array $modulesOrApps): void
    {
        $tonicsHelper = helper();
        $updates = [];
        foreach ($this->updates as $update) {
            $updates[strtolower($update)] = $update;
        }
        $this->updates = $updates;
        foreach ($modulesOrApps as $module) {
            /** @var $module ExtensionConfig */
            $dir = $tonicsHelper->getClassDirectory($module);
            $dirName = $tonicsHelper->getFileName($dir);
            if (count($this->types) === 1) {
                if (!empty($this->updates) && !key_exists(strtolower($dirName), $this->updates)) {
                    continue;
                }
            }

            if (isset($module->info()['update_discovery_url'])) {
                $data = $this->getJSONFromURL($module->info()['update_discovery_url']);
                if (isset($data->tag_name) && isset($data->assets[0])) {
                    $releaseTimestamp = $tonicsHelper->getTimeStampFromVersion($data->tag_name);
                    $moduleTimestamp = $tonicsHelper->getTimeStampFromVersion($module->info()['version'] ?? '');
                    $discovered = (isset($data->name)) ? $data->name : $dirName;
                    $this->collate[$type][$module::class] = [
                        'name' => $discovered,
                        'folder_name' => $dirName,
                        'version' => $data->tag_name,
                        'discovered_from' => $this->discoveredFrom,
                        'download_url' => (isset($data->assets[0]->browser_download_url)) ? $data->assets[0]->browser_download_url : '',
                        'can_update' => $releaseTimestamp > $moduleTimestamp,
                        'module_timestamp' => $moduleTimestamp,
                        'release_timestamp' => $releaseTimestamp,
                        'last_checked' => helper()->date()
                    ];
                    $tonicsHelper->sendMsg(self::getCurrentState(), "Discovered $discovered");
                }
            }
        }
    }


    /**
     * @param $type
     * @param string $tempPath
     * @param string $dirPath
     * @return void
     * @throws \Exception
     */
    private function downloadExtractCopy($type, string $tempPath, string $dirPath): void
    {
        $tonicsHelper = helper();
        $modules = $this->collate[$type] ?? [];
        foreach ($modules as $classString => $module) {
            $ref = new \ReflectionClass($classString);
            $dir = dirname($ref->getFileName());
            $dirName = helper()->getFileName($dir);
            if (count($this->types) === 1) {
                if (!empty($this->updates) && !key_exists(strtolower($dirName), $this->updates)) {
                    continue;
                }
            }

            if ($module['can_update'] && !empty($module['download_url'])) {
                $localDriver = new LocalDriver();

                $name = strtolower($module['version']) . '.zip';
                $folderName = $module['folder_name'];
                $sep = DIRECTORY_SEPARATOR;

                $createFromURLResult = $localDriver->createFromURL($module['download_url'], $tempPath, $name, importToDB: false);
                if ($createFromURLResult === false) {
                    $error = "An Error Occurred Downloading {$module['download_url']}";
                    $this->setErrorMessage($error);
                    $this->setStateResult(SimpleState::ERROR);
                    $tonicsHelper->sendMsg($this->getCurrentState(), $error, 'issue');
                    break;
                }

                $result = $localDriver->extractFile($tempPath . $sep . "$name", $tempPath, importToDB: false);
                $tempPathFolder = $tempPath . $sep . "$folderName";
                $appModulePathFolder = $dirPath . $sep . "$folderName";
                if ($result && $tonicsHelper->fileExists($tempPathFolder) && $tonicsHelper->fileExists($appModulePathFolder)) {

                    # If there is .installed in the app path, drop it in the tempPath, if it fails, then user might
                    # want to re-install the app
                    if ($tonicsHelper->fileExists($appModulePathFolder . DIRECTORY_SEPARATOR . '.installed')) {
                        @file_put_contents($tempPathFolder . DIRECTORY_SEPARATOR . '.installed', '');
                    }

                    $deleted = $tonicsHelper->deleteDirectory($appModulePathFolder);

                    if ($deleted === false) {
                        $error = "An Error Occurred Updating $folderName";
                        $this->setErrorMessage($error);
                        $this->setStateResult(SimpleState::ERROR);
                        $tonicsHelper->sendMsg($this->getCurrentState(), $error, 'issue');
                        break;
                    }

                    $renamedResult = @rename($tempPathFolder, $appModulePathFolder);
                    $tonicsHelper->deleteDirectory($tempPathFolder);
                    if (!$renamedResult) {
                        $error = "An Error Occurred, Moving $tempPathFolder to $appModulePathFolder";
                        $this->setErrorMessage($error);
                        $this->setStateResult(SimpleState::ERROR);
                        $tonicsHelper->sendMsg($this->getCurrentState(), $error, 'issue');
                        break;
                    } else {
                        $directory = $dirPath . $sep . "$folderName";
                        $this->collate[$type][$classString]['can_update'] = false;
                        $this->reActivate($directory, $folderName);
                    }
                } else {
                    $error = "Failed To Extract: '$name'";
                    $this->setErrorMessage($error);
                    $this->setStateResult(SimpleState::ERROR);
                    helper()->sendMsg($this->getCurrentState(), $error, 'issue');
                    break;
                }
            }
        }
    }

    /**
     * @param string $url
     * @return mixed
     */
    private function getJSONFromURL(string $url): mixed
    {
        $update_key = AppConfig::getAppUpdateKey();
        // update_key could be used to identify a site in case of premium plugins and or themes
        $curl = curl_init($url . "?update_key=$update_key");
        curl_setopt_array($curl, [
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_PROXY_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYSTATUS => false,
            CURLOPT_DNS_CACHE_TIMEOUT => false,
            CURLOPT_DNS_USE_GLOBAL_CACHE => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_MAXREDIRS => 1,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36',
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, flags: JSON_PRETTY_PRINT);
    }

    /**
     * @return array
     */
    public function getUpdates(): array
    {
        return $this->updates;
    }

    /**
     * @param array $updates
     * @return UpdateMechanismState
     */
    public function setUpdates(array $updates): UpdateMechanismState
    {
        $this->updates = $updates;
        return $this;
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @param array $types
     * @return UpdateMechanismState
     */
    public function setTypes(array $types): UpdateMechanismState
    {
        $this->types = $types;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     * @return UpdateMechanismState
     */
    public function setAction(string $action): UpdateMechanismState
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return array
     */
    public function getCollate(): array
    {
        return $this->collate;
    }

    /**
     * @param array $collate
     */
    public function setCollate(array $collate): void
    {
        $this->collate = $collate;
    }
}