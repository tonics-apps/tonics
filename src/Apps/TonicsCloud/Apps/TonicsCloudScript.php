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
     * Data should contain:
     *
     * ```
     * [
     *     'content' => '...' // content of script
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
[{"field_id":1,"field_parent_id":null,"field_name":"modular_rowcolumn","field_input_name":"app_config_script_recipe","main_field_slug":"app-tonicscloud-app-config-script","field_options":"{\"field_slug\":\"modular_rowcolumn\",\"main_field_slug\":\"app-tonicscloud-app-config-script\",\"field_slug_unique_hash\":\"em5qrt886bs000000000\",\"field_input_name\":\"app_config_script_recipe\"}"},{"field_id":2,"field_parent_id":1,"field_name":"modular_fieldselectiondropper","field_input_name":"app_config_script_recipe_selected","main_field_slug":"app-tonicscloud-app-config-script","field_options":"{\"field_slug\":\"modular_fieldselectiondropper\",\"main_field_slug\":\"app-tonicscloud-app-config-script\",\"field_slug_unique_hash\":\"4k8ygj5o0p60000000000\",\"field_input_name\":\"app_config_script_recipe_selected\",\"app_config_script_recipe_selected\":\"app-tonicscloud-script-recipe-manual\"}"},{"field_id":3,"field_parent_id":2,"field_name":"input_text","field_input_name":"content","main_field_slug":"app-tonicscloud-script-recipe-manual","field_options":"{\"field_slug\":\"input_text\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-script-recipe-manual\",\"field_slug_unique_hash\":\"64fmuksdbqo0000000000\",\"field_input_name\":\"content\",\"content\":\"\"}"}]
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
        $code = trim(str_replace("\r", '', $this->getPostPrepareForFlight()?->content)); // quick clean up
        $this->runCommand(null, null, "bash", "-c", <<<SCRIPT
$code
SCRIPT,
        );

    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function install (): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function uninstall (): bool
    {
        return true;
    }

    public function prepareForFlight (array $data, string $flightType = self::PREPARATION_TYPE_SETTINGS): array
    {
        $content = '';
        foreach ($data as $field) {
            if (isset($field->main_field_slug) && isset($field->field_input_name)) {
                $fieldOptions = json_decode($field->field_options);
                if ($field->field_input_name == 'content') {
                    $content = $fieldOptions->{$field->field_input_name} ?? null;
                    if (is_string($content)) {
                        $content = $this->replaceContainerGlobalVariables($content);
                    }
                    break;
                }
            }
        }

        return [
            'content' => $content,
        ];
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

    public static function WordPressScript (): string
    {
        return <<<'SCRIPT'
# Enable logging
LOG_FILE="/var/log/wp_script.log"
touch $LOG_FILE
exec > >(tee -a $LOG_FILE) 2>&1

chown -R "www-data:www-data" [[ROOT]]

#
#   Change permission of all directory and files
find [[ROOT]] -type d -exec chmod 755 {} \;
find [[ROOT]] -type f -exec chmod 644 {} \;

#
#   Change permission of wp-config
chmod 660 [[ROOT]]/wp-config.php

#
#   Allow WordPress To Manage public contents
find [[ROOT]]/wp-content -type d -exec chmod 775 {} \;
find [[ROOT]]/wp-content -type f -exec chmod 664 {} \;

#
# Import WordPress DB if there is one, and clean it up once done

# Define the folder to check
SQL_FILE="[[ROOT]]/exported.sql"

# Check if the exported.sql file exists in the folder
if [ -f "$SQL_FILE" ]; then
  # Import the exported.sql file into MariaDB
  if mysql --user="[[DB_USER]]" --password="[[DB_PASS]]" "[[DB_DATABASE]]" < "$SQL_FILE"; then
    echo "Database import completed successfully."
    echo "Cleaning up the SQL file..."
    if rm "$SQL_FILE"; then
      echo "SQL file deleted successfully."
    else
      echo "Error: Failed to delete the SQL file."
      exit 1
    fi
  else
    echo "Error: Database import failed."
    exit 1
  fi
fi
SCRIPT;

    }

    /**
     * @return string
     */
    public static function TonicsCloudScript (): string
    {
        return <<<'SCRIPT'
# Enable logging
LOG_FILE="/var/log/tonics_script.log"
touch $LOG_FILE
exec > >(tee -a $LOG_FILE) 2>&1

chown -R "www-data:www-data" [[ROOT]]

#
#   Change permission of all directory and files
find [[ROOT]] -type d -exec chmod 755 {} \;
find [[ROOT]] -type f -exec chmod 644 {} \;

#
#   Change permission of env file
chmod 660 [[ROOT]]/web/.env

#
#   Allow Tonics To Manage private uploads
find [[ROOT]]/private -type d -exec chmod 775 {} \;
find [[ROOT]]/private -type f -exec chmod 664 {} \;

#
#   Allow Tonics To Manage public contents
find [[ROOT]]/web/public -type d -exec chmod 775 {} \;
find [[ROOT]]/web/public -type f -exec chmod 664 {} \;

#
# Import Tonics DB if there is one, and clean it up once done

# Define the folder to check
SQL_FILE="[[ROOT]]/exported.sql"

# Check if the exported.sql file exists in the folder
if [ -f "$SQL_FILE" ]; then
  # Import the exported.sql file into MariaDB
  if mysql --user="[[DB_USER]]" --password="[[DB_PASS]]" "[[DB_DATABASE]]" < "$SQL_FILE"; then
    echo "Database import completed successfully."
    echo "Cleaning up the SQL file..."
    if rm "$SQL_FILE"; then
      echo "SQL file deleted successfully."
    else
      echo "Error: Failed to delete the SQL file."
      exit 1
    fi
  else
    echo "Error: Database import failed."
    exit 1
  fi
fi
SCRIPT;

    }
}