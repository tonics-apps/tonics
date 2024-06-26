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
use App\Modules\Field\EventHandlers\Fields\Modular\FieldSelectionDropper;

class TonicsCloudNginx extends CloudAppInterface implements CloudAppSignalInterface
{

    const NGINX_CONFIG_RECIPE_REVERSE_PROXY_SIMPLE = 'app-tonicscloud-nginx-recipe-reverse-proxy-simple';
    const NGINX_TONICS_RECIPE_SIMPLE               = 'app-tonicscloud-nginx-recipe-tonics-simple';
    const NGINX_WORDPRESS_RECIPE_SIMPLE            = 'app-tonicscloud-nginx-recipe-wordpress-simple';
    private static string $backend    = '';
    private string        $phpVersion = '';

    /**
     * Example usage:
     *
     * ```
     * [
     *     'serverName' => 'cloud.tonics.app', // siteName
     *     'proxyPassContainer' => 'tc-UUID', // container to redirect incoming serverName connection to
     *     'ssl' => false, // set to true if you want SSL
     * ];
     * ```
     *
     * @param array $settings
     *
     * @return string
     */
    public static function NginxConfigReverseProxySimple (array $settings): string
    {
        $serverName = $settings['serverName'] ?? '';
        $proxyPassContainer = $settings['proxyPassContainer'] ?? '';
        $ssl = $settings['ssl'] ?? false;

        $config = <<<CONFIG
server {
        listen 80 proxy_protocol;
        listen [::]:80 proxy_protocol;
        server_name $serverName;
        location / {
                proxy_set_header Host \$http_host;
                proxy_set_header X-Real-IP \$remote_addr;
                proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
                proxy_set_header X-Forwarded-Proto \$scheme;
                proxy_pass http://$proxyPassContainer;
}
        real_ip_header proxy_protocol;
        set_real_ip_from 127.0.0.1;
}

CONFIG;
        if ($ssl) {
            $config .= <<<CONFIG
server {
        listen 443 ssl proxy_protocol;
        listen [::]:443 ssl proxy_protocol;
        http2 on;
        ssl_certificate /etc/ssl/{$serverName}_fullchain.cer;
        ssl_certificate_key /etc/ssl/$serverName.key;
        ssl_protocols        TLSv1.3 TLSv1.2 TLSv1.1;
        server_name $serverName;
        location / {
                proxy_set_header Host \$http_host;
                proxy_set_header X-Real-IP \$remote_addr;
                proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
                proxy_set_header X-Forwarded-Proto \$scheme;
                proxy_pass http://$proxyPassContainer;
}
        real_ip_header proxy_protocol;
        set_real_ip_from 127.0.0.1;
}

CONFIG;
        }

        return $config;
    }

    /**
     *  Example usage:
     *
     *  ```
     *  [
     *      'serverName' => 'cloud.tonics.app', // siteName
     *      'root' => '/var/www',
     *      'ssl' => false, // set to true if you want SSL
     *  ];
     *  ```
     *
     * @param $settings
     *
     * @return string
     */
    public static function NginxSimple ($settings): string
    {
        $serverName = $settings['serverName'] ?? '';
        $root = $settings['root'] ?? '/var/www/[[ACME_DOMAIN]]';
        $ssl = $settings['ssl'];

        $config = <<<CONFIG
server {
    listen 80;
    listen [::]:80;
    server_name $serverName;

    root $root;
    index index.html;

    location / {
        try_files \$uri \$uri/ =404;
    }
}

CONFIG;
        if ($ssl) {
            $config .= <<<CONFIG
server {
    listen 443 ssl;
    server_name $serverName;

    http2 on;
    ssl_certificate /etc/ssl/{$serverName}_fullchain.cer;
    ssl_certificate_key /etc/ssl/$serverName.key;
    ssl_protocols        TLSv1.3 TLSv1.2 TLSv1.1;

    location / {
        root $root;
        index index.html;
    }

    # Additional HTTPS configurations if needed
}

CONFIG;
        }

        return $config;

    }

