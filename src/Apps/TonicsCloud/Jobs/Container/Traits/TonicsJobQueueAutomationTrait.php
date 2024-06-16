<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Apps\TonicsCloud\Jobs\Container\Traits;

use App\Apps\TonicsCloud\Apps\TonicsCloudACME;
use App\Apps\TonicsCloud\Apps\TonicsCloudENV;
use App\Apps\TonicsCloud\Apps\TonicsCloudMariaDB;
use App\Apps\TonicsCloud\Apps\TonicsCloudNginx;
use App\Apps\TonicsCloud\Apps\TonicsCloudPHP;
use App\Apps\TonicsCloud\Apps\TonicsCloudScript;
use App\Apps\TonicsCloud\Apps\TonicsCloudUnZip;
use App\Apps\TonicsCloud\Services\ContainerService;

trait TonicsJobQueueAutomationTrait
{
    # APPS SETTINGS
    /**
     * It uses the following fieldSettings by default:
     * ```
     * [
     * 'serverName' => '[[ACME_DOMAIN]]',
     * 'root'       => '[[ROOT]]',
     * 'ssl'        => false,
     * ]
     * ```
     */
    const APP_SETTING_SIMPLE_NGINX_HTTP_MODE = 'SimpleNginxHTTPMode';
    /**
     * It uses the following fieldSettings by default:
     * ```
     * [
     * 'serverName' => '[[ACME_DOMAIN]]',
     * 'root'       => '[[ROOT]]',
     * 'ssl'        => true,
     * ]
     * ```
     */
    const APP_SETTING_SIMPLE_NGINX_HTTPS_MODE = 'SimpleNginxHTTPSMode';
    /**
     * It uses the following fieldSettings by default:
     * ```
     * [
     * 'serverName' => '[[ACME_DOMAIN]]',
     * 'root'       => '[[ROOT]]',
     * 'ssl'        => false,
     * ]
     * ```
     */
    const APP_SETTING_TONICS_NGINX_HTTP_MODE = 'TonicsNginxHTTPMode';
    /**
     * It uses the following fieldSettings by default:
     * ```
     * [
     * 'serverName' => '[[ACME_DOMAIN]]',
     * 'root'       => '[[ROOT]]',
     * 'ssl'        => true,
     * ]
     * ```
     */
    const APP_SETTING_TONICS_NGINX_HTTPS_MODE = 'TonicsNginxHTTPSMode';
    /**
     * It uses the following fieldSettings by default:
     * ```
     * [
     * 'serverName' => '[[ACME_DOMAIN]]',
     * 'root'       => '[[ROOT]]',
     * 'ssl'        => false,
     * ]
     * ```
     */
    const APP_SETTING_WORDPRESS_NGINX_HTTP_MODE = 'WordPressNginxHTTPMode';
    /**
     * It uses the following fieldSettings by default:
     * ```
     * [
     * 'serverName' => '[[ACME_DOMAIN]]',
     * 'root'       => '[[ROOT]]',
     * 'ssl'        => true,
     * ]
     * ```
     */
    const APP_SETTING_WORDPRESS_NGINX_HTTPS_MODE = 'WordPressNginxHTTPSMode';
    /**
     * It uses the following fieldSettings by default:
     * ```
     * [
     * 'unzip_extractTo'   => '[[ROOT]]',
     * 'unzip_archiveFile' => '[[ARCHIVE_FILE]]',
     * 'unzip_format'      => '',
     * 'unzip_overwrite'   => '1',
     * ]
     * ```
     */
    const APP_SETTING_UNZIP = 'UNZIP';
    /**
     * It uses the following fieldSettings by default:
     * ```
     * [
     * 'db_name'   => '[[DB_DATABASE]]',
     * 'db_user'   => '[[DB_USER]]',
     * 'user_name' => '[[DB_USER]]',
     * 'user_pass' => '[[DB_PASS]]',
     * ]
     * ```
     */
    const APP_SETTING_MARIADB = 'MARIADB';
    /**
     * It uses the following fieldSettings by default:
     * ```
     * [
     * 'acme_email'  => '[[ACME_EMAIL]]',
     * 'acme_mode'   => 'nginx',
     * 'acme_issuer' => 'zerossl',
     * 'acme_sites'  => ['[[ACME_DOMAIN]]'],
     * ]
     * ```
     */
    const APP_SETTING_ACME = 'ACME';
    /**
     * It uses the following fieldSettings by default:
     * ```
     * [
     * 'env_path'    => '[[ROOT]]/.env',
     * 'env_content' => TonicsCloudENV::TonicsENV(),
     * ]
     * ```
     */
    const APP_SETTING_TONICS_ENV = 'TonicsENV';
    /**
     * It uses the following fieldSettings by default:
     * ```
     * [
     * 'env_path'    => '[[ROOT]]/.env',
     * 'env_content' => TonicsCloudENV::TonicsENV(), // with generated APP_KEY
     * ]
     * ```
     */
    const APP_SETTING_TONICS_EXISTING_ENV = 'TonicsExistingENV';
    /**
     * It uses the following fieldSettings by default:
     * ```
     * [
     * 'env_path'    => '[[ROOT]]/wp-config.php',
     * 'env_content' => TonicsCloudENV::WordPressENV(),
     * ]
     * ```
     */
    const APP_SETTING_WORDPRESS_ENV = 'WordPressENV';
    /**
     * It uses the following fieldSettings by default:
     *```
     * [
     * 'content' => TonicsCloudScript::TonicsCloudScript(),
     * ]
     * ```
     */
    const APP_SETTING_TONICS_SCRIPT = 'TonicsScript';
    /**
     * It uses the following fieldSettings by default:
     *```
     * [
     * 'content' => TonicsCloudScript::WordPressScript(),
     * ]
     * ```
     */
    const APP_SETTING_WORDPRESS_SCRIPT = 'WordPressScript';
    /**
     * It uses the following fieldSettings by default:
     * ```
     *  [
     *      'fpm' => TonicsCloudPHP::PHP_FPM(),
     *      'ini' => TonicsCloudPHP::INI_OPTIMIZED(),
     * ]
     *  ```
     */
    const APP_SETTING_PHP = 'PHP_SETTINGS';

