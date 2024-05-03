<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Apps;

use App\Apps\TonicsCloud\Interfaces\CloudAppInterface;
use App\Apps\TonicsCloud\Interfaces\CloudAppSignalInterface;

class TonicsCloudMariaDB extends CloudAppInterface implements CloudAppSignalInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function updateSettings(): true
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
            if (!empty($user->user_remote_address)){ $host = $user->user_remote_address; }
            $username = $user->user_name;
            if (!$username){continue;}
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
            if (!$db_name){continue;}
            $host = 'localhost';
            if (!empty($database->db_user_host)){ $host = $database->db_user_host; }

            if (!empty($db_user)){
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
            if (empty(trim($database))){continue;}
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
            if (empty($user) || str_starts_with($user, "'root'")){continue;}
            $command .= "DROP USER IF EXISTS $user; ";
        }
        $this->runMySQLCommand($command);

        return true;
    }

    /**
     * @throws \Exception
     */
    private function runMySQLCommand(string $command): void
    {
        if (empty($command)){return;}
        $command = "mysql --user=root --password=tonics_cloud  <<< \"$command FLUSH PRIVILEGES;\"";
        $this->runCommand(null, null, "bash", "-c", $command);
    }

    /**
     * @inheritDoc
     */
    public function install(): void
    {
        // TODO: Implement install() method.
    }

    /**
     * @inheritDoc
     */
    public function uninstall(): void
    {
        // TODO: Implement uninstall() method.
    }

    /**
     * @param array $data
     * @param string $flightType
     * @return array
     */
    public function prepareForFlight(array $data, string $flightType = self::PREPARATION_TYPE_SETTINGS): array
    {
        $settings = [
            'users' => null,
            'databases' => null,
            'config' => null,
        ];
        $user = []; $database = [];
        foreach ($data as $field){
            if (isset($field->main_field_slug) && isset($field->field_input_name)){
                $fieldOptions = json_decode($field->field_options);
                $field->field_options = $fieldOptions;
                $value = trim(strtolower($fieldOptions->{$field->field_input_name} ?? ''));
                $value = $this->replaceContainerGlobalVariables($value);
                if ($field->field_input_name == 'user_name' && $value !== 'root'){
                    $user = [];
                    $user['user_name'] = $value;
                }

                if (!empty($user)){
                    if ($field->field_input_name == 'user_pass'){
                        // password is case-sensitive, so, no strtolower
                        $pass = trim($fieldOptions->{$field->field_input_name} ?? '');
                        $user['user_pass'] = $this->replaceContainerGlobalVariables($pass);
                    }

                    if ($field->field_input_name == 'user_remote_address'){
                        $user['user_remote_address'] = $this->replaceContainerGlobalVariables($value);
                        $settings['users'][] = $user;
                    }
                }

                if ($field->field_input_name == 'db_name'){
                    $database = [];
                    $database['db_name'] = $value;
                }

                if ($field->field_input_name == 'db_user'){
                    $database['db_user'] = $value;
                }

                if ($field->field_input_name == 'db_user_host'){
                    $database['db_user_host'] = $value;
                    $settings['databases'][] = $database;
                }

                if ($field->field_input_name == 'db_remove'){
                    $field->field_options->{$field->field_input_name} = null;
                    $settings['db_remove'] = (!empty($value)) ? array_unique(explode(',', trim($value, ','))) : null;
                }

                if ($field->field_input_name == 'user_remove'){
                    $field->field_options->{$field->field_input_name} = null;
                    $settings['user_remove'] = (!empty($value)) ? array_unique(explode(',', trim($value, ','))) : null;
                }

                if ($field->field_input_name == 'config'){
                    $settings['config'] = $value;
                }
            }
        }

        return $settings;
    }

    /**
     * @throws \Exception
     */
    public function reload(): bool
    {
        # If Config is Okay, We Restart
        return $this->runCommand(null, null, "bash", "-c", "if ! mysqld --help 2>&1 | grep -ci error; then systemctl restart mariadb; fi");
    }

    /**
     * @throws \Exception
     */
    public function stop(): bool
    {
        return $this->signalSystemDService('mariadb', self::SystemDSignalStop);
    }

    /**
     * @throws \Exception
     */
    public function start(): bool
    {
        return $this->signalSystemDService('mariadb', self::SystemDSignalStart);
    }

    /**
     * @throws \Exception
     */
    public function isStatus(string $statusString): bool
    {
        $status = '';
        if (CloudAppSignalInterface::STATUS_RUNNING === $statusString){
            $this->runCommand(function ($out) use (&$status){ $status = $out; }, null, "bash", "-c", "systemctl show mariadb -p ActiveState");
            return str_starts_with($status, 'ActiveState=active');
        }

        if (CloudAppSignalInterface::STATUS_STOPPED === $statusString){
            $this->runCommand(function ($out) use (&$status){ $status = $out; }, null, "bash", "-c", "systemctl show mariadb -p ActiveState");
            return str_starts_with($status, 'ActiveState=inactive');
        }

        return false;
    }
}