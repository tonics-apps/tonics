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

namespace App\Apps\TonicsCoupon\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Modules\Post\Data\PostData;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

const TonicsApp_TonicsCouponSettings = 'TonicsApp_TonicsCouponSettings';

class CouponSettingsController
{
    private ?FieldData $fieldData;

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
            ['app-tonicscoupon-settings'],
            $this->getSettingData()
        )->getHTMLFrag();

        view('Apps::TonicsCoupon/Views/settings', [
                'FieldItems' => $fieldItems,
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
            redirect(route('tonicsCoupon.settings'));
        }catch (\Exception){
            session()->flash(['An Error Occurred Saving Settings'], $_POST);
            redirect(route('tonicsCoupon.settings'));
        }

    }

    /**
     * @throws \Exception
     */
    public static function getSettingData()
    {
        $settings = apcu_fetch(self::getCacheKey());
        if ($settings === false){
            $settings = FieldConfig::loadPluginSettings(self::getCacheKey());
        }

        return $settings;
    }

    public static function getCacheKey(): string
    {
        return AppConfig::getAppCacheKey() . TonicsApp_TonicsCouponSettings;
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

    /**
     * @throws \Exception
     */
    public static function getTonicsCouponRootPath(): string
    {
        $data = CouponSettingsController::getSettingData();
        $rootPath = 'coupon';
        if (isset($data['tonicsCoupon_root_path'])){
            $rootPath = helper()->slug($data['tonicsCoupon_root_path']);
        }
        return $rootPath;
    }

    /**
     * @throws \Exception
     */
    public static function getTonicsCouponTypeRootPath(): string
    {
        $data = CouponSettingsController::getSettingData();
        $rootPath = 'coupon_type';
        if (isset($data['tonicsCouponType_root_path'])){
            $rootPath = helper()->slug($data['tonicsCouponType_root_path']);
        }
        return $rootPath;
    }
}