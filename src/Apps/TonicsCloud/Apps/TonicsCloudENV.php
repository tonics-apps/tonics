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

                $fieldOptions = json_decode($field->field_options);
                $field->field_options = $fieldOptions;
                $value = $fieldOptions->{$field->field_input_name} ?? null;

                if ($field->field_input_name == 'env_path') {
                    $app = [];
                    $app['env_path'] = $value;
                }

                if ($field->field_input_name == 'env_content') {
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
     * @throws \Exception
     */
    private function replaceString (string $content, string $targetString, callable $replacer): string
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