    /**
     * Data should contain:
     *
     * ```
     * [
     *     'config' => '...' // content of nginx config
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
[{"field_id":1,"field_parent_id":null,"field_name":"modular_rowcolumn","field_input_name":"app_config_nginx_recipe","main_field_slug":"app-tonicscloud-app-config-nginx","field_options":"{\"field_slug\":\"modular_rowcolumn\",\"main_field_slug\":\"app-tonicscloud-app-config-nginx\",\"field_slug_unique_hash\":\"5l2r8ntok740000000000\",\"field_input_name\":\"app_config_nginx_recipe\"}"},{"field_id":2,"field_parent_id":1,"field_name":"modular_rowcolumnrepeater","field_input_name":"","main_field_slug":"app-tonicscloud-app-config-nginx","field_options":"{\"field_slug\":\"modular_rowcolumnrepeater\",\"_moreOptions\":{\"inputName\":\"\",\"field_slug_unique_hash\":\"uwtvd1nvteo000000000\",\"field_slug\":\"modular_rowcolumnrepeater\",\"field_name\":\"Recipe Repeater\",\"depth\":\"0\",\"repeat_button_text\":\"Repeat Recipe\",\"grid_template_col\":\" grid-template-columns: ;\",\"row\":\"1\",\"column\":\"1\",\"_cell_position\":null,\"_can_have_repeater_button\":true},\"main_field_slug\":\"app-tonicscloud-app-config-nginx\",\"field_slug_unique_hash\":\"uwtvd1nvteo000000000\",\"field_input_name\":\"\"}"},{"field_id":3,"field_parent_id":2,"field_name":"modular_fieldselectiondropper","field_input_name":"app_config_nginx_recipe_selected","main_field_slug":"app-tonicscloud-app-config-nginx","field_options":"{\"field_slug\":\"modular_fieldselectiondropper\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-nginx\",\"field_slug_unique_hash\":\"5z0rte6xjow0000000000\",\"field_input_name\":\"app_config_nginx_recipe_selected\",\"app_config_nginx_recipe_selected\":\"app-tonicscloud-app-config-default\"}"},{"field_id":4,"field_parent_id":3,"field_name":"input_text","field_input_name":"config","main_field_slug":"app-tonicscloud-app-config-default","field_options":"{\"field_slug\":\"input_text\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-default\",\"field_slug_unique_hash\":\"7f9nl332bbo0000000000\",\"field_input_name\":\"config\",\"config\":\"nginx content...\"}"}]
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
        $config = $this->getPostPrepareForFlight()->config;
        if ($this->getPostPrepareForFlight()->backend === 'php') {
            $config = helper()->replacePlaceHolders($config, [
                "[[PHP_VERSION]]" => $this->phpVersion(),
            ]);
        }
        $this->createOrReplaceFile("/etc/nginx/conf.d/default.conf", $config);
    }

    /**
     * @inheritDoc
     */
    public function install () {}

    /**
     * @inheritDoc
     */
    public function uninstall () {}

    public function prepareForFlight (array $data, string $flightType = self::PREPARATION_TYPE_SETTINGS): ?array
    {
        if ($flightType === self::PREPARATION_TYPE_SETTINGS) {
            return $this->extractNginxConfig($data);
        }

        return null;
    }

