<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Core\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Field\Data\FieldData;

abstract class AbstractFieldSettingsController
{
    const CACHE_KEY = '';

    public function __construct (private ?FieldData $fieldData = null) {}

    /**
     * @return string
     */
    public static function getCacheKey (): string
    {
        return AppConfig::getAppCacheKey() . static::CACHE_KEY;
    }

    /**
     * @param string $routeName
     *
     * @return void
     * @throws \Throwable
     */
    public function updateSettings (string $routeName): void
    {
        try {
            $settings = FieldConfig::savePluginFieldSettings(static::getCacheKey(), $_POST);
            apcu_store(static::getCacheKey(), $settings);

            session()->flash(['Settings Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route($routeName));
        } catch (\Exception) {
            session()->flash(['An Error Occurred Saving Settings'], $_POST);
            redirect(route($routeName));
        }
    }

    /**
     * @throws \Exception
     */
    public static function getSettingsData (): array
    {
        $settings = apcu_fetch(static::getCacheKey());
        if ($settings === false) {
            $settings = FieldConfig::loadPluginSettings(static::getCacheKey());
        }
        return $settings;
    }

    /**
     * @param string $key
     * @param $default
     * If $key value is empty, we use $default
     *
     * @return string
     * @throws \Exception
     */
    public static function getSettingsValue (string $key, $default = null): mixed
    {
        #
        # If DB doesn't exist here, then it means we are accessing settings too early,
        # we fall back to $default
        #
        if (!function_exists('db')) {
            return $default;
        }

        $settings = static::getSettingsData();
        if (key_exists($key, $settings)) {
            $value = $settings[$key];
            if ($value !== '') {
                return $value;
            }
        }

        return $default;
    }

    /**
     * @return FieldData|null
     */
    public function getFieldData (): ?FieldData
    {
        return $this->fieldData;
    }

    /**
     * @param FieldData|null $fieldData
     */
    public function setFieldData (?FieldData $fieldData): void
    {
        $this->fieldData = $fieldData;
    }


}