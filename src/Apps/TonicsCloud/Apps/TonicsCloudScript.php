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

class TonicsCloudScript extends CloudAppInterface implements CloudAppSignalInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function updateSettings(): void
    {
        $this->runCommand(null, null, "bash", "-c", <<<EOF
{$this->getPostPrepareForFlight()?->content}
EOF);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function install(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function uninstall(): bool
    {
        return true;
    }

    public function prepareForFlight(array $data, string $flightType = self::PREPARATION_TYPE_SETTINGS): array
    {
        $content = '';
        foreach ($data as $field) {
            if (isset($field->main_field_slug) && isset($field->field_input_name)) {
                $fieldOptions = json_decode($field->field_options);
                if ($field->field_input_name == 'content'){
                    $content = $fieldOptions->{$field->field_input_name} ?? null;
                    if (is_string($content)) {
                        $content = $this->replaceContainerGlobalVariables($content);
                    }
                    break;
                }
            }
        }

        return [
            'content' => $content
        ];
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