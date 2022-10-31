<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsAmazonAffiliate\EventHandler;

use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item;
use App\Apps\TonicsAmazonAffiliate\Controller\TonicsAmazonAffiliateController;
use App\Apps\TonicsAmazonAffiliate\TonicsAmazonAffiliateActivator;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\FieldTemplateFileInterface;

class TonicsAmazonAffiliateProductIndividuallyFieldsFieldHandler implements FieldTemplateFileInterface
{

    /**
     * @throws \Exception
     */
    public function handleFieldLogic(OnFieldMetaBox $event = null, $fields = null): string
    {
        $asin = '';
        $fieldType = '';
        if (isset($fields[0]->_children)){
            $tonicsAmazonAffiliateProductIndividualAsin = 'tonicsAmazonAffiliateProductIndividual_asin';
            $tonicsAmazonAffiliateProductIndividualFieldType = 'tonicsAmazonAffiliateProductIndividual_fieldType';

            foreach ($fields[0]->_children as $child){
                if ($child->field_input_name === $tonicsAmazonAffiliateProductIndividualAsin){
                    $asin = $child->field_data[$tonicsAmazonAffiliateProductIndividualAsin] ?? '';
                }

                if ($child->field_input_name === $tonicsAmazonAffiliateProductIndividualFieldType){
                    $fieldType = $child->field_data[$tonicsAmazonAffiliateProductIndividualFieldType] ?? '';
                }
            }

            /** @var TonicsAmazonAffiliateController $tonicsAmazonAffiliateController */
            $tonicsAmazonAffiliateController = container()->get(TonicsAmazonAffiliateController::class);
            $settings = $tonicsAmazonAffiliateController::getSettingsData();
            $getOnAmazon = $settings['tonicsAmazonAffiliateSettings_buttonText'] ?? '';

            $TAATable = TonicsAmazonAffiliateActivator::tableName();
            $asin = trim($asin);
            if (!empty($asin)){
                $fieldType = strtoupper(trim($fieldType));
                $itemIds = [$asin];
                $asinDataFromDB = db()->Select('*')->From($TAATable)->WhereEquals('asin', $asin)->FetchFirst();
                $item = null;
                if(empty($asinDataFromDB)){
                    $responseList = $tonicsAmazonAffiliateController->searchAmazonByASIN($itemIds);
                    if (isset($responseList[$asin])){
                        $serialize = serialize($responseList[$asin]);
                        db()->InsertOnDuplicate($TAATable,
                            [
                                'asin' => $asin,
                                'others' => json_encode(['serialized' => $serialize])

                            ], ['others']);
                        $item = $responseList[$asin];
                    }
                } else {
                    $item = unserialize(json_decode($asinDataFromDB->others)->serialized);
                }

                if ($item instanceof Item){
                    $title = $item->getItemInfo()?->getTitle()?->getDisplayValue();
                    $imageURL = $item->getImages()?->getPrimary()?->getLarge()->getURL();
                    $height = $item->getImages()?->getPrimary()?->getLarge()->getHeight();
                    $width = $item->getImages()?->getPrimary()?->getLarge()->getWidth();
                    $imageSrc = '';
                    $button = '';
                    $detailPageURL = $item?->getDetailPageURL();
                    if (!empty($detailPageURL)){
                        $button = <<<BUTTON
<a class="text-align:center bg:transparent border:none bg:amazon-orange color:black border-width:default border:black padding:small
                    margin-top:0 cursor:pointer button:box-shadow-variant-1" href="$detailPageURL" title="$getOnAmazon" 
                    target="_blank" rel="nofollow noopener sponsored">$getOnAmazon</a>
BUTTON;
                    }

                    if (!empty($imageURL)){
                        $imageSrc = <<<IMG
<img src="$imageURL"
alt="$title" title="$title" width="$width" height="$height" loading="lazy" decoding="async">
IMG;
                    }

                    $descriptionItems = $item->getItemInfo()?->getFeatures()?->getDisplayValues();
                    $descriptionFrag = '';
                    if (is_array($descriptionItems)){
                        foreach ($descriptionItems as $descriptionItem){
                            $descriptionFrag .= "<li>" . $descriptionItem . "</li>";
                        }
                    }
                    $descriptionFrag = "<ul>" . $descriptionFrag . "</ul>";

                    $fieldData = [
                        'TITLE' => $title,
                        'DESCRIPTION' => $descriptionFrag,
                        'IMAGE' => $imageSrc,
                        'PRICE' => $item->getOffers()?->getListings()[0]->getPrice()->getDisplayAmount(),
                        'BUTTON' => $button,
                        'URL' => $detailPageURL,
                        'LAST UPDATE' => null,
                    ];

                    return $fieldData[$fieldType] ?? '';
                } else {
                    return '';
                }
            }
        }

        return '';
    }

    public function name(): string
    {
        return 'Tonics Amazon Affiliate Product Individually';
    }

    public function canPreSaveFieldLogic(): bool
    {
        return true;
    }

    public function fieldSlug(): string
    {
        return  'app-tonicsamazonaffiliate-product-individually';
    }
}