    /**
     *  Example usage:
     *
     *  ```
     *  [
     *      'serverName' => 'cloud.tonics.app', // siteName
     *      'root' => '/var/www', // container to redirect incoming serverName connection to
     *      'ssl' => false, // set to true if you want SSL
     *  ];
     *  ```
     *
     * @param $settings
     *
     * @return string
     */
    public static function TonicsNginxSimple ($settings): string
    {
        self::$backend = 'php';
        $serverName = $settings['serverName'] ?? '';
        $root = $settings['root'] ?? '/var/www';
        $ssl = $settings['ssl'] ?? false;

        $root = rtrim($root, '/'); // e.g /var/www
        if ($ssl === false) {
            $config = <<<CONFIG
server {

        listen 80;
CONFIG;
        } else {
            $config = <<<CONFIG
server {
    listen 80;
    server_name $serverName www.$serverName;
    return 301 https://$serverName\$request_uri;
}

server {
        listen 443 ssl http2;

        # ssl on;
        ssl_certificate /etc/ssl/{$serverName}_fullchain.cer;
        ssl_certificate_key /etc/ssl/$serverName.key;
        ssl_protocols        TLSv1.3 TLSv1.2 TLSv1.1;
CONFIG;
        }

        $config .= <<<CONFIG

        root $root/web/public;
        client_max_body_size 900M;
        index index.php;
        # Set server_name directive with the hostname
        server_name $serverName;

        # DISALLOW PHP EXECUTION IN THE UPLOAD FOLDER
        location ~ /uploads/.*\.php$ {
                return 403;
        }

        # DISALLOW PHP EXECUTION IN MODULES PATH
        location ~ /modules_file_path_987654321/.*\.php$ {
                return 403;
        }

        # DISALLOW PHP EXECUTION IN APP PATH
        location ~ /apps_file_path_987654321/.*\.php$ {
                return 403;
        }

        location / {
                try_files \$uri \$uri/ /index.php?\$query_string;
        }

        # pass PHP scripts to FastCGI server
        location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_param PHP_VALUE "upload_max_filesize=5G \n post_max_size=2G \n output_buffering=off";
                fastcgi_read_timeout 30000000;
                proxy_ssl_verify off;
                fastcgi_pass unix:/var/run/php/php[[PHP_VERSION]]-fpm.sock;
        }
        location ~ /\.ht {
                deny all;
        }

        # NGINX X-Accel Download Redirection Settings
        location /download_file_path_987654321 {
           internal;
            alias $root/private;
        }

        # NGINX X-Accel For Modules
        location /modules_file_path_987654321 {
             internal;
             alias $root/web/src/Modules;
        }

        # NGINX X-Accel For Apps
        location /apps_file_path_987654321 {
             internal;
             alias $root/web/src/Apps;
        }
}
CONFIG;

        return $config;

    }

