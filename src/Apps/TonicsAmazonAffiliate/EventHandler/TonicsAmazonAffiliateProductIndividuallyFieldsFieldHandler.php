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

use App\Apps\TonicsAmazonAffiliate\Controller\TonicsAmazonAffiliateController;
use App\Apps\TonicsAmazonAffiliate\TonicsAmazonAffiliateActivator;
use App\Apps\TonicsToc\Controller\TonicsTocController;
use App\Modules\Core\Configs\FieldConfig;
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
            $TAATable = TonicsAmazonAffiliateActivator::tableName();
            $asin = trim($asin);
            if (!empty($asin)){
                $fieldData = [
                    'TITLE' => null,
                    'DESCRIPTION' => null,
                    'IMAGE' => null,
                    'PRICE' => null,
                    'BUTTON' => null,
                    'LAST UPDATE' => null,
                ];
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

                dd($item);

                if (is_array($responseList)){
                    foreach ($itemIds as $itemId) {
                        $item = $responseList[$itemId];
                        if ($item !== null) {
                            if ($item->getASIN()) {
                                echo 'ASIN: ', $item->getASIN(), PHP_EOL;
                            }
                            if ($item->getItemInfo() !== null && $item->getItemInfo()->getTitle() !== null
                                && $item->getItemInfo()->getTitle()->getDisplayValue() !== null) {
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
        }




        dd($asin, $fieldType);
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