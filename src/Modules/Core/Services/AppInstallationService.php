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

namespace App\Modules\Core\Services;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Library\AbstractService;
use App\Modules\Media\FileManager\LocalDriver;
use Devsrealm\TonicsContainer\Container;
use Devsrealm\TonicsHelpers\TonicsHelpers;
use Exception;

class AppInstallationService extends AbstractService
{
    private array          $settings = [];
    private array|string   $appSlug  = '';
    private ?TonicsHelpers $helpers;
    private ?Container     $container;

    /**
     * Settings should contain at least:
     *
     * ```
     * [
     *     'PHP_VERSION' => '8.2', or '8.3', etc.
     *     'SITE_KEY' => '...', // the site key
     *     'APP_SLUG' => '...', // or can be an array ['...', '...']
     *     'APP_POST_ENDPOINT' => '...', // endpoint of tonics app store
     *     'TonicsHelper' => 'new TonicsHelper()', // inject another helper object, defaults to helper()
     *     'Container' => 'new Container()', // inject another container object, defaults to container()
     * ]
     * ```
     *
     * @param array $settings
     *
     * @throws \Exception
     */
    public function __construct (array $settings = [])
    {
        $this->settings['PHP_VERSION'] = $settings['PHP_VERSION'] ?? PHP_VERSION;
        $this->settings['SITE_KEY'] = $settings['SITE_KEY'] ?? AppConfig::getAppSiteKey();
        $this->settings['APP_SLUG'] = $settings['APP_SLUG'] ?? '';
        $this->settings['APP_POST_ENDPOINT'] = $settings['APP_POST_ENDPOINT'] ?? AppConfig::getAppPostEndpoint();
        $this->settings['TonicsHelper'] = $settings['TonicsHelper'] ?? helper();
        $this->settings['Container'] = $settings['Container'] ?? container();

        $this->helpers = $this->settings['TonicsHelper'];
        $this->container = $this->settings['Container'];
        $this->appSlug = $this->settings['APP_SLUG'];
    }

