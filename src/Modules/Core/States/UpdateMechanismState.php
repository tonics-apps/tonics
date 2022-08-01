<?php

namespace App\Modules\Core\States;

use App\Library\ModuleRegistrar\Interfaces\ModuleConfig as ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
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
    const DownloadAppsState = 'DownloadPluginsState';

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

    private string $discoveredFrom;
    private array $collate = [];

    public function __construct(array $updates = [], array $types = [], string $action = 'discover', string $discoveredFrom = 'console')
    {
        $this->updates = $updates;
        $this->types = $types;
        $this->action = match ($action) {
            'update' => 'update',
            default => 'discover',
        };
        $this->collate = [];
        $this->discoveredFrom = $discoveredFrom;
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
        if (is_array($oldCollate)){
            $this->collate = [...$oldCollate, ...$this->collate];
        }

        if (!empty($this->collate)){
            db()->insertOnDuplicate(
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
        $modules = $tonicsHelper->getModuleActivators([ModuleConfig::class, PluginConfig::class]);
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
        $appsActivators = $tonicsHelper->getAppsActivator([PluginConfig::class]);
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
                # We are manually doing the dispatching ourselves cos if one update types fails, we want to continue with the next type regardless...
                $this->dispatchState(self::$DOWNLOADER[$type]);
            }
        }
        helper()->updateMaintainanceMode();
        return self::DONE;
    }

    /**
     * @throws \Exception
     */
    public function DownloadAppsState()
    {
        $this->downloadExtractCopy('app', DriveConfig::getTempPathForApps(), AppConfig::getAppsPath());
    }

    /**
     * @throws \Exception
     */
    public function DownloadModulesState()
    {
        $this->downloadExtractCopy('module', DriveConfig::getTempPathForModules(), AppConfig::getModulesPath());
    }


    /**
     * @throws \Exception
     */
    private function reActivate($directory, $folderName)
    {
        $tonicsHelper = helper();
        $file = $tonicsHelper->findFilesWithExtension(['php'], $directory);
        if (isset($file[0]) && $tonicsHelper->fileExists($file[0])){
            $class = $tonicsHelper->getFullClassName(file_get_contents($file[0]));
            $implements = @class_implements($class);
            $implementors = [PluginConfig::class];

            foreach ($implementors as $implement) {
                if (is_array($implements) && key_exists($implement, $implements)) {
                    $moduleClass = new $class;
                    /**@var $moduleClass PluginConfig */
                    $moduleClass->onUpdate();
                    $this->successMessage("$folderName Updated");
                }
            }
        } else {
            $error = "Update Was Successfully But Failed To Call onUpdate method";
            $this->errorMessage($error);
            helper()->sendMsg($this->getCurrentState(), $error, 'issue');
        }
    }

    /**
     * @param $type
     * @param array $modulesOrPluginsOrThemes
     * @return void
     * @throws \Exception
     */
    private function discover($type, array $modulesOrPluginsOrThemes): void
    {
        $tonicsHelper = helper();
        $this->updates = array_filter(array_combine($this->updates, $this->updates));
        foreach ($modulesOrPluginsOrThemes as $module) {
            /** @var $module ModuleConfig|PluginConfig */
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
                    $releaseTimestamp = $this->getTimeStampFromVersion($data->tag_name);
                    $moduleTimestamp = $this->getTimeStampFromVersion($module->info()['version'] ?? '');
                    $canUpdate = $releaseTimestamp > $moduleTimestamp;
                    $discovered = (isset($data->name)) ? $data->name : $dirName;
                    $this->collate[$type][$module::class] = [
                        'name' => $discovered,
                        'folder_name' => $dirName,
                        'version' => $data->tag_name,
                        'discovered_from' => $this->discoveredFrom,
                        'download_url' => (isset($data->assets[0]->browser_download_url)) ? $data->assets[0]->browser_download_url : '',
                        'can_update' => $canUpdate
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
        foreach ($modules as $module) {
            if ($module['can_update'] && !empty($module['download_url'])) {
                $localDriver = new LocalDriver();
                $name = strtolower($module['version']) . '.zip';
                $folderName = $module['folder_name'];
                $sep = DIRECTORY_SEPARATOR;
                $localDriver->createFromURL($module['download_url'], $tempPath, $name, importToDB: false);
                $result = $localDriver->extractFile($tempPath . $sep. "$name", $tempPath, importToDB: false);
                if ($result && $tonicsHelper->fileExists($tempPath . $sep . "$folderName") && $tonicsHelper->fileExists($dirPath . $sep . "$folderName")) {
                    $copyResult = $tonicsHelper->copyFolder($tempPath . $sep . "$folderName", $dirPath . $sep . "$folderName");
                    if (!$copyResult) {
                        $error = "An Error Occurred, Moving Some Files In: '$name'";
                        $this->errorMessage($error);
                        $tonicsHelper->sendMsg($this->getCurrentState(), $error, 'issue');
                    } else {
                        $directory = $dirPath . $sep . "$folderName";
                        $this->reActivate($directory, $folderName);
                    }
                } else {
                    $error = "Failed To Extract: '$name'";
                    $this->errorMessage($error);
                    helper()->sendMsg($this->getCurrentState(), $error, 'issue');
                }
            }
        }
    }

    /**
     * @param string $version
     * @return int|string
     */
    private function getTimeStampFromVersion(string $version): int|string
    {
        $versionTimeStamp = '';
        $versionExploded = explode('.', $version);
        if (isset($versionExploded[1]) && is_numeric($versionExploded[1])) {
            $versionTimeStamp = (int)$versionExploded[1];
        }

        return $versionTimeStamp;
    }

    private function getJSONFromURL(string $url)
    {
        $update_key = AppConfig::getAppUpdateKey();
        // update_key could be used to identify a site in case of premium plugins and or themes
        $curl = curl_init($url."?update_key=$update_key");
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