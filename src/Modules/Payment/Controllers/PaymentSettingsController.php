<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Field\Data\FieldData;

class PaymentSettingsController
{
    private ?FieldData $fieldData;

    const TonicsModule_TonicsPaymentSettings = 'TonicsModule_TonicsPaymentSettings';

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
            ['payment-settings'],
            $this->getSettingsData()
        )->getHTMLFrag();

        view('Modules::Payment/Views/settings', [
                'FieldItems' => $fieldItems
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
            redirect(route('payment.settings'));
        }catch (\Exception){
            session()->flash(['An Error Occurred Saving Settings'], $_POST);
            redirect(route('payment.settings'));
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
        return $settings;
    }

    public static function getCacheKey(): string
    {
        return AppConfig::getAppCacheKey() . self::TonicsModule_TonicsPaymentSettings;
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