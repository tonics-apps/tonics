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
            $TAATable = TonicsAmazonAffiliateActivator::tableName();
            $asin = trim($asin);
            $fieldData = [];
            if (!empty($asin)){
                $fieldType = strtoupper(trim($fieldType));
                $itemIds = [$asin];
                $asinDataFromDB = db()->Select('*')->From($TAATable)->WhereEquals('asin', $asin)->FetchFirst();
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
                        $fieldData = $tonicsAmazonAffiliateController->collateItems([$item]);
                    }
                } else {
                    $item = unserialize(json_decode($asinDataFromDB->others)->serialized);
                    $fieldData = $tonicsAmazonAffiliateController->collateItems([$item]);
                }
            }
        }

        return $fieldData[$asin][$fieldType] ?? '';
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