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