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

require dirname(__FILE__, 2) . '/Library/AmazonPAA/paapi5/vendor/autoload.php';

class TonicsAmazonAffiliateController
{
    private string $accessKey = '';
    private string $secretKey = '';
    private string $partnerTag = '';
    
    private ?Configuration $configuration = null;

    public function __construct()
    {
        $this->accessKey = 'AKIAJB5WXDLIJNIYHLWQ';
        $this->secretKey = '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';
        $this->partnerTag = 'exclu25401-20';

        $this->configuration = new Configuration();

        /*
         * Add your credentials
         */
        # Please add your access key here
        $this->configuration->setAccessKey($this->accessKey);
        # Please add your secret key here
        $this->configuration->setSecretKey($this->secretKey);

        # Please add your partner tag (store/tracking id) here
        $partnerTag = $this->partnerTag;

        /*
         * PAAPI host and region to which you want to send request
         * For more details refer:
         * https://webservices.amazon.com/paapi5/documentation/common-request-parameters.html#host-and-region
         */
        $this->configuration->setHost('webservices.amazon.com');
        $this->configuration->setRegion('us-east-1');

        $apiInstance = new DefaultApi(
        /*
         * If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
         * This is optional, `GuzzleHttp\Client` will be used as default.
         */
            new \GuzzleHttp\Client(),
            $this->configuration
        );

        # Request initialization

        # Choose item id(s)
        $itemIds = ["059035342X", "B00X4WHP55", "1401263119", "0136816487", 'B01EJ5UV3C'];

        /*
         * Choose resources you want from GetItemsResource enum
         * For more details, refer: https://webservices.amazon.com/paapi5/documentation/get-items.html#resources-parameter
         */
        $resources = [
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

        # Forming the request
        $getItemsRequest = new GetItemsRequest();
        $getItemsRequest->setItemIds($itemIds);
        $getItemsRequest->setPartnerTag($partnerTag);
        $getItemsRequest->setPartnerType(PartnerType::ASSOCIATES);
        $getItemsRequest->setResources($resources);

        # Validating request
        $invalidPropertyList = @$getItemsRequest->listInvalidProperties();
        $length = count($invalidPropertyList);
        if ($length > 0) {
            echo "Error forming the request", PHP_EOL;
            foreach ($invalidPropertyList as $invalidProperty) {
                echo $invalidProperty, PHP_EOL;
            }
            return;
        }

        # Sending the request
        try {
            $getItemsResponse = @$apiInstance->getItems($getItemsRequest);
           // dd($getItemsResponse);

            // echo 'API called successfully', PHP_EOL;
           // echo 'Complete Response: ', $getItemsResponse, PHP_EOL;

            # Parsing the response
            if ($getItemsResponse->getItemsResult() !== null) {
                if ($getItemsResponse->getItemsResult()->getItems() !== null) {
                    $responseList = $this->parseResponse($getItemsResponse->getItemsResult()->getItems());
                    dd($responseList, $itemIds);

                    foreach ($itemIds as $itemId) {
                        echo 'Printing information about the itemId: ', $itemId, PHP_EOL;
                        $item = $responseList[$itemId];
                        if ($item !== null) {
                            if ($item->getASIN()) {
                                echo 'ASIN: ', $item->getASIN(), PHP_EOL;
                            }
                            if ($item->getItemInfo() !== null and $item->getItemInfo()->getTitle() !== null
                                and $item->getItemInfo()->getTitle()->getDisplayValue() !== null) {
                                echo 'Title: ', $item->getItemInfo()->getTitle()->getDisplayValue(), PHP_EOL;
                            }
                            if ($item->getDetailPageURL() !== null) {
                                echo 'Detail Page URL: ', $item->getDetailPageURL(), PHP_EOL;
                            }
                            if ($item->getOffers() !== null and
                                $item->getOffers()->getListings() !== null
                                and $item->getOffers()->getListings()[0]->getPrice() !== null
                                and $item->getOffers()->getListings()[0]->getPrice()->getDisplayAmount() !== null) {
                                echo 'Buying price: ', $item->getOffers()->getListings()[0]->getPrice()
                                    ->getDisplayAmount(), PHP_EOL;
                            }
                        } else {
                            echo "Item not found, check errors", PHP_EOL;
                        }
                    }
                }
            }
            if ($getItemsResponse->getErrors() !== null) {
                // Error Code and Error Message
                // $getItemsResponse->getErrors()[0]->getCode()
                // $getItemsResponse->getErrors()[0]->getMessage()
            }
        } catch (ApiException $exception) {
            // Error Code and Error Message
            // $exception->getCode()
            // $exception->getMessage()
            if ($exception->getResponseObject() instanceof ProductAdvertisingAPIClientException) {
                $errors = $exception->getResponseObject()->getErrors();
                // Multiple Errors
                foreach ($errors as $error) {
                    // $error->getCode()
                    // $error->getMessage()
                }
            } else {
                // Error Response Body
                // $exception->getResponseBody()
            }
        } catch (\Exception $exception) {
            // Error Regular PHP Exception
            // $exception->getMessage()
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
}