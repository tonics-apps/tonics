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

class TonicsCloudMariaDB extends CloudAppInterface implements CloudAppSignalInterface
{

    /**
     * Data should contain:
     *
     * ```
     * // if you do not want user to be created, remove the `user_name` and `user_pass`
     * [
     *     'db_name' => 'database-name-to-create',
     *     'db_user' => 'database-username-to-create', // should be same as user_name or a user that has been created beforehand
     *     'user_name' => 'database-username-to-create', // should not be root
     *     'user_pass' => 'database-pass-to-create',
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
[{"field_id":1,"field_parent_id":null,"field_name":"modular_rowcolumn","field_input_name":"MySQL_CONFIG_CONTAINER","main_field_slug":"app-tonicscloud-app-config-mysql","field_options":"{\"field_slug\":\"modular_rowcolumn\",\"main_field_slug\":\"app-tonicscloud-app-config-mysql\",\"field_slug_unique_hash\":\"60cd6ovnpho0000000000\",\"field_input_name\":\"MySQL_CONFIG_CONTAINER\",\"MySQL-MariaDB Config_187a115e5bf1116eeccf\":\"on\"}"},{"field_id":2,"field_parent_id":1,"field_name":"modular_rowcolumn","field_input_name":"MySQL_CONFIG_CONTAINER_USER","main_field_slug":"app-tonicscloud-app-config-mysql","field_options":"{\"field_slug\":\"modular_rowcolumn\",\"main_field_slug\":\"app-tonicscloud-app-config-mysql\",\"field_slug_unique_hash\":\"77ltk20c3y80000000000\",\"field_input_name\":\"MySQL_CONFIG_CONTAINER_USER\"}"},{"field_id":3,"field_parent_id":2,"field_name":"modular_rowcolumnrepeater","field_input_name":"","main_field_slug":"app-tonicscloud-app-config-mysql","field_options":"{\"field_slug\":\"modular_rowcolumnrepeater\",\"_moreOptions\":{\"inputName\":\"\",\"field_slug_unique_hash\":\"62v74cv0w9s0000000000\",\"field_slug\":\"modular_rowcolumnrepeater\",\"field_name\":\"User\",\"depth\":\"0\",\"repeat_button_text\":\"Add User\",\"grid_template_col\":\" grid-template-columns: ;\",\"row\":\"1\",\"column\":\"1\",\"_cell_position\":null,\"_can_have_repeater_button\":true},\"main_field_slug\":\"app-tonicscloud-app-config-mysql\",\"field_slug_unique_hash\":\"62v74cv0w9s0000000000\",\"field_input_name\":\"\"}"},{"field_id":4,"field_parent_id":3,"field_name":"input_text","field_input_name":"user_name","main_field_slug":"app-tonicscloud-app-config-mysql","field_options":"{\"field_slug\":\"input_text\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-mysql\",\"field_slug_unique_hash\":\"3sgleofvnro0000000000\",\"field_input_name\":\"user_name\",\"user_name\":\"[[DB_USER]]\"}"},{"field_id":5,"field_parent_id":3,"field_name":"input_text","field_input_name":"user_pass","main_field_slug":"app-tonicscloud-app-config-mysql","field_options":"{\"field_slug\":\"input_text\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-mysql\",\"field_slug_unique_hash\":\"6hrbtfyncwg0000000000\",\"field_input_name\":\"user_pass\",\"user_pass\":\"[[DB_PASS]]\"}"},{"field_id":6,"field_parent_id":3,"field_name":"input_text","field_input_name":"user_remote_address","main_field_slug":"app-tonicscloud-app-config-mysql","field_options":"{\"field_slug\":\"input_text\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-mysql\",\"field_slug_unique_hash\":\"58f8ve2px440000000000\",\"field_input_name\":\"user_remote_address\",\"user_remote_address\":\"\"}"},{"field_id":7,"field_parent_id":1,"field_name":"modular_rowcolumn","field_input_name":"MySQL_CONFIG_CONTAINER_DATABASE","main_field_slug":"app-tonicscloud-app-config-mysql","field_options":"{\"field_slug\":\"modular_rowcolumn\",\"main_field_slug\":\"app-tonicscloud-app-config-mysql\",\"field_slug_unique_hash\":\"43aaquchw4g0000000000\",\"field_input_name\":\"MySQL_CONFIG_CONTAINER_DATABASE\"}"},{"field_id":8,"field_parent_id":7,"field_name":"modular_rowcolumnrepeater","field_input_name":"","main_field_slug":"app-tonicscloud-app-config-mysql","field_options":"{\"field_slug\":\"modular_rowcolumnrepeater\",\"_moreOptions\":{\"inputName\":\"\",\"field_slug_unique_hash\":\"3nl97lkj1vw0000000000\",\"field_slug\":\"modular_rowcolumnrepeater\",\"field_name\":\"Database\",\"depth\":\"0\",\"repeat_button_text\":\"Add Database\",\"grid_template_col\":\" grid-template-columns: ;\",\"row\":\"1\",\"column\":\"1\",\"_cell_position\":null,\"_can_have_repeater_button\":true},\"main_field_slug\":\"app-tonicscloud-app-config-mysql\",\"field_slug_unique_hash\":\"3nl97lkj1vw0000000000\",\"field_input_name\":\"\"}"},{"field_id":9,"field_parent_id":8,"field_name":"input_text","field_input_name":"db_name","main_field_slug":"app-tonicscloud-app-config-mysql","field_options":"{\"field_slug\":\"input_text\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-mysql\",\"field_slug_unique_hash\":\"51jqzprktyw0000000000\",\"field_input_name\":\"db_name\",\"db_name\":\"[[DB_DATABASE]]\"}"},{"field_id":10,"field_parent_id":8,"field_name":"input_text","field_input_name":"db_user","main_field_slug":"app-tonicscloud-app-config-mysql","field_options":"{\"field_slug\":\"input_text\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-mysql\",\"field_slug_unique_hash\":\"6en6hjp8iy00000000000\",\"field_input_name\":\"db_user\",\"db_user\":\"[[DB_USER]]\"}"},{"field_id":11,"field_parent_id":8,"field_name":"input_text","field_input_name":"db_user_host","main_field_slug":"app-tonicscloud-app-config-mysql","field_options":"{\"field_slug\":\"input_text\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-mysql\",\"field_slug_unique_hash\":\"5c23jpv5d6c0000000000\",\"field_input_name\":\"db_user_host\",\"db_user_host\":\"\"}"},{"field_id":12,"field_parent_id":1,"field_name":"input_text","field_input_name":"config","main_field_slug":"app-tonicscloud-app-config-mysql","field_options":"{\"field_slug\":\"input_text\",\"main_field_slug\":\"app-tonicscloud-app-config-mysql\",\"field_slug_unique_hash\":\"6if1x49amdc0000000000\",\"field_input_name\":\"config\",\"config\":\"[mysqld]\\nbind-address            = 127.0.0.1\\n\\n#\\n# * Fine Tuning\\n#\\n\\n#key_buffer_size        = 128M\\n#max_allowed_packet     = 1G\\n#thread_stack           = 192K\\n#thread_cache_size      = 8\\n#max_connections        = 100\\n#table_cache            = 64\\n\\n#\\n# * SSL/TLS\\n#\\n\\n# For documentation, please read\\n# https://mariadb.com/kb/en/securing-connections-for-client-and-server/\\n#ssl-ca = /etc/mysql/cacert.pem\\n#ssl-cert = /etc/mysql/server-cert.pem\\n#ssl-key = /etc/mysql/server-key.pem\\n#require-secure-transport = on\\n\\n#\\n# * Character sets\\n#\\n\\n# MySQL/MariaDB default is Latin1, but in Debian we rather default to the full\\n# utf8 4-byte character set. See also client.cnf\\ncharacter-set-server  = utf8mb4\\ncollation-server      = utf8mb4_general_ci\\n\\n\\n# Uncomment For Remote Connection\\n[mysqld]\\n# skip-networking=0\\n# skip-bind-address\"}"},{"field_id":13,"field_parent_id":null,"field_name":"modular_rowcolumn","field_input_name":"","main_field_slug":"app-tonicscloud-app-config-mysql","field_options":"{\"field_slug\":\"modular_rowcolumn\",\"main_field_slug\":\"app-tonicscloud-app-config-mysql\",\"field_slug_unique_hash\":\"2bv36abhhtlw000000000\",\"field_input_name\":\"\"}"},{"field_id":14,"field_parent_id":13,"field_name":"input_text","field_input_name":"db_remove","main_field_slug":"app-tonicscloud-app-config-mysql","field_options":"{\"field_slug\":\"input_text\",\"main_field_slug\":\"app-tonicscloud-app-config-mysql\",\"field_slug_unique_hash\":\"3lerg7rramm0000000000\",\"field_input_name\":\"db_remove\",\"db_remove\":\"\"}"},{"field_id":15,"field_parent_id":13,"field_name":"input_text","field_input_name":"user_remove","main_field_slug":"app-tonicscloud-app-config-mysql","field_options":"{\"field_slug\":\"input_text\",\"main_field_slug\":\"app-tonicscloud-app-config-mysql\",\"field_slug_unique_hash\":\"1rpbsfocr3i8000000000\",\"field_input_name\":\"user_remove\",\"user_remove\":\"\"}"}]
JSON;
        $fields = json_decode($fieldDetails);
        return json_encode(self::updateFieldOptions($fields, $data));
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function updateSettings (): true
    {
        $this->createOrReplaceFile("/root/.my.cnf", $this->getPostPrepareForFlight()?->config);
        # I could have sanitized the username, and password or the database name, but this won't be possible here,
        # as long as you are the one creating them you should be fine.

        #-----------------
        # FOR USER CREATE
        #-----------------
        $users = $this->getPostPrepareForFlight()?->users;
        $command = '';
        foreach ($users as $user) {
            $host = 'localhost';
            if (!empty($user->user_remote_address)) {
                $host = $user->user_remote_address;
            }
            $username = $user->user_name;
            if (!$username) {
                continue;
            }
            $pass = $user->user_pass;

            $command .= "CREATE USER IF NOT EXISTS '$username'@'$host'; SET PASSWORD FOR '$username'@'$host' =  PASSWORD('$pass'); ";
        }

        $this->runMySQLCommand($command);

        #-----------------------
        # FOR DATABASE CREATE
        # ----------------------
        $databases = $this->getPostPrepareForFlight()?->databases;
        $command = '';
        foreach ($databases as $database) {
            $db_name = $database?->db_name;
            $db_user = $database?->db_user;
            if (!$db_name) {
                continue;
            }
            $host = 'localhost';
            if (!empty($database->db_user_host)) {
                $host = $database->db_user_host;
            }

            if (!empty($db_user)) {
                $command .= "CREATE DATABASE IF NOT EXISTS $db_name; GRANT ALL ON $db_name.* TO '$db_user'@'$host'; ";
            } else {
                $command .= "CREATE DATABASE IF NOT EXISTS $db_name; ";
            }
        }

        $this->runMySQLCommand($command);

        #-----------------------
        # FOR DATABASE REMOVE
        # ----------------------
        $databases = $this->getPostPrepareForFlight()?->db_remove;
        $command = '';
        foreach ($databases as $database) {
            if (empty(trim($database))) {
                continue;
            }
            $command .= "DROP DATABASE IF EXISTS $database; ";
        }

        $this->runMySQLCommand($command);

        #-----------------------
        # FOR USER REMOVE
        # ----------------------
        $users = $this->getPostPrepareForFlight()?->user_remove;
        $command = '';
        foreach ($users as $user) {
            $user = trim($user);
            if (empty($user) || str_starts_with($user, "'root'")) {
                continue;
            }
            $command .= "DROP USER IF EXISTS $user; ";
        }
        $this->runMySQLCommand($command);

        return true;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    private function runMySQLCommand (string $command): void
    {
        if (empty($command)) {
            return;
        }
        $command = "mysql --user=root --password=tonics_cloud  <<< \"$command FLUSH PRIVILEGES;\"";
        $this->runCommand(null, null, "bash", "-c", $command);
    }

    /**
     * @inheritDoc
     */
    public function install (): void
    {
        // TODO: Implement install() method.
    }

    /**
     * @inheritDoc
     */
    public function uninstall (): void
    {
        // TODO: Implement uninstall() method.
    }

    /**
     * @param array $data
     * @param string $flightType
     *
     * @return array
     */
    public function prepareForFlight (array $data, string $flightType = self::PREPARATION_TYPE_SETTINGS): array
    {
        $settings = [
            'users'     => null,
            'databases' => null,
            'config'    => null,
        ];
        $user = [];
        $database = [];
        foreach ($data as $field) {
            if (isset($field->main_field_slug) && isset($field->field_input_name)) {
                $fieldOptions = json_decode($field->field_options);
                $field->field_options = $fieldOptions;
                $value = trim(strtolower($fieldOptions->{$field->field_input_name} ?? ''));
                $value = $this->replaceContainerGlobalVariables($value);
                if ($field->field_input_name == 'user_name' && $value !== 'root') {
                    $user = [];
                    $user['user_name'] = $value;
                }

                if (!empty($user)) {
                    if ($field->field_input_name == 'user_pass') {
                        // password is case-sensitive, so, no strtolower
                        $pass = trim($fieldOptions->{$field->field_input_name} ?? '');
                        $user['user_pass'] = $this->replaceContainerGlobalVariables($pass);
                    }

                    if ($field->field_input_name == 'user_remote_address') {
                        $user['user_remote_address'] = $this->replaceContainerGlobalVariables($value);
                        $settings['users'][] = $user;
                    }
                }

                if ($field->field_input_name == 'db_name') {
                    $database = [];
                    $database['db_name'] = $value;
                }

                if ($field->field_input_name == 'db_user') {
                    $database['db_user'] = $value;
                }

                if ($field->field_input_name == 'db_user_host') {
                    $database['db_user_host'] = $value;
                    $settings['databases'][] = $database;
                }

                if ($field->field_input_name == 'db_remove') {
                    $field->field_options->{$field->field_input_name} = null;
                    $settings['db_remove'] = (!empty($value)) ? array_unique(explode(',', trim($value, ','))) : null;
                }

                if ($field->field_input_name == 'user_remove') {
                    $field->field_options->{$field->field_input_name} = null;
                    $settings['user_remove'] = (!empty($value)) ? array_unique(explode(',', trim($value, ','))) : null;
                }

                if ($field->field_input_name == 'config') {
                    $settings['config'] = $value;
                }
            }
        }

        return $settings;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function reload (): bool
    {
        # If Config is Okay, We Restart
        return $this->runCommand(null, null, "bash", "-c", "if ! mysqld --help 2>&1 | grep -ci error; then systemctl restart mariadb; fi");
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function stop (): bool
    {
        return $this->signalSystemDService('mariadb', self::SystemDSignalStop);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function start (): bool
    {
        return $this->signalSystemDService('mariadb', self::SystemDSignalStart);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function isStatus (string $statusString): bool
    {
        $status = '';
        if (CloudAppSignalInterface::STATUS_RUNNING === $statusString) {
            $this->runCommand(function ($out) use (&$status) { $status = $out; }, null, "bash", "-c", "systemctl show mariadb -p ActiveState");
            return str_starts_with($status, 'ActiveState=active');
        }

        if (CloudAppSignalInterface::STATUS_STOPPED === $statusString) {
            $this->runCommand(function ($out) use (&$status) { $status = $out; }, null, "bash", "-c", "systemctl show mariadb -p ActiveState");
            return str_starts_with($status, 'ActiveState=inactive');
        }

        return false;
    }
}