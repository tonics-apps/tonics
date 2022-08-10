<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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
            ['Apps_TonicsToc_Settings'],
            $this->getTonicsTocSettingData()
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
        $result = FieldConfig::savePluginFieldSettings(self::getTonicsTocSettingsFile(), $_POST);

        if (!$result){
            session()->flash(['An Error Occurred Saving Settings'], $_POST);
            redirect(route('tonicsToc.settings'));
        }

        apcu_store(self::TonicsPlugin_TonicsTocSettings, FieldConfig::loadPluginSettings(self::getTonicsTocSettingsFile()));
        session()->flash(['Settings Updated'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('tonicsToc.settings'));
    }

    public static function getTonicsTocSettingsFile(): string
    {
        return AppConfig::getAppsPath() . DIRECTORY_SEPARATOR . 'TonicsToc' . DIRECTORY_SEPARATOR . 'settings.json';
    }

    /**
     * @throws \Exception
     */
    public static function getTonicsTocSettingData()
    {
        $settings = apcu_fetch(self::TonicsPlugin_TonicsTocSettings);
        if ($settings === false){
            $settings = FieldConfig::loadPluginSettings(self::getTonicsTocSettingsFile());
        }
        if (empty($settings)){
            // force default:
            $settings = [
                'toc_depth' => 4,
                'toc_trigger' => 2,
                'toc_label' => 'Table of Contents',
                'toc_label_tag' => 'div',
                'toc_class' => 'tonics-tic',
            ];
        }
        return $settings;
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