<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Controllers;

use App\Apps\TonicsCloud\Schedules\CloudScheduleCheckCredits;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class TonicsCloudSettingsController
{
    const TonicsApp_TonicsCloudSettings = 'TonicsApp_TonicsCloudSettings';

    const LinodeAPIToken = 'tonics_cloud_main_container_APITokens_LinodeAkamai_Key';
    const LinodeDeploymentOption = 'tonics_cloud_main_container_APITokens_LinodeDeploymentOption';
    const LinodeImage = 'tonics_cloud_main_container_APITokens_LinodeAkamai_LinodeImage';
    const LinodeStackScriptMode = 'tonics_cloud_main_container_APITokens_LinodeAkamai_LinodeStackScript_Mode';
    const LinodeStackScriptSSHPublicKeyForDevMode = 'tonics_cloud_main_container_APITokens_LinodeAkamai_LinodeStackScript_SSH_PUBLIC_KEY_DEV_MODE';
    const LinodePriceList = 'tonics_cloud_main_container_APITokens_LinodeAkamai_PriceList';
    const LinodeRegion = 'tonics_cloud_main_container_APITokens_LinodeAkamai_Region';
    const LinodeBackup = 'tonics_cloud_main_container_APITokens_LinodeAkamai_Backup';

    const UpCloudUserName = 'tonics_cloud_main_container_APITokens_UpCloud_UserName';
    const UpCloudPassword = 'tonics_cloud_main_container_APITokens_UpCloud_Password';
    const UpCloudPriceList = 'tonics_cloud_main_container_APITokens_UpCloud_PriceList';
    const UpCloudRegion = 'tonics_cloud_main_container_APITokens_UpCloud_Region';
    const UpCloudMode = 'tonics_cloud_main_container_APITokens_UpCloud_Mode';
    const UpCloudSSHPublicKeyForDevMode = 'tonics_cloud_main_container_APITokens_UpCloud_SSH_PUBLIC_KEY';


    const AWSAPIToken = 'tonics_cloud_main_container_APITokens_AWS_Key';
    const CloudServerIntegrationType = 'tonics_cloud_main_container_cloudServer_Integration';
    const CloudDNSIntegrationType = 'tonics_cloud_main_container_DNS_Integration';

    const IncusTrustPassword = 'tonics_cloud_main_container_incus_TrustPassword';

    const EnableBilling = 'tonics_cloud_main_container_Others_enableBilling';
    const NotifyIfCreditBalanceIsLessThan = 'tonics_cloud_main_container_Others_notifyIfCreditIsLessThan';

    private ?FieldData $fieldData;
    private static array $settings = [];
    public function __construct(FieldData $fieldData = null)
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @throws \Exception|\Throwable
     */
    public function edit(): void
    {
        view('Apps::TonicsCloud/Views/settings', [
                'FieldItems' => FieldConfig::getSettingsHTMLFrag($this->getFieldData(), self::getSettingsData(), ['app-tonicscloud-settings'])
            ]
        );
    }

    /**
     * @throws \Exception|\Throwable
     */
    public function update()
    {
        try {
            $settings = FieldConfig::savePluginFieldSettings(self::getCacheKey(), $_POST);
            apcu_store(self::getCacheKey(), $settings);

            self::insertServerDefaultServices();

            $cloudScheduleCheckCredits = new CloudScheduleCheckCredits();
            schedule()->enqueue($cloudScheduleCheckCredits);

            session()->flash(['Settings Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('tonicsCloud.settings'));
        }catch (\Exception){
            session()->flash(['An Error Occurred Saving Settings'], $_POST);
            redirect(route('tonicsCloud.settings'));
        }
    }


    /**
     * If key is given, we check if it exists, return the value, otherwise, we return all settings data
     * @param string $key
     * @return array|false|mixed
     * @throws \Exception
     */
    public static function getSettingsData(string $key = ''): mixed
    {
        if (!self::$settings) {
            $settings = apcu_fetch(self::getCacheKey());
            if ($settings === false){
                $settings = FieldConfig::loadPluginSettings(self::getCacheKey());
            }
            self::$settings = $settings;
        }

        if (key_exists($key, self::$settings)){
            return self::$settings[$key];
        }

        return self::$settings;
    }

    public static function getCacheKey(): string
    {
        return AppConfig::getAppCacheKey() . self::TonicsApp_TonicsCloudSettings;
    }

    /**
     * Minimum Available Credit User Can Have
     * @return int
     */
    public static function MinimumCredit(): int
    {
        return 1;
    }

    /**
     * The most days you can have in a month is 744, so, the hourly rate would be based off of that,
     * adjust your monthly prices accordingly.
     */
    public static function TotalMonthlyHours(): int
    {
        return 744;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public static function billingEnabled(): bool
    {
        $billing = TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::EnableBilling);
        if ($billing === '1') {
            return true;
        }

        // by default billing is false whether we have a settings data or not
        return false;

    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public static function insertServerDefaultServices(): void
    {
        $handler = TonicsCloudActivator::getCloudServerHandler(TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::CloudServerIntegrationType));

        $providersToInsert = [
            'provider_name' => $handler->displayName(),
            'provider_perm_name' => $handler->name(),
        ];

        db(onGetDB: function (TonicsQuery $db) use ($handler, &$providersToInsert) {
            $cloudProviderTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_PROVIDER);
            $db->insertOnDuplicate($cloudProviderTable, $providersToInsert, ['provider_name']);

            $providerID = $db->Q()->Select('provider_id')
                ->From($cloudProviderTable)
                ->WhereEquals('provider_perm_name', $handler->name())->FetchFirst()?->provider_id;

            $services = [];
            $cloudServiceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICES);
            foreach ($handler->prices() as $key => $value) {
                $services[] = [
                    'service_name' => $key,
                    'service_description' => $value['description'],
                    'service_provider_id' => $providerID,
                    'monthly_rate' => $value['price']['monthly'],
                    'others' => json_encode($value)
                ];
            }
            $db->Q()->insertOnDuplicate($cloudServiceTable, $services, ['service_description', 'monthly_rate', 'others']);
        });
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