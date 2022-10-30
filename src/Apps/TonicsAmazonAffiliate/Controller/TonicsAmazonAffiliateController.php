<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsAmazonAffiliate\Controller;

use Amazon\ProductAdvertisingAPI\v1\ApiException;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\api\DefaultApi;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsRequest;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsResource;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ProductAdvertisingAPIClientException;
use Amazon\ProductAdvertisingAPI\v1\Configuration;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Field\Data\FieldData;

require dirname(__FILE__, 2) . '/Library/AmazonPAA/paapi5/vendor/autoload.php';

class TonicsAmazonAffiliateController
{
    private string $accessKey = '';
    private string $secretKey = '';
    private string $partnerTag = '';

    const CACHE_KEY = 'TonicsPlugin_TonicsAmazonAffiliateSettings';

    const SETTINGS_ACCESS_KEY_INPUT_NAME = 'tonicsAmazonAffiliateSettings_apiKey';
    const SETTINGS_SECRET_KEY_INPUT_NAME = 'tonicsAmazonAffiliateSettings_apiSecret';
    const SETTINGS_PARTNER_TAG_INPUT_NAME = 'tonicsAmazonAffiliateSettings_partnerTag';
    const SETTINGS_REGION_INPUT_NAME = 'tonicsAmazonAffiliateSettings_region';

    private ?Configuration $configuration;
    private ?FieldData $fieldData;

    public function __construct(FieldData $fieldData = null)
    {
        $this->fieldData = $fieldData;
        $this->searchAmazonByASIN(['B07XF8XG45']);
    }

    /**
     * @param array $settings
     * @return Configuration|null
     * @throws \Exception
     */
    public function getAmazonConfiguration(array $settings = []): ?Configuration
    {
        if (empty($settings)){
            $settings = self::getSettingsData();
        }

        $this->accessKey = $settings[self::SETTINGS_ACCESS_KEY_INPUT_NAME] ?? '';
        $this->secretKey = $settings[self::SETTINGS_SECRET_KEY_INPUT_NAME] ?? '';
        $this->partnerTag = $settings[self::SETTINGS_PARTNER_TAG_INPUT_NAME] ?? '';
        $region = $settings[self::SETTINGS_REGION_INPUT_NAME] ?? 'USA';
        $regionKey = strtoupper(trim($region));

        $regionData = [
            'AUSTRALIA' => [
                'HOST' => 'webservices.amazon.com.au',
                'REGION' => 'us-west-2',
            ],
            'BELGIUM' => [
                'HOST' => 'webservices.amazon.com.be',
                'REGION' => 'eu-west-1',
            ],
            'BRAZIL' => [
                'HOST' => 'webservices.amazon.com.be',
                'REGION' => 'eu-west-1',
            ],
            'CANADA' => [
                'HOST' => 'webservices.amazon.ca',
                'REGION' => 'us-east-1',
            ],
            'EGYPT' => [
                'HOST' => 'webservices.amazon.eg',
                'REGION' => 'eu-west-1',
            ],
            'FRANCE' => [
                'HOST' => 'webservices.amazon.fr',
                'REGION' => 'eu-west-1',
            ],
            'GERMANY' => [
                'HOST' => 'webservices.amazon.de',
                'REGION' => 'eu-west-1',
            ],
            'INDIA' => [
                'HOST' => 'webservices.amazon.in',
                'REGION' => 'eu-west-1',
            ],
            'ITALY' => [
                'HOST' => 'webservices.amazon.it',
                'REGION' => 'eu-west-1',
            ],
            'JAPAN' => [
                'HOST' => 'webservices.amazon.jp',
                'REGION' => 'us-west-2',
            ],
            'MEXICO' => [
                'HOST' => 'webservices.amazon.com.mx',
                'REGION' => 'us-east-1',
            ],
            'NETHERLANDS' => [
                'HOST' => 'webservices.amazon.nl',
                'REGION' => 'eu-west-1',
            ],
            'POLAND' => [
                'HOST' => 'webservices.amazon.pl',
                'REGION' => 'eu-west-1',
            ],
            'SINGAPORE' => [
                'HOST' => 'webservices.amazon.sg',
                'REGION' => 'us-west-2',
            ],
            'SAUDI ARABIA' => [
                'HOST' => 'webservices.amazon.sa',
                'REGION' => 'eu-west-1',
            ],
            'SPAIN' => [
                'HOST' => 'webservices.amazon.sa',
                'REGION' => 'eu-west-1',
            ],
            'SWEDEN' => [
                'HOST' => 'webservices.amazon.se',
                'REGION' => 'eu-west-1',
            ],
            'TURKEY' => [
                'HOST' => 'webservices.amazon.tr',
                'REGION' => 'eu-west-1',
            ],
            'UAE' => [
                'HOST' => 'webservices.amazon.ae',
                'REGION' => 'eu-west-1',
            ],
            'UK' => [
                'HOST' => 'webservices.amazon.co.uk',
                'REGION' => 'eu-west-1',
            ],
            'USA' => [
                'HOST' => 'webservices.amazon.com',
                'REGION' => 'us-east-1',
            ],
        ];
        $region = $regionData[$regionKey] ?? $regionData['USA'];

        $this->configuration = new Configuration();

        /*
         * Add your credentials
         */
        # Please add your access key here
        $this->configuration->setAccessKey($this->accessKey);
        # Please add your secret key here
        $this->configuration->setSecretKey($this->secretKey);

        $this->configuration->setHost($region['HOST']);
        $this->configuration->setRegion($region['REGION']);

        return $this->configuration;
    }

    protected function getAmazonResourceParameter(): array
    {
        /*
         * Choose resources you want from GetItemsResource enum
         * For more details, refer: https://webservices.amazon.com/paapi5/documentation/get-items.html#resources-parameter
         */
        return [
            GetItemsResource::ITEM_INFOTITLE,
            GetItemsResource::ITEM_INFOBY_LINE_INFO,
            GetItemsResource::ITEM_INFOFEATURES,
            GetItemsResource::ITEM_INFOPRODUCT_INFO,
            GetItemsResource::ITEM_INFOTECHNICAL_INFO,
            GetItemsResource::OFFERSLISTINGSDELIVERY_INFOIS_AMAZON_FULFILLED,
            GetItemsResource::OFFERSLISTINGSDELIVERY_INFOIS_PRIME_ELIGIBLE,
            GetItemsResource::OFFERSLISTINGSPRICE,
            GetItemsResource::OFFERSLISTINGSPROMOTIONS,
            GetItemsResource::OFFERSLISTINGSSAVING_BASIS,
            GetItemsResource::CUSTOMER_REVIEWSCOUNT,
            GetItemsResource::CUSTOMER_REVIEWSSTAR_RATING,
            GetItemsResource::IMAGESPRIMARYLARGE,
            GetItemsResource::IMAGESPRIMARYMEDIUM,
            GetItemsResource::IMAGESPRIMARYSMALL,
            GetItemsResource::IMAGESVARIANTSLARGE,
            GetItemsResource::IMAGESVARIANTSMEDIUM,
            GetItemsResource::IMAGESVARIANTSSMALL,
        ];
    }

    /**
     * @throws \Exception
     */
    public function searchAmazonByASIN(array $asinItemIDS)
    {
        $settings = self::getSettingsData();
        $apiInstance = new DefaultApi(
        /*
         * If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
         * This is optional, `GuzzleHttp\Client` will be used as default.
         */
            new \GuzzleHttp\Client(),
            $this->getAmazonConfiguration($settings)
        );

        # Forming the request
        $getItemsRequest = new GetItemsRequest();
        $getItemsRequest->setItemIds($asinItemIDS);
        $getItemsRequest->setPartnerTag($this->partnerTag);
        $getItemsRequest->setPartnerType(PartnerType::ASSOCIATES);
        $getItemsRequest->setResources($this->getAmazonResourceParameter());

        # Validating request
        $invalidPropertyList = @$getItemsRequest->listInvalidProperties();
        $length = count($invalidPropertyList);
        if ($length > 0) {
            // Log..
//            echo "Error forming the request", PHP_EOL;
//            foreach ($invalidPropertyList as $invalidProperty) {
//                echo $invalidProperty, PHP_EOL;
//            }
            return null;
        }

        # Sending the request
        try {
            $getItemsResponse = @$apiInstance->getItems($getItemsRequest);
            # Parsing the response
            if ($getItemsResponse->getItemsResult() !== null) {
                if ($getItemsResponse->getItemsResult()->getItems() !== null) {
                    return $this->parseResponse($getItemsResponse->getItemsResult()->getItems());
                }
            }
            if ($getItemsResponse->getErrors() !== null) {
                // Error Code and Error Message
                // $getItemsResponse->getErrors()[0]->getCode()
                // $getItemsResponse->getErrors()[0]->getMessage()
                // Log..
                return null;
            }
        } catch (ApiException $exception) {
            // Error Code and Error Message
            // Log..
        } catch (\Exception $exception) {
            // Error Regular PHP Exception
            // Log..
        }

        return null;
    }



    /**
     * @throws \Exception
     */
    public function edit(): void
    {
        $fieldItems = $this->getFieldData()->generateFieldWithFieldSlug(
            ['app-tonicsamazonaffiliate-settings'],
            $this->getSettingsData()
        )->getHTMLFrag();

        view('Apps::TonicsAmazonAffiliate/Views/settings', [
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
            redirect(route('tonicsAmazonAffiliate.settings'));
        }catch (\Exception){
            session()->flash(['An Error Occurred Saving Settings'], $_POST);
            redirect(route('tonicsAmazonAffiliate.settings'));
        }
    }

    /**
     * Returns the array of items mapped to ASIN
     *
     * @param array $items Items value.
     * @return array of \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item mapped to ASIN.
     */
    protected function parseResponse(array $items): array
    {
        $mappedResponse = [];
        foreach ($items as $item) {
            $mappedResponse[$item->getASIN()] = $item;
        }
        return $mappedResponse;
    }

    /**
     * @return FieldData|null
     */
    public function getFieldData(): ?FieldData
    {
        return $this->fieldData;
    }

    public static function getCacheKey(): string
    {
        return AppConfig::getAppCacheKey() . self::CACHE_KEY;
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
}