    # APP NAME
    const APP_NGINX   = 'Nginx';
    const APP_UNZIP   = 'UnZip';
    const APP_MARIADB = 'MariaDB';
    const APP_ACME    = 'ACME';
    const APP_PHP     = 'PHP';
    const APP_SCRIPT  = 'Script';
    const APP_HARAKA  = 'Haraka';
    const APP_ENV     = 'ENV';

    protected array $appSettingsRegistrar = [];
    protected array $apps                 = [];
    protected       $containerID          = null;

    /**
     * @throws \Exception
     */
    public function constructorSetup (): void
    {
        if ($this->containerID === null) {
            $this->containerID = $this->getContainerID();
        }

        $this->mapAppsByName(ContainerService::getAppsInContainer($this->containerID));
        $this->setAppSettingsRegistrar($this->appSettingsRegistrar());
    }

    /**
     * @return mixed|null
     */
    public function getCurrentContainerID (): mixed
    {
        return $this->containerID;
    }

    /**
     * @param array $appsInContainer
     *
     * @return array
     */
    protected function mapAppsByName (array $appsInContainer): array
    {
        $apps = [];
        foreach ($appsInContainer as $app) {
            $apps[$app->app_name] = $app;
        }
        $this->setApps($apps);
        return $apps;
    }

    /**
     * @param $containerID
     * @param array $callables
     *
     * @return array
     */
    public function processCollationAppToUpdates ($containerID, array $callables = []): array
    {
        $appsToUpdates = [];
        foreach ($callables as $callable) {
            if (is_callable($callable)) {
                $appsToUpdates[] = $callable($containerID);
            }
        }

        return $appsToUpdates;
    }

    /**
     * Example Usage:
     *
     * ```
     * // the app settings value can be an array, it overrides the default field settings of the app settings,
     * // you can just list the AppSettings name, if the array would be empty.
     * $this->>pickAppSettings(
     * [
     *      self::APP_SETTING_SIMPLE_NGINX_HTTP_MODE => [...],
     *      function () { return []; }, // you can also use a callable, this would skip app_settings_key check
     *      fn() => []
     *      ...
     * ]
     * );
     * ```
     *
     * @param array $toPick
     * @param $containerID
     *
     * @return array
     */
    public function pickAppSettings (array $toPick, $containerID): array
    {
        $picked = [];
        foreach ($toPick as $appSettingKey => $fieldSettings) {

            # If callable, call and continue
            if (is_callable($fieldSettings)) {
                $picked[] = $fieldSettings($containerID);
                continue;
            }

            # If $appSettingKey is int, then, there is probably no fieldSettings, let's swap things around
            if (is_int($appSettingKey)) {
                $appSettingKey = $fieldSettings;
                $fieldSettings = [];
            }

            if (!is_array($fieldSettings)) {
                $fieldSettings = [];
            }

            if (isset($this->appSettingsRegistrar[$appSettingKey])) {
                $picked[] = $this->appSettingsRegistrar[$appSettingKey]($containerID, $fieldSettings);
            }
        }

        return $picked;
    }

