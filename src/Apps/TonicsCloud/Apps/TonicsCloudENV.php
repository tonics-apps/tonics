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

class TonicsCloudENV extends CloudAppInterface implements CloudAppSignalInterface
{
    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function updateSettings(): void
    {
        foreach ($this->getPostPrepareForFlight() as $env){
            if (isset($env->env_path) && isset($env->env_content)){
                $this->createOrReplaceFile($env->env_path, $env->env_content);
            }
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
        $settings = [];
        $app = [];
        foreach ($data as $field){
            if (isset($field->main_field_slug) && isset($field->field_input_name)) {

                $fieldOptions = json_decode($field->field_options);
                $field->field_options = $fieldOptions;
                $value = $fieldOptions->{$field->field_input_name} ?? null;

                if ($field->field_input_name == 'env_path'){
                    $app = [];
                    $app['env_path'] = $value;
                }

                if ($field->field_input_name == 'env_content'){
                    $app['env_content'] = $this->replaceContainerGlobalVariables($value);
                    $field->field_options->{$field->field_input_name} = $app['env_content'];
                    $settings[] = $app;
                }
            }
        }

        // $this->setFields(json_decode(json_encode($data)));
        return $settings;
    }

    /**
     * @throws \Exception
     */
    public function reload(): bool
    {
        return true;
    }

    /**
     * @throws \Exception
     */
    public function stop(): bool
    {
        return true;
    }

    /**
     * @throws \Exception
     */
    public function start(): bool
    {
        return true;
    }

    /**
     * @throws \Exception
     */
    public function isStatus(string $statusString): bool
    {
        return true;
    }

    /**
     * @throws \Exception
     */
    private function replaceString(string $content, string $targetString, callable $replacer): string
    {
        // Calculate the length of the target string
        $targetStringLength = strlen($targetString);

        // Replace all occurrences of the target string with random bytes
        while (($pos = strpos($content, $targetString)) !== false) {
            $replace = $replacer();
            // Replace the target string with the random bytes
            $content = substr_replace($content, $replace, $pos, $targetStringLength);
        }

        return $content;
    }
}