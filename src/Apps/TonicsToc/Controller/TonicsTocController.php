<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Apps\TonicsToc\Controller;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Field\Data\FieldData;

class TonicsTocController
{
    private ?FieldData $fieldData;

    const TonicsPlugin_TonicsTocSettings = 'TonicsPlugin_TonicsTocSettings';

    public function __construct(FieldData $fieldData = null)
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @throws \Exception
     */
    public function edit(): void
    {
        $fieldItems = $this->getFieldData()->generateFieldWithFieldSlug(
            ['app-tonicstoc-settings'],
            $this->getSettingsData()
        )->getHTMLFrag();

        view('Apps::TonicsToc/Views/settings', [
            'FieldItems' => $fieldItems,
                'Asset' => [
                    'js' => AppConfig::getAppAsset('TonicsToc', 'js/script.js')
                ]
            ]
        );
    }

    /**
     * @throws \Exception
     */
    public function update()
    {
        try {
            $settings = FieldConfig::savePluginFieldSettings(self::getCacheKey(), $_POST);
            apcu_store(self::getCacheKey(), $settings);
            session()->flash(['Settings Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('tonicsToc.settings'));
        }catch (\Exception){
            session()->flash(['An Error Occurred Saving Settings'], $_POST);
            redirect(route('tonicsToc.settings'));
        }
    }

    /**
     * @throws \Exception
     */
    public static function getSettingsData()
    {
        $settings = apcu_fetch(self::getCacheKey());
        if ($settings === false){
            $settings = FieldConfig::loadPluginSettings(self::getCacheKey());
        }
        if (empty($settings)){
            // force default:
            $settings = [
                'toc_depth' => 4,
                'toc_trigger' => 2,
                'toc_label' => 'Table of Contents',
                'toc_label_tag' => 'div',
                'toc_class' => 'tonics-toc width:100% padding:default color:black border-width:default border:black position:relative',
            ];
        }
        return $settings;
    }

    public static function getCacheKey(): string
    {
        return AppConfig::getAppCacheKey() . self::TonicsPlugin_TonicsTocSettings;
    }

    /**
     * @return FieldData|null
     */
    public function getFieldData(): ?FieldData
    {
        return $this->fieldData;
    }

    /**
     * @param FieldData|null $fieldData
     */
    public function setFieldData(?FieldData $fieldData): void
    {
        $this->fieldData = $fieldData;
    }
}