    public function appSettingsRegistrar (): array
    {
        return [
            self::APP_SETTING_SIMPLE_NGINX_HTTP_MODE     => function ($containerID, array $fieldDetails = []) {
                return $this->NginxMode($containerID, $this->getApps(), TonicsCloudNginx::NginxSimple([
                    ...[
                        'serverName' => '[[ACME_DOMAIN]]',
                        'root'       => '[[ROOT]]',
                        'ssl'        => false,
                    ],
                    ...$fieldDetails,
                ]));
            },
            self::APP_SETTING_SIMPLE_NGINX_HTTPS_MODE    => function ($containerID, array $fieldDetails = []) {
                return $this->NginxMode($containerID, $this->getApps(), TonicsCloudNginx::NginxSimple([
                    ...[
                        'serverName' => '[[ACME_DOMAIN]]',
                        'root'       => '[[ROOT]]',
                        'ssl'        => true,
                    ],
                    ...$fieldDetails,
                ]));
            },
            self::APP_SETTING_TONICS_NGINX_HTTP_MODE     => function ($containerID, array $fieldDetails = []) {
                return $this->NginxMode($containerID, $this->getApps(), TonicsCloudNginx::TonicsNginxSimple([
                    ...[
                        'serverName' => '[[ACME_DOMAIN]]',
                        'root'       => '[[ROOT]]',
                        'ssl'        => false,
                    ],
                    ...$fieldDetails,
                ]));
            },
            self::APP_SETTING_TONICS_NGINX_HTTPS_MODE    => function ($containerID, array $fieldDetails = []) {
                return $this->NginxMode($containerID, $this->getApps(), TonicsCloudNginx::TonicsNginxSimple([
                    ...[
                        'serverName' => '[[ACME_DOMAIN]]',
                        'root'       => '[[ROOT]]',
                        'ssl'        => true,
                    ],
                    ...$fieldDetails,
                ]));
            },
            self::APP_SETTING_WORDPRESS_NGINX_HTTP_MODE  => function ($containerID, array $fieldDetails = []) {
                return $this->NginxMode($containerID, $this->getApps(), TonicsCloudNginx::WordPressNginxSimple([
                    ...[
                        'serverName' => '[[ACME_DOMAIN]]',
                        'root'       => '[[ROOT]]',
                        'ssl'        => false,
                    ],
                    ...$fieldDetails,
                ]));
            },
            self::APP_SETTING_WORDPRESS_NGINX_HTTPS_MODE => function ($containerID, array $fieldDetails = []) {
                return $this->NginxMode($containerID, $this->getApps(), TonicsCloudNginx::WordPressNginxSimple([
                    ...[
                        'serverName' => '[[ACME_DOMAIN]]',
                        'root'       => '[[ROOT]]',
                        'ssl'        => true,
                    ],
                    ...$fieldDetails,
                ]));
            },
            self::APP_SETTING_UNZIP                      => function ($containerID, array $fieldDetails = []) {
                return [
                    'container_id'  => $containerID,
                    'app_id'        => $this->getApps()[self::APP_UNZIP]->app_id,
                    '_fieldDetails' => TonicsCloudUnZip::createFieldDetails([
                        ...[
                            'unzip_extractTo'   => '[[ROOT]]',
                            'unzip_archiveFile' => '[[ARCHIVE_FILE]]',
                            'unzip_format'      => '',
                            'unzip_overwrite'   => '1',
                        ],
                        ...$fieldDetails,
                    ]),
                ];
            },
            self::APP_SETTING_MARIADB                    => function ($containerID, array $fieldDetails = []) {
                return [
                    'container_id'  => $containerID,
                    'app_id'        => $this->getApps()[self::APP_MARIADB]->app_id,
                    '_fieldDetails' => TonicsCloudMariaDB::createFieldDetails([
                        ...[
                            'db_name'   => '[[DB_DATABASE]]',
                            'db_user'   => '[[DB_USER]]',
                            'user_name' => '[[DB_USER]]',
                            'user_pass' => '[[DB_PASS]]',
                        ],
                        ...$fieldDetails,
                    ]),
                ];
            },
            self::APP_SETTING_ACME                       => function ($containerID, array $fieldDetails = []) {
                return [
                    'container_id'  => $containerID,
                    'app_id'        => $this->getApps()[self::APP_ACME]->app_id,
                    '_fieldDetails' => TonicsCloudACME::createFieldDetails([
                        ...[
                            'acme_email'  => '[[ACME_EMAIL]]',
                            'acme_mode'   => 'nginx',
                            'acme_issuer' => 'zerossl',
                            'acme_sites'  => ['[[ACME_DOMAIN]]'],
                        ],
                        ...$fieldDetails,
                    ]),
                ];
            },
            self::APP_SETTING_TONICS_ENV                 => function ($containerID, array $fieldDetails = []) {
                return [
                    'container_id'  => $containerID,
                    'app_id'        => $this->getApps()[self::APP_ENV]->app_id,
                    '_fieldDetails' => TonicsCloudENV::createFieldDetails([
                        ...[
                            'env_path'    => '[[ROOT]]/web/.env',
                            'env_content' => TonicsCloudENV::TonicsENV(
                                [
                                    'INSTALL_KEY' => '[[INSTALL_KEY]]',
                                    'SITE_KEY'    => '[[SITE_KEY]]',
                                ],
                            ),
                        ],
                        ...$fieldDetails,
                    ]),
                ];
            },
            self::APP_SETTING_TONICS_EXISTING_ENV        => function ($containerID, array $fieldDetails = []) {
                return [
                    'container_id'  => $containerID,
                    'app_id'        => $this->getApps()[self::APP_ENV]->app_id,
                    '_fieldDetails' => TonicsCloudENV::createFieldDetails([
                        ...[
                            'env_path'    => '[[ROOT]]/web/.env',
                            'env_content' => TonicsCloudENV::TonicsENV(
                                [
                                    'APP_KEY'     => helper()->randomString(25),
                                    'INSTALL_KEY' => '[[INSTALL_KEY]]',
                                    'SITE_KEY'    => '[[SITE_KEY]]',
                                ],
                            ),
                        ],
                        ...$fieldDetails,
                    ]),
                ];
            },
            self::APP_SETTING_WORDPRESS_ENV              => function ($containerID, array $fieldDetails = []) {
                return [
                    'container_id'  => $containerID,
                    'app_id'        => $this->getApps()[self::APP_ENV]->app_id,
                    '_fieldDetails' => TonicsCloudENV::createFieldDetails([
                        ...[
                            'env_path'    => '[[ROOT]]/wp-config.php',
                            'env_content' => TonicsCloudENV::WordPressENV(),
                        ],
                        ...$fieldDetails,
                    ]),
                ];
            },
            self::APP_SETTING_TONICS_SCRIPT              => function ($containerID, array $fieldDetails = []) {
                return [
                    'container_id'  => $containerID,
                    'app_id'        => $this->getApps()[self::APP_SCRIPT]->app_id,
                    '_fieldDetails' => TonicsCloudScript::createFieldDetails([
                        ...[
                            'content' => TonicsCloudScript::TonicsCloudScript(),
                        ],
                        ...$fieldDetails,
                    ]),
                ];
            },
            self::APP_SETTING_WORDPRESS_SCRIPT           => function ($containerID, array $fieldDetails = []) {
                return [
                    'container_id'  => $containerID,
                    'app_id'        => $this->getApps()[self::APP_SCRIPT]->app_id,
                    '_fieldDetails' => TonicsCloudScript::createFieldDetails([
                        ...[
                            'content' => TonicsCloudScript::WordPressScript(),
                        ],
                        ...$fieldDetails,
                    ]),
                ];
            },
            self::APP_SETTING_PHP                        => function ($containerID, array $fieldDetails = []) {
                return [
                    'container_id'  => $containerID,
                    'app_id'        => $this->getApps()[self::APP_PHP]->app_id,
                    '_fieldDetails' => TonicsCloudPHP::createFieldDetails([
                        ...[
                            'fpm' => TonicsCloudPHP::PHP_FPM(),
                            'ini' => TonicsCloudPHP::INI_OPTIMIZED(),
                        ],
                        ...$fieldDetails,
                    ]),
                ];
            },
        ];
    }

    protected function NginxMode ($containerID, $apps, $config): array
    {
        return [
            'container_id'  => $containerID,
            'app_id'        => $apps['Nginx']->app_id,
            '_fieldDetails' => TonicsCloudNginx::createFieldDetails([
                'config' => $config,
            ]),
        ];
    }

    public function getAppSettingsRegistrar (): array
    {
        return $this->appSettingsRegistrar;
    }

    /**
     * @param array $appSettingsRegistrar
     *
     * @return $this
     */
    public function setAppSettingsRegistrar (array $appSettingsRegistrar): static
    {
        $this->appSettingsRegistrar = $appSettingsRegistrar;
        return $this;
    }

    public function getApps (): array
    {
        return $this->apps;
    }

    public function setApps (array $apps): static
    {
        $this->apps = $apps;
        return $this;
    }
}