    /**
     * The url can be `/latest` or `/register`, etc., it should never be a full url, it should be part of the endpoint.
     *
     * For the postField, you can just call any of the method: `$this->getPostFieldForLatestAppRelease()` or `$this->getPostFieldForSiteAppRegistration()`, or just create your own
     *
     * @param string $url
     * @param array $postField
     *
     * @return mixed
     * @throws \Exception
     */
    public function getJSONFromURL (string $url, array $postField): mixed
    {
        $url = $this->settings['APP_POST_ENDPOINT'] . $url;
        $curl = curl_init($url);

        $options = [
            CURLOPT_POST                 => true,
            CURLOPT_POSTFIELDS           => json_encode($postField),
            CURLOPT_SSL_VERIFYHOST       => 2,
            CURLOPT_PROXY_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYSTATUS     => true,
            CURLOPT_DNS_CACHE_TIMEOUT    => false,
            CURLOPT_DNS_USE_GLOBAL_CACHE => false,
            CURLOPT_FOLLOWLOCATION       => false,
            CURLOPT_RETURNTRANSFER       => true,
            CURLOPT_HTTPHEADER           => ['Accept: application/json'],
            CURLOPT_MAXREDIRS            => 1,
        ];

        # For Development Purpose
        if (!AppConfig::isProduction()) {
            $options[CURLOPT_SSL_VERIFYSTATUS] = false;
            $options[CURLOPT_PROXY_SSL_VERIFYPEER] = false;
            $options[CURLOPT_SSL_VERIFYHOST] = false;
        }

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, flags: JSON_PRETTY_PRINT);
    }

    /**
     * Ensure you populate the AppSlugs either from the constructor ir the AppSlug setter
     * @return bool
     * @throws Exception
     */
    public function registerApps (): bool
    {
        $json = $this->getJSONFromURL('/register', $this->getPostFieldForSiteAppRegistration());
        $success = isset($json->status) && $json->status === 200;
        if (!$success) {
            $this->setFails(true)->setErrors([$json->message ?? '']);
        }
        return $success;
    }

    /**
     * @param string $moduleTimestamp
     *
     * @return array
     * @throws Exception
     */
    public function getUpdateDiscoveryData (string $moduleTimestamp): array
    {
        $discoveredFrom = 'browser';
        if ($this->helpers->isCLI()) {
            $discoveredFrom = 'console';
        }
        $updateData = [];
        $json = $this->getJSONFromURL('/latest', $this->getPostFieldForLatestAppRelease());
        if (isset($json->data)) {
            $data = $json->data;
            if (empty($data)) {
                return [];
            }
            $updateData = [
                'name'              => $data->name,
                'folder_name'       => $data->name,
                'hash'              => $data->hash,
                'version'           => $data->tag_name,
                'discovered_from'   => $discoveredFrom,
                'download_url'      => $data->browser_download_url,
                'assets'            => json_decode(json_encode($data->assets), true),
                'can_update'        => $data->release_timestamp > $moduleTimestamp,
                'module_timestamp'  => $moduleTimestamp,
                'release_timestamp' => $data->release_timestamp,
                'last_checked'      => $this->helpers->date(),
            ];
        }

        return $updateData;
    }

    /**
     *
     * For the settings param, it can contain the below, note that all the below have defaults (it is a good idea to set the AppType though) as such you can just leave it as is:
     *
     * ```
     * [
     *      // 1 for modules and 2 for Apps, (2 is by default), it validates the type
     *      'AppType' => 1 or 2,
     *      'AppPath' => '...', // where the app should be uploaded to, default to: AppConfig::getAppsPath()
     *      'ModulePath' => '...', // for module, where module should be uploaded to, default to: AppConfig::getModulesPath()
     *      // it defaults to either TempPathForModules or TempPathForApps, override it here
     *      'TempPath' => '/path/to/temp/folder',
     *      // folder name to use, where we place the app unto, default to generating random name
     *      'TempPathFolderName' => '...',
     *      // it would use this path and skip downloading from the URL
     *      'DownloadURLOverride' => '/path/to/app/zip',
     *      'ForceSigning' => true, // false to turn of, true by default
     *      'Signature' => '...', // The signed signature to verify the authenticity of the App
     * ]
     * ```
     *
     * @param string $downloadURL
     * @param array $settings
     *
     * @return void
     * @throws \Throwable
     */
    public function uploadApp (string $downloadURL, array $settings = []): void
    {
        $localDriver = new LocalDriver($this->helpers);
        $appType = $settings['AppType'] ?? 2; // defaults to App
        if ($appType > 2 || $appType <= 0) {
            $this->setFails(true)->setErrors(["App Type Should Either Be 1 for Module or 2 for Apps"]);
            return;
        }

        $signing = $settings['ForceSigning'] ?? true;
        $isModule = $appType === 1;
        $isApp = $appType === 2;

        $tempPath = $settings['TempPath'] ?? (($isModule) ? DriveConfig::getTempPathForModules() : DriveConfig::getTempPathForApps());
        $name = $settings['TempPathFolderName'] ?? $this->helpers->randomString(15);
        $zipName = $name . '.zip';
        $extractToTemp = $tempPath . DIRECTORY_SEPARATOR . $name;
        $extractToTempZip = $tempPath . DIRECTORY_SEPARATOR . $zipName;

        # Edge case, forceDelete temp zip path if it already exists
        if ($this->helpers->fileExists($extractToTempZip)) {
            $this->helpers->forceDeleteFile($extractToTempZip);
        }

        # For Temp Extract
        if ($this->helpers->fileExists($extractToTemp)) {
            $this->helpers->forceDeleteDirectory($extractToTemp);
        }

        $cleanUpTempDir = function () use ($extractToTempZip, $extractToTemp) {
            if ($this->helpers->fileExists($extractToTemp)) {
                $this->helpers->forceDeleteDirectory($extractToTemp);
            }
            if ($this->helpers->fileExists($extractToTempZip)) {
                $this->helpers->forceDeleteFile($extractToTempZip);
            }
        };

        try {

            if (!isset($settings['DownloadURLOverride'])) {
                if (!$localDriver->createFromURL($downloadURL, $tempPath, $zipName, false)) {
                    throw new Exception("An Error Occurred Downloading $downloadURL");
                }
            } else {
                $extractToTempZip = $settings['DownloadURLOverride'];
            }

            # Let's create the directory structure of our temp path in case it is needed
            $result = $this->helpers->createDirectoryRecursive($extractToTemp);
            if (!$result) {
                throw new Exception("An Error Occurred Creating Directory Structure For Temp. Path");
            }

            $extractedFileResult = $localDriver->extractFile($extractToTempZip, $extractToTemp, 'zip', false);
            if (!$extractedFileResult) {
                throw new Exception("Failed To Extract File");
            }

            $dir = array_filter(glob($extractToTemp . DIRECTORY_SEPARATOR . '*'), 'is_dir');
            # It should only contain one folder which should be the name of the app, so, we return an error fam
            $countDir = count($dir);
            if (count($dir) !== 1) {
                throw new Exception("The Extracted Should Contain Only One Folder Which Should be The App Name, Instead We Had ($countDir) Directory");
            }

            # Validate Activator
            $appTempPathDir = $dir[0];
            $activatorFile = $this->helpers->findFilesWithExtension(['php'], $appTempPathDir) ?? [];
            $getFileContent = @file_get_contents($activatorFile[0] ?? '');
            if (count($activatorFile) !== 1 || $getFileContent === false) {
                throw new Exception("Should only have one PHP File at the Root which should be the PHP activator");
            }

            $class = $this->helpers->getFullClassName($getFileContent);
            if ($isModule) {
                if (!AppConfig::isInternalModuleNameSpace($class)) {
                    throw new Exception("The AppType is Module and Should be a Valid Module Namespace");
                }
                $directoryToMoveDestination = $settings['ModulePath'] ?? AppConfig::getModulesPath();
            } elseif ($isApp) {
                if (!AppConfig::isAppNameSpace($class)) {
                    throw new Exception("The AppType is App and Should be a Valid App Namespace");
                }
                $directoryToMoveDestination = $settings['AppPath'] ?? AppConfig::getAppsPath();
            } else {
                throw new Exception("The AppType is Invalid");
            }

            $infoArray = $this->extractInfoArrayToArray($getFileContent);
            # If signing is true, we check signing
            if ($signing === true) {
                # Grab the app signature
                $signature = $settings['Signature'] ?? '';

                # Construct Signatures
                $signatures = [];
                foreach ($this->getPublicKeys() as $publicKey) {
                    $signatures[] = [
                        'key' => $publicKey,
                        'sig' => $signature,
                    ];
                }

                if (!$this->helpers->signingVerifyFileSignature([
                    'file'       => $extractToTempZip,
                    'signatures' => $signatures,
                ])) {
                    throw new Exception("Can't Verify The Authenticity of The App");
                }

            }

            $appName = $infoArray['name'] ?? '';
            if (empty($appName)) {
                throw new Exception("There is No Valid AppName in App Activator");
            }

            $appModulePathFolder = $directoryToMoveDestination . DIRECTORY_SEPARATOR . $appName;
            # If there is .installed in the app path, drop it in the tempPath, if it fails, then user might
            # want to re-install the app
            if ($this->helpers->fileExists($appModulePathFolder . DIRECTORY_SEPARATOR . '.installed')) {
                @file_put_contents($appTempPathDir . DIRECTORY_SEPARATOR . '.installed', '');
            }

            # Time For Copying To The Actual Directory, First, Lets Backup The Original if There is one
            $backupDir = $tempPath . DIRECTORY_SEPARATOR . $this->helpers->randomString(10);

            if ($this->helpers->fileExists($appModulePathFolder)) {
                # Let's create the directory structure of our backup temp path
                $result = $this->helpers->createDirectoryRecursive($backupDir);
                if (!$result) {
                    throw new Exception("An Error Occurred Creating Directory Structure For Backup Temp. Path");
                }

                $renamedResult = @rename($appModulePathFolder, $backupDir);
                if (!$renamedResult) {
                    throw new Exception("Failed To Backup The Existing App, Afraid I Can't Continue :(");
                }
            }

            $renamedResult = @rename($appTempPathDir, $appModulePathFolder);
            if ($renamedResult) {
                # Should Only Delete The Backup Dirs if renamed is successful
                $this->helpers->forceDeleteDirectory($backupDir);
                $this->setFails(false)->setMessage("[$appName] App Successfully Uploaded");
            } else {
                # Restore Deleted App Folder, There is no error checking here, as my hands are up at this point
                $this->helpers->forceDeleteDirectory($appModulePathFolder);
                rename($backupDir, $appModulePathFolder);
                $this->helpers->forceDeleteDirectory($backupDir);
                throw new Exception('An Error Occurred Moving App From Temp To The Actual Directory');
            }
        } catch (\Exception $exception) {
            $this->setFails(true)->setErrors([$exception->getMessage()]);
        } finally {
            # Delete Temps
            $cleanUpTempDir();
        }

    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getPostFieldForLatestAppRelease (): array
    {
        return [
            'store_php_version' => $this->settings['PHP_VERSION'],
            'store_site_key'    => $this->settings['SITE_KEY'],
            'store_app_slug_id' => $this->settings['APP_SLUG'],
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getPostFieldForSiteAppRegistration (): array
    {
        return [
            'store_php_version' => $this->settings['PHP_VERSION'],
            'store_site_key'    => $this->settings['SITE_KEY'],
            'store_app_slugs'   => $this->settings['APP_SLUG'],
        ];
    }

    public function getPublicKeys (): array
    {
        return [
            'WY/nS1Z/HU9YpmJVzI94ryBkxSe+KTRBzFkciUS6a5g',
        ];
    }

    /**
     * Pass the class content, and it would extract the info method into something like so:
     *
     * ```
     *[
     *  "name" => "AppName",
     *  "type" => "Tool",
     *  "version" => "1-O-app.1714604528",
     *  "description" => "This is Tonics404Handler",
     *  "update_discovery_url" => "https://api.github.com/repos/tonics-apps/app-tonics404_handler/releases/latest",
     *  "email" => "name@website.com",
     *  "role" => "Developer"
     * ]
     * ```
     *
     * @param $string
     *
     * @return array
     */
    public function extractInfoArrayToArray ($string): array
    {
        // Extracting key-value pairs using regex
        $pattern = <<<PATTERN
/"([^"]+)"\s*=>\s*("[^"]+"|'[^']+')/
PATTERN;

        preg_match_all($pattern, $string, $matches);
        // Creating the PHP array
        $result = [];
        foreach ($matches[1] as $index => $key) {
            if (!isset($result[$key])) {
                $value = trim($matches[2][$index], "\'\"");
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public function getHelpers (): ?TonicsHelpers
    {
        return $this->helpers;
    }

    public function setHelpers (?TonicsHelpers $helpers): void
    {
        $this->helpers = $helpers;
    }

    public function getContainer (): ?Container
    {
        return $this->container;
    }

    public function setContainer (?Container $container): void
    {
        $this->container = $container;
    }

    public function getAppSlug (): array|string
    {
        return $this->appSlug;
    }

    public function setAppSlug (array|string $appSlug): AppInstallationService
    {
        $this->appSlug = $appSlug;
        $this->settings['APP_SLUG'] = $appSlug;
        return $this;
    }

}