    /**
     *  Example usage:
     *
     *  ```
     *  [
     *      'serverName' => 'cloud.tonics.app', // siteName
     *      'root' => '/var/www', // container to redirect incoming serverName connection to
     *      'ssl' => false, // set to true if you want SSL
     *  ];
     *  ```
     *
     * @param $settings
     *
     * @return string
     */
    public static function WordPressNginxSimple ($settings): string
    {
        self::$backend = 'php';
        $serverName = $settings['serverName'] ?? '';
        $root = $settings['root'] ?? '/var/www';
        $ssl = $settings['ssl'] ?? false;

        $root = rtrim($root, '/'); // e.g /var/www
        if ($ssl === false) {
            $config = <<<CONFIG
server {

        listen 80;
CONFIG;
        } else {
            $config = <<<CONFIG
server {
    listen 80;
    server_name $serverName www.$serverName;
    return 301 https://$serverName\$request_uri;
}

server {
        listen 443 ssl http2;

        # ssl on;
        ssl_certificate /etc/ssl/{$serverName}_fullchain.cer;
        ssl_certificate_key /etc/ssl/$serverName.key;
        ssl_protocols        TLSv1.3 TLSv1.2 TLSv1.1;
CONFIG;
        }

        $config .= <<<CONFIG

        root $root;
        client_max_body_size 900M;
        index index.php;
        # Set server_name directive with the hostname
        server_name $serverName;

        location / {
                         try_files \$uri \$uri/ =404;
            }

            location ~ \.php$ {
                         include snippets/fastcgi-php.conf;
                         fastcgi_pass unix:/run/php/php[[PHP_VERSION]]-fpm.sock;
            }
            
            location ~ /\.ht {
                         deny all;
            }

            location = /favicon.ico {
                         log_not_found off;
                         access_log off;
            }

            location = /robots.txt {
                         allow all;
                         log_not_found off;
                         access_log off;
           }
       
            location ~* \.(js|css|png|jpg|jpeg|gif|ico)$ {
                         expires max;
                         log_not_found off;
           }
}
CONFIG;

        return $config;

    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    private function phpVersion (): string
    {
        if (empty($this->phpVersion)) {
            $this->runCommand(function ($out) { $this->phpVersion = $out; }, function ($err) { $this->phpVersion = ''; }, "bash", "-c", <<<EOF
php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;'
EOF,
            );
        }
        return trim($this->phpVersion);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function isStatus (string $statusString): bool
    {
        $status = '';
        if (CloudAppSignalInterface::STATUS_RUNNING === $statusString) {
            $this->runCommand(function ($out) use (&$status) { $status = $out; }, null, "bash", "-c", "systemctl show nginx -p ActiveState");
            return str_starts_with($status, 'ActiveState=active');
        }

        if (CloudAppSignalInterface::STATUS_STOPPED === $statusString) {
            $this->runCommand(function ($out) use (&$status) { $status = $out; }, null, "bash", "-c", "systemctl show nginx -p ActiveState");
            return str_starts_with($status, 'ActiveState=inactive');
        }

        return false;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function start (): bool
    {
        return $this->signalSystemDService('nginx', self::SystemDSignalStart);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function reload (): bool
    {
        return $this->runCommand(null, null, "bash", "-c", "nginx -t && nginx -s reload");
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function stop (): bool
    {
        return $this->signalSystemDService('nginx', self::SystemDSignalStop);
    }

    /**
     * @param $fields
     *
     * @return array
     */
    private function extractNginxConfig ($fields): array
    {
        $config = '';
        $serverName = '';
        $proxyPassContainer = '';
        $root = '';
        $ssl = false;
        foreach ($fields as $field) {
            if (isset($field->main_field_slug) && $field->field_name !== FieldSelectionDropper::FieldSlug) {
                $fieldOptions = $this->getFieldOption($field);
                $value = $fieldOptions->{$field->field_input_name} ?? null;

                if ($value === null) {
                    continue;
                }

                if ($field->field_input_name === 'root') {
                    $root = $value;
                    continue;
                }

                if ($field->field_input_name === 'server_name') {
                    $serverName = $value;
                    if (!isset($this->dispatchNginxConfig()[$field->main_field_slug])) {
                        $config .= "        server_name $serverName;\n";
                    }
                    continue;
                }

                if ($field->field_input_name === 'proxy_pass_container') {
                    $proxyPassContainer = $value;
                    if (!isset($this->dispatchNginxConfig()[$field->main_field_slug])) {
                        $config .= "                proxy_pass http://$proxyPassContainer;\n";
                    }
                    continue;
                }

                if ($field->field_input_name === 'server_ssl') {
                    $ssl = $value == '1';
                }

                if (isset($this->dispatchNginxConfig()[$field->main_field_slug])) {
                    $staticFunc = $this->dispatchNginxConfig()[$field->main_field_slug];
                    $settings = [
                        'proxyPassContainer' => $proxyPassContainer,
                        'root'               => $root,
                        'serverName'         => $serverName,
                        'ssl'                => $ssl,
                    ];
                    $config .= self::$staticFunc($settings);
                    continue;
                }
                $config .= $value;
            }
        }

        return [
            'config'  => $this->replaceContainerGlobalVariables($config),
            'backend' => self::$backend,
        ];
    }

    private function dispatchNginxConfig (): array
    {
        return [
            self::NGINX_CONFIG_RECIPE_REVERSE_PROXY_SIMPLE => 'NginxConfigReverseProxySimple',
            self::NGINX_TONICS_RECIPE_SIMPLE               => 'TonicsNginxSimple',
        ];
    }


}