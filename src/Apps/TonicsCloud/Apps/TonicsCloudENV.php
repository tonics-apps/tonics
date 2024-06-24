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

namespace App\Apps\TonicsCloud\Apps;

use App\Apps\TonicsCloud\Interfaces\CloudAppInterface;
use App\Apps\TonicsCloud\Interfaces\CloudAppSignalInterface;

class TonicsCloudENV extends CloudAppInterface implements CloudAppSignalInterface
{
    /**
     * Data should contain:
     *
     * ```
     * [
     *     'env_path' => '/path/to/.env', // example: /var/www/.env or /var/www/tonics/web/.env, etc
     *     'env_content' => '...' // content of env
     * ]
     * ```
     *
     * @param array $data
     *
     * @return mixed
     */
    public static function createFieldDetails (array $data = []): mixed
    {
        $fieldDetails = <<<'JSON'
[{"field_id":1,"field_parent_id":null,"field_name":"modular_rowcolumn","field_input_name":"app_config_env_recipe","main_field_slug":"app-tonicscloud-app-config-env","field_options":"{\"field_slug\":\"modular_rowcolumn\",\"main_field_slug\":\"app-tonicscloud-app-config-env\",\"field_slug_unique_hash\":\"6yccojv18l40000000000\",\"field_input_name\":\"app_config_env_recipe\"}"},{"field_id":2,"field_parent_id":1,"field_name":"modular_rowcolumnrepeater","field_input_name":"","main_field_slug":"app-tonicscloud-app-config-env","field_options":"{\"field_slug\":\"modular_rowcolumnrepeater\",\"_moreOptions\":{\"inputName\":\"\",\"field_slug_unique_hash\":\"1pox9dubck8w000000000\",\"field_slug\":\"modular_rowcolumnrepeater\",\"field_name\":\"ENV Repeater\",\"depth\":\"0\",\"repeat_button_text\":\"Repeat Recipe\",\"grid_template_col\":\" grid-template-columns: ;\",\"row\":\"1\",\"column\":\"1\",\"_cell_position\":null,\"_can_have_repeater_button\":true},\"main_field_slug\":\"app-tonicscloud-app-config-env\",\"field_slug_unique_hash\":\"1pox9dubck8w000000000\",\"field_input_name\":\"\"}"},{"field_id":3,"field_parent_id":2,"field_name":"modular_fieldselectiondropper","field_input_name":"app_config_env_recipe_selected","main_field_slug":"app-tonicscloud-app-config-env","field_options":"{\"field_slug\":\"modular_fieldselectiondropper\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-env\",\"field_slug_unique_hash\":\"1uiwxw4k63wg000000000\",\"field_input_name\":\"app_config_env_recipe_selected\",\"app_config_env_recipe_selected\":\"app-tonicscloud-env-recipe-manual\"}"},{"field_id":4,"field_parent_id":3,"field_name":"modular_rowcolumn","field_input_name":"","main_field_slug":"app-tonicscloud-env-recipe-manual","field_options":"{\"field_slug\":\"modular_rowcolumn\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-env-recipe-manual\",\"field_slug_unique_hash\":\"id7xhplib5s000000000\",\"field_input_name\":\"\"}"},{"field_id":5,"field_parent_id":4,"field_name":"input_text","field_input_name":"env_path","main_field_slug":"app-tonicscloud-env-recipe-manual","field_options":"{\"field_slug\":\"input_text\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-env-recipe-manual\",\"field_slug_unique_hash\":\"43iqrc0f3re0000000000\",\"field_input_name\":\"env_path\",\"env_path\":\"/var/www/.env\"}"},{"field_id":6,"field_parent_id":4,"field_name":"input_text","field_input_name":"env_content","main_field_slug":"app-tonicscloud-env-recipe-manual","field_options":"{\"field_slug\":\"input_text\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-env-recipe-manual\",\"field_slug_unique_hash\":\"3tenqnk5guu0000000000\",\"field_input_name\":\"env_content\",\"env_content\":\"\"}"}]
JSON;
        $fields = json_decode($fieldDetails);
        return json_encode(self::updateFieldOptions($fields, $data));
    }

    /**
     * @inheritDoc
     * @throws \Exception
     * @throws \Throwable
     */
    public function updateSettings (): void
    {
        foreach ($this->getPostPrepareForFlight() as $env) {
            if (isset($env->env_path) && isset($env->env_content)) {
                $this->createOrReplaceFile($env->env_path, $env->env_content);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function install (): mixed
    {
        // TODO: Implement install() method.
    }

    /**
     * @inheritDoc
     */
    public function uninstall (): mixed
    {
        // TODO: Implement uninstall() method.
    }

    /**
     * @throws \Exception
     */
    public function prepareForFlight (array $data, string $flightType = self::PREPARATION_TYPE_SETTINGS): array
    {
        $settings = [];
        $app = [];
        foreach ($data as $field) {
            if (isset($field->main_field_slug) && isset($field->field_input_name)) {

                $fieldOptions = $this->getFieldOption($field);
                $field->field_options = $fieldOptions;
                $value = $fieldOptions->{$field->field_input_name} ?? null;

                if ($field->field_input_name == 'env_path') {
                    $app = [];
                    $app['env_path'] = $this->replaceContainerGlobalVariables($value);
                }

                if ($field->field_input_name == 'env_content') {
                    $app['env_content'] = $this->replaceContainerGlobalVariables($value);
                    $settings[] = $app;
                }
            }
        }

        return $settings;
    }

    /**
     * @throws \Exception
     */
    public function reload (): bool
    {
        return true;
    }

    /**
     * @throws \Exception
     */
    public function stop (): bool
    {
        return true;
    }

    /**
     * @throws \Exception
     */
    public function start (): bool
    {
        return true;
    }

    /**
     * @throws \Exception
     */
    public function isStatus (string $statusString): bool
    {
        return true;
    }

    /**
     * You can currently swap the following if you want to:
     *
     * ```
     *[
     *  'APP_URL' => '...',
     *  'APP_KEY' => '...',
     *  'INSTALL_KEY' => '...',
     *  'SITE_KEY' => '...',
     * ]
     * ```
     *
     * @param array $settings
     *
     * @return string
     */
    public static function TonicsENV (array $settings = []): string
    {
        $appURL = (!empty($settings['APP_URL'])) ? $settings['APP_URL'] : "https://[[ACME_DOMAIN]]";
        $installKey = (!empty($settings['INSTALL_KEY'])) ? $settings['INSTALL_KEY'] : "[[RAND_STRING_RENDER]]";
        $siteKey = (!empty($settings['SITE_KEY'])) ? $settings['SITE_KEY'] : "[[RAND_STRING]]";
        $appKey = (!empty($settings['APP_KEY'])) ? $settings['APP_KEY'] : "xxx";

        return <<<ENV
APP_NAME=Tonics
APP_ENV=production
APP_URL_PORT=443
APP_URL=$appURL
APP_TIME_ZONE=Africa/Lagos
APP_LANGUAGE=0
APP_LOG_404=1
APP_PAGINATION_MAX_LIMIT=20
APP_STARTUP_CLI_FORK_LIMIT=1

JOB_TRANSPORTER=DATABASE
SCHEDULE_TRANSPORTER=DATABASE

INSTALL_KEY=$installKey
APP_KEY=$appKey
APP_POST_ENDPOINT=https://tonics.app/api/app_store
SITE_KEY=$siteKey

MAINTENANCE_MODE=0
AUTO_UPDATE_MODULES=1
AUTO_UPDATE_APPS=0

ACTIVATE_EVENT_STREAM_MESSAGE=1

DB_CONNECTION=mysql
DB_HOST=[[DB_HOST]]
DB_PORT=3306
DB_DATABASE=[[DB_DATABASE]]
DB_USERNAME=[[DB_USER]]
DB_PASSWORD=[[DB_PASS]]
DB_CHARSET=utf8mb4
DB_ENGINE=InnoDB
DB_PREFIX=tonics_

MAIL_MAILER=smtp
MAIL_HOST=mail.domain.com
MAIL_PORT=587
MAIL_USERNAME=user
MAIL_PASSWORD=password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=user@mail.domain.com
MAIL_REPLY_TO=user@mail.domain.com

DROPBOX_KEY=xxx
ENV;

    }

    /**
     * @return string
     */
    public static function WordPressENV (): string
    {
        return <<<'ENV'
<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', '[[DB_DATABASE]]' );

/** Database username */
define( 'DB_USER', '[[DB_USER]]' );

/** Database password */
define( 'DB_PASSWORD', '[[DB_PASS]]' );

/** Database hostname */
define( 'DB_HOST', '[[DB_HOST]]' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '[[RAND_STRING]]' );
define( 'SECURE_AUTH_KEY',  '[[RAND_STRING]]' );
define( 'LOGGED_IN_KEY',    '[[RAND_STRING]]' );
define( 'NONCE_KEY',        '[[RAND_STRING]]' );
define( 'AUTH_SALT',        '[[RAND_STRING]]' );
define( 'SECURE_AUTH_SALT', '[[RAND_STRING]]' );
define( 'LOGGED_IN_SALT',   '[[RAND_STRING]]' );
define( 'NONCE_SALT',       '[[RAND_STRING]]' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

$_SERVER['HTTPS']='on';

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
ENV;

    }
}