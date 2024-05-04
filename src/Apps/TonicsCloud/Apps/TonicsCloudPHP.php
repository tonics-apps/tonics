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

class TonicsCloudPHP extends CloudAppInterface implements CloudAppSignalInterface
{
    private string $version = '';

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function updateSettings(): void
    {
        $fpm = $this->getPostPrepareForFlight()->fpm;
        $ini = $this->getPostPrepareForFlight()->ini;

        if ($fpm){
            $fpm = helper()->replacePlaceHolders($fpm, [
                "[[PHP_VERSION]]" => $this->phpVersion()
            ]);
            $this->createOrReplaceFile("/etc/php/{$this->phpVersion()}/fpm/php-fpm.conf", $fpm);
        }

        if ($ini){
            $ini = helper()->replacePlaceHolders($ini, [
                "[[PHP_VERSION]]" => $this->phpVersion()
            ]);
            $this->createOrReplaceFile("/etc/php/{$this->phpVersion()}/fpm/php.ini", $ini);
        }
    }

    /**
     * @inheritDoc
     */
    public function install(): mixed
    {
        // TODO: Implement install() method.
    }

    /**
     * @inheritDoc
     */
    public function uninstall(): mixed
    {
        // TODO: Implement uninstall() method.
    }

    /**
     * @throws \Exception
     */
    public function prepareForFlight(array $data, string $flightType = self::PREPARATION_TYPE_SETTINGS): array
    {
        $settings = [
            'fpm' => null,
            'ini' => null
        ];

        foreach ($data as $field) {

            if (isset($field->main_field_slug) && isset($field->field_input_name)) {

                $fieldOptions = json_decode($field->field_options);
                $value = $fieldOptions->{$field->field_input_name} ?? null;
                if ($field->field_input_name == 'fpm') {
                    $settings['fpm'] = $value;
                }

                if ($field->field_input_name == 'ini') {
                    $settings['ini'] = $value;
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
        return $this->signalSystemDService("php{$this->phpVersion()}-fpm", self::SystemDSignalReload);
    }

    /**
     * @throws \Exception
     */
    public function stop(): bool
    {
        return $this->signalSystemDService("php{$this->phpVersion()}-fpm", self::SystemDSignalStop);
    }

    /**
     * @throws \Exception
     */
    public function start(): bool
    {
        return $this->signalSystemDService("php{$this->phpVersion()}-fpm", self::SystemDSignalStart);
    }

    /**
     * @throws \Exception
     */
    public function isStatus(string $statusString): bool
    {
        $status = '';
        $php = "php{$this->phpVersion()}-fpm";
        if (CloudAppSignalInterface::STATUS_RUNNING === $statusString){
            $this->runCommand(function ($out) use (&$status){ $status = $out; }, null, "bash", "-c", "systemctl show $php -p ActiveState");
            return str_starts_with($status, 'ActiveState=active');
        }

        if (CloudAppSignalInterface::STATUS_STOPPED === $statusString){
            $this->runCommand(function ($out) use (&$status){ $status = $out; }, null, "bash", "-c", "systemctl show $php -p ActiveState");
            return str_starts_with($status, 'ActiveState=inactive');
        }

        return false;
    }

    /**
     * @throws \Exception
     */
    private function phpVersion(): string
    {
        if (empty($this->version)){
            $this->runCommand(function ($out){ $this->version = $out;}, null, "bash", "-c", <<<EOF
php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;'
EOF);
        }
        return trim($this->version);
    }
}