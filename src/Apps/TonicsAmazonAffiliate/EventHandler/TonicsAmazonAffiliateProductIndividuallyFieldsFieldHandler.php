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

namespace App\Apps\TonicsAmazonAffiliate\EventHandler;

use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item;
use App\Apps\TonicsAmazonAffiliate\Controller\TonicsAmazonAffiliateController;
use App\Apps\TonicsAmazonAffiliate\TonicsAmazonAffiliateActivator;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\FieldTemplateFileInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class TonicsAmazonAffiliateProductIndividuallyFieldsFieldHandler implements FieldTemplateFileInterface
{

    /**
     * @throws \Exception
     */
    public function handleFieldLogic(OnFieldMetaBox $event = null, $fields = null): string
    {
        $asin = '';
        $fieldType = '';
        $fieldData = [];
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
                $fieldType = strtoupper(trim($fieldType));
                $itemIds = [$asin];
                $asinDataFromDB = null;
                db(onGetDB: function (TonicsQuery $db) use ($asin, $TAATable, &$asinDataFromDB){
                    $asinDataFromDB = $db->Select('*')->From($TAATable)->WhereEquals('asin', $asin)->FetchFirst();
                });

                if(empty($asinDataFromDB)){
                    $responseList = $tonicsAmazonAffiliateController->searchAmazonByASIN($itemIds);
                    if (isset($responseList[$asin])){
                        $serialize = serialize($responseList[$asin]);
                        db(onGetDB: function (TonicsQuery $db) use ($serialize, $asin, $TAATable) {
                            $db->InsertOnDuplicate($TAATable,
                                [
                                    'asin' => $asin,
                                    'others' => json_encode(['serialized' => $serialize])

                                ], ['others']);
                        });
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