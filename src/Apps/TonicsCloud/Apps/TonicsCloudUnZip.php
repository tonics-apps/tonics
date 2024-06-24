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

class TonicsCloudUnZip extends CloudAppInterface implements CloudAppSignalInterface
{

    /**
     * Data should contain:
     *
     * ```
     * [
     *     'unzip_extractTo' => '/path/to/extract/to',
     *     'unzip_archiveFile' => 'link-to-unzip-file',
     *     'unzip_format' => 'zip', // leave empty, to auto-detect
     *     'unzip_overwrite' => '1', // or '0', // 1 would overwrite if it already exists
     *     'unzip_createSubDirectory' => '0', // or '1', // 1 if you want to create subDirectory for the file you are extracting
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
[{"field_id":1,"field_parent_id":null,"field_name":"modular_rowcolumn","field_input_name":"","main_field_slug":"app-tonicscloud-app-config-upload-unzip","field_options":"{\"field_slug\":\"modular_rowcolumn\",\"main_field_slug\":\"app-tonicscloud-app-config-upload-unzip\",\"field_slug_unique_hash\":\"65499rzo7qg0000000000\",\"field_input_name\":\"\"}"},{"field_id":2,"field_parent_id":1,"field_name":"modular_rowcolumnrepeater","field_input_name":"","main_field_slug":"app-tonicscloud-app-config-upload-unzip","field_options":"{\"field_slug\":\"modular_rowcolumnrepeater\",\"_moreOptions\":{\"inputName\":\"\",\"field_slug_unique_hash\":\"37zj4aqu5aw0000000000\",\"field_slug\":\"modular_rowcolumnrepeater\",\"field_name\":\"Unzip\",\"depth\":\"0\",\"repeat_button_text\":\"Repeat Unzip\",\"grid_template_col\":\" grid-template-columns: ;\",\"row\":\"1\",\"column\":\"1\",\"_cell_position\":null,\"_can_have_repeater_button\":true},\"main_field_slug\":\"app-tonicscloud-app-config-upload-unzip\",\"field_slug_unique_hash\":\"37zj4aqu5aw0000000000\",\"field_input_name\":\"\"}"},{"field_id":3,"field_parent_id":2,"field_name":"input_text","field_input_name":"unzip_extractTo","main_field_slug":"app-tonicscloud-app-config-upload-unzip","field_options":"{\"field_slug\":\"input_text\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-upload-unzip\",\"field_slug_unique_hash\":\"2vaztkywnk00000000000\",\"field_input_name\":\"unzip_extractTo\",\"unzip_extractTo\":\"/var/www/[[ACME_DOMAIN]]\"}"},{"field_id":4,"field_parent_id":2,"field_name":"input_text","field_input_name":"unzip_archiveFile","main_field_slug":"app-tonicscloud-app-config-upload-unzip","field_options":"{\"field_slug\":\"input_text\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-upload-unzip\",\"field_slug_unique_hash\":\"5cbaf9xaf600000000000\",\"field_input_name\":\"unzip_archiveFile\",\"unzip_archiveFile\":\"https://zip.com/my-file-name-is.zip\"}"},{"field_id":5,"field_parent_id":2,"field_name":"input_select","field_input_name":"unzip_format","main_field_slug":"app-tonicscloud-app-config-upload-unzip","field_options":"{\"field_slug\":\"input_select\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-upload-unzip\",\"field_slug_unique_hash\":\"430yly4zg280000000000\",\"field_input_name\":\"unzip_format\",\"unzip_format\":\"\"}"},{"field_id":6,"field_parent_id":2,"field_name":"input_select","field_input_name":"unzip_overwrite","main_field_slug":"app-tonicscloud-app-config-upload-unzip","field_options":"{\"field_slug\":\"input_select\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-upload-unzip\",\"field_slug_unique_hash\":\"6y5aljg5myo0000000000\",\"field_input_name\":\"unzip_overwrite\",\"unzip_overwrite\":\"1\"}"},{"field_id":7,"field_parent_id":2,"field_name":"input_select","field_input_name":"unzip_createSubDirectory","main_field_slug":"app-tonicscloud-app-config-upload-unzip","field_options":"{\"field_slug\":\"input_select\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-upload-unzip\",\"field_slug_unique_hash\":\"6wm8yessefo0000000000\",\"field_input_name\":\"unzip_createSubDirectory\",\"unzip_createSubDirectory\":\"0\"}"}]
JSON;
        $fields = json_decode($fieldDetails);
        return json_encode(self::updateFieldOptions($fields, $data));
    }

    /**
     * @inheritDoc
     * @throws \Exception
     * @throws \Throwable
     */
    public function updateSettings (): bool
    {
        return $this->runCommand(
            function ($out) {
                // install acme
                if (empty($out)) {
                    $this->install();
                }
                $this->extractFile();
            },
            function ($err) {
                $err = trim($err);
                if (str_ends_with($err, "'{$this->atoolBin()}': No such file or directory")) {
                    $this->install();
                    $this->extractFile();
                }
            }, "bash", "-c", "ls {$this->atoolBin()}");

    }

    /**
     * @inheritDoc
     * @throws \Exception
     * @throws \Throwable
     */
    public function install (): bool
    {
        return $this->runCommand(null, null, "bash", "-c", "apt update -y && apt-get install -y atool wget rsync");
    }

    /**
     * @inheritDoc
     * @throws \Exception
     * @throws \Throwable
     */
    public function uninstall (): bool
    {
        return $this->runCommand(null, null, "bash", "-c", "apt remove atool -y && apt autoremove -y");
    }

    /**
     * @param array $data
     * @param string $flightType
     *
     * @return array
     */
    public function prepareForFlight (array $data, string $flightType = self::PREPARATION_TYPE_SETTINGS): array
    {
        $settings = [];
        $atool = [];
        foreach ($data as $field) {
            if (isset($field->main_field_slug) && isset($field->field_input_name)) {
                $fieldOptions = $this->getFieldOption($field);
                $value = $fieldOptions->{$field->field_input_name} ?? null;
                if ($field->field_input_name == 'unzip_extractTo') {
                    $atool['extract_to'] = $this->replaceContainerGlobalVariables($value);
                }

                if ($field->field_input_name == 'unzip_archiveFile') {
                    $atool['archiveFile'] = $this->replaceContainerGlobalVariables($value);
                }

                if ($field->field_input_name == 'unzip_createSubDirectory') {
                    $atool['subDirectory'] = $value;
                }

                if ($field->field_input_name == 'unzip_format') {
                    $atool['format'] = $value;
                }

                if ($field->field_input_name == 'unzip_overwrite') {
                    $atool['overwrite'] = $value;
                    $settings[] = $atool;
                }
            }
        }

        return $settings;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    private function extractFile (): void
    {
        $formats = "7z,a,ace,alz,arc,arj,bz,bz2,cab,cpio,deb,gz,jar,lha,lrz,lz,lzh,lzma,lzo,rar,rpm,rz,t7z,tar,tar.7z,tar.bz,tar.bz2,tar.gz,tar.lz,tar.lzo,tar.xz,tar.Z,tbz,tbz2,tgz,tlz,txz,tZ,tzo,war,xz,Z,zip";
        $formats = explode(',', $formats);
        $formats = array_flip($formats);
        $extracts = $this->getPostPrepareForFlight();
        foreach ($extracts as $extract) {
            $option = '';
            $archiveFile = '';
            $extractTo = '';
            $overwrite = false;
            if (isset($extract->extract_to)) {
                $extractTo = escapeshellarg($extract->extract_to);
            }

            if (isset($extract->subDirectory) && $extract->extract_to === '1') {
                $option .= '-D ';
            }

            if (isset($extract->overwrite) && $extract->overwrite === '1') {
                $overwrite = true;
                $option .= '-f ';
            }

            if (!empty($extract->format) && isset($formats[$extract->format])) {
                $option .= "-F $extract->format ";
            }

            if (isset($extract->archiveFile)) {
                $archiveFile = escapeshellarg($extract->archiveFile);
            }

            # MkDir If it Does Not Exit
            # Create a TempDir
            # Download The File into The Temp Dir
            # Get The Downloaded File Path
            # Unpack The Archive Into The Respective Dir
            # Clean The Temp Dir
            $command = "mkdir -p $extractTo && temp_dir=$(mktemp -d) && wget --content-disposition -P \$temp_dir $archiveFile && file_path=$(ls -1 \$temp_dir) && ";
            if ($overwrite) {
                # (for overwrite, it extracts it into a temp dir, and then sync it to $extractTo, anything not in the temp dir, would be deleted from $extractTo for overwrite)
                # Create a extractTemp Dir
                # Extract into the extractTemp
                # Sync extractTemp to ExtractTo, this is complete overwrite, anything not in temp dir, would be deleted from extractTo
                # Clean up extractTemp
                $command .= "extract_temp=$(mktemp -d) && ";
                $command .= "{$this->atoolBin()} $option -X \$extract_temp \$temp_dir/\$file_path && ";
                $command .= "rsync -av --delete \$extract_temp/ $extractTo/ && rm -r \$extract_temp && ";
            } else {
                $command .= "{$this->atoolBin()} $option -X $extractTo \$temp_dir/\$file_path && ";
            }

            $command .= "rm -r \$temp_dir";

            $this->runCommand(null, null, "bash", "-c", $command);
        }

    }

    private function atoolBin (): string
    {
        return "/bin/atool";
    }

    public function reload (): true
    {
        return true;
    }

    public function stop (): true
    {
        return true;
    }

    public function start (): true
    {
        return true;
    }

    public function isStatus (string $statusString): bool
    {
        return true;
    }
}