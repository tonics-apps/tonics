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

class TonicsCloudUnZip extends CloudAppInterface implements CloudAppSignalInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function updateSettings(): bool
    {
       return $this->runCommand(
            function ($out){
                // install acme
                if (empty($out)){
                    $this->install();
                }
                $this->extractFile();
            },
            function ($err) {
                $err = trim($err);
                if (str_ends_with($err, "'{$this->atoolBin()}': No such file or directory")){
                    $this->install();
                    $this->extractFile();
                }
                }, "bash", "-c", "ls {$this->atoolBin()}");

    }

    private function atoolBin(): string
    {
        return "/bin/atool";
    }

    /**
     * @throws \Exception
     */
    private function extractFile(): void
    {
        $formats = "7z,a,ace,alz,arc,arj,bz,bz2,cab,cpio,deb,gz,jar,lha,lrz,lz,lzh,lzma,lzo,rar,rpm,rz,t7z,tar,tar.7z,tar.bz,tar.bz2,tar.gz,tar.lz,tar.lzo,tar.xz,tar.Z,tbz,tbz2,tgz,tlz,txz,tZ,tzo,war,xz,Z,zip";
        $formats = explode(',', $formats);
        $formats = array_flip($formats);
        $extracts = $this->getPostPrepareForFlight();
        foreach ($extracts as $extract){
            $option = '';
            $archiveFile = '';
            $extractTo = '';
            $overwrite = false;
            if (isset($extract->extract_to)){
                $extractTo = escapeshellarg($extract->extract_to);
                $option .= "-X $extractTo ";
            }

            if (isset($extract->subDirectory) && $extract->extract_to === '1'){
                $option .= '-D ';
            }

            if (isset($extract->overwrite) && $extract->overwrite === '1'){
                $overwrite = true;
                $option .= '-f ';
            }

            if (!empty($extract->format) && isset($formats[$extract->format])){
                $option .= "-F $extract->format ";
            }

            if (isset($extract->archiveFile)){
                $archiveFile = escapeshellarg($extract->archiveFile);
            }

            # MkDir If it Does Not Exit
            # Create a TempDir
            # Download The File into The Temp Dir
            # Get The Downloaded File Path
            # Unpack The Archive Into The Respective Dir
            # Clean The Temp Dir
            $command = "mkdir -p $extractTo && temp_dir=$(mktemp -d) && wget --content-disposition -P \$temp_dir $archiveFile && file_path=$(ls -1 \$temp_dir) && ";
            $command .= ($overwrite) ? "yes |" : "yes N |"; # the --force option is not working, so, we're using yes command as an alternative
            $command .= " {$this->atoolBin()} $option \$temp_dir/\$file_path && rm -r \$temp_dir";
            $this->runCommand(null, null, "bash", "-c", $command);
        }

    }


    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function install(): bool
    {
        return $this->runCommand(null, null, "bash", "-c", "apt update -y && apt-get install -y atool wget");
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function uninstall(): bool
    {
        return $this->runCommand(null, null, "bash", "-c", "apt remove atool -y && apt autoremove -y");
    }

    /**
     * @param array $data
     * @param string $flightType
     * @return array
     */
    public function prepareForFlight(array $data, string $flightType = self::PREPARATION_TYPE_SETTINGS): array
    {
        $settings = [];
        $atool = [];
        foreach ($data as $field){
            if (isset($field->main_field_slug) && isset($field->field_input_name)){
                $fieldOptions = json_decode($field->field_options);
                $value = $fieldOptions->{$field->field_input_name} ?? null;
                if ($field->field_input_name == 'unzip_extractTo'){
                    $atool['extract_to'] = $this->replaceContainerGlobalVariables($value);
                }

                if ($field->field_input_name == 'unzip_archiveFile'){
                    $atool['archiveFile']  = $this->replaceContainerGlobalVariables($value);
                }

                if ($field->field_input_name == 'unzip_createSubDirectory'){
                    $atool['subDirectory']  = $value;
                }

                if ($field->field_input_name == 'unzip_format'){
                    $atool['format'] = $value;
                }

                if ($field->field_input_name == 'unzip_overwrite'){
                    $atool['overwrite'] = $value;
                    $settings[] = $atool;
                }
            }
        }

        return $settings;
    }

    public function reload(): true
    {
        return true;
    }

    public function stop(): true
    {
        return true;
    }

    public function start(): true
    {
        return true;
    }

    public function isStatus(string $statusString): bool
    {
        return true;
    }
}