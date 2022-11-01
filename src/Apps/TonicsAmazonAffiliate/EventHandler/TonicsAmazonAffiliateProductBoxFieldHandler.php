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

class TonicsAmazonAffiliateProductBoxFieldHandler implements FieldTemplateFileInterface
{

    /**
     * @throws \Exception
     */
    public function handleFieldLogic(OnFieldMetaBox $event = null, $fields = null): string
    {
        $asin = '';
        $title = '';
        $description = '';
        $type = 'Horizontal';
        $htmlFrag = '';
        $fieldData = [];
        if (isset($fields[0]->_children)) {
            // dd($fields[0]->_children);
            $tonicsAmazonAffiliateProductBoxAsin = 'tonicsAmazonAffiliateProductBox_asin';
            $tonicsAmazonAffiliateProductBoxTitle = 'tonicsAmazonAffiliateProductBox_title';
            $tonicsAmazonAffiliateProductBoxDescription = 'tonicsAmazonAffiliateProductBox_description';
            $tonicsAmazonAffiliateProductBoxType = 'tonicsAmazonAffiliateProductBox_boxType';

            foreach ($fields[0]->_children as $child) {
                if ($child->field_input_name === $tonicsAmazonAffiliateProductBoxAsin) {
                    $asin = $child->field_data[$tonicsAmazonAffiliateProductBoxAsin] ?? '';
                }

                if ($child->field_input_name === $tonicsAmazonAffiliateProductBoxTitle) {
                    $title = $child->field_data[$tonicsAmazonAffiliateProductBoxTitle] ?? '';
                }

                if ($child->field_input_name === $tonicsAmazonAffiliateProductBoxDescription) {
                    $description = $child->field_data[$tonicsAmazonAffiliateProductBoxDescription] ?? '';
                }

                if ($child->field_input_name === $tonicsAmazonAffiliateProductBoxType) {
                    $type = $child->field_data[$tonicsAmazonAffiliateProductBoxType] ?? '';
                }
            }

            /** @var TonicsAmazonAffiliateController $tonicsAmazonAffiliateController */
            $tonicsAmazonAffiliateController = container()->get(TonicsAmazonAffiliateController::class);
            $TAATable = TonicsAmazonAffiliateActivator::tableName();
            $asin = trim($asin);
            if (!empty($asin)) {
                $itemIds = [$asin];
                $asinDataFromDB = db()->Select('*')->From($TAATable)->WhereEquals('asin', $asin)->FetchFirst();
                if (empty($asinDataFromDB)) {
                    $responseList = $tonicsAmazonAffiliateController->searchAmazonByASIN($itemIds);
                    if (isset($responseList[$asin])) {
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

                if (isset($fieldData[$asin])) {
                    $fieldData = $fieldData[$asin];
                    $title = (empty($title)) ? $fieldData['TITLE'] : $title;
                    $description = (empty($description)) ? $fieldData['DESCRIPTION'] : $description;
                    $type = trim(strtoupper($type));

                    if ($type === 'VERTICAL') {
                        $htmlFrag = <<<HTML
<ul style="list-style:none;">
    <li tabindex="0" class="owl width:100% padding:default color:black bg:white-one border-width:default border:black position:relative">
              <div style="border-top-right-radius: 10px;" class="bg:pure-black color:white padding:small tonics-amazon-affiliate-title">$title</div>
                <div class="tonics-amazon-affiliate-image">
        <a href="{$fieldData['URL']}" target="_top">
        {$fieldData['IMAGE']}
        </a>
      </div>
    <div class="tonics-amazon-affiliate-description">
    $description
    </div>
    
        <div class="tonics-amazon-affiliate-footer d:flex align-items:center flex-d:column">
            <div class="tonics-amazon-affiliate-pricing">
                <span style="font-size: 125%;" class="tonics-amazon-affiliate-price-current">{$fieldData['PRICE']}</span>
            </div>
            {$fieldData['BUTTON']}
        </div>
    </li>
</ul>
HTML;
                    } elseif ($type === 'HORIZONTAL') {
                        $htmlFrag = <<<HTML
<ul style="list-style:none;">
<style>

    @media (min-width: 1200px) {
      .tonics-amazon-affiliate-list {
        display: grid;
        grid-template-areas:
            "taf-title taf-title"
            "taf-image taf-description"
            "taf-footer taf-footer";
        grid-template-columns: repeat(auto-fit,minmax(min(100%,400px),1fr));
        }
    }
     
    .tonics-amazon-affiliate-title {
        grid-area: taf-title;
    }
    
    .tonics-amazon-affiliate-image {
        grid-area: taf-image;
    }
    
    .tonics-amazon-affiliate-description {
        grid-area: taf-description;
    }
    
    .tonics-amazon-affiliate-footer {
        grid-area: taf-footer;
    }
</style>
    <li tabindex="0" class="owl tonics-amazon-affiliate-list width:100% padding:default color:black bg:white-one border-width:default border:black position:relative">
              <div style="border-top-right-radius: 10px;" class="bg:pure-black color:white padding:small tonics-amazon-affiliate-title">$title</div>
                <div class="tonics-amazon-affiliate-image">
        <a href="{$fieldData['URL']}" target="_top">
        {$fieldData['IMAGE']}
        </a>
      </div>
    <div class="tonics-amazon-affiliate-description">
    $description
    </div>
    
        <div class="tonics-amazon-affiliate-footer d:flex align-items:center flex-d:column">
            <div class="tonics-amazon-affiliate-pricing">
                <span style="font-size: 125%;" class="tonics-amazon-affiliate-price-current">{$fieldData['PRICE']}</span>
            </div>
            {$fieldData['BUTTON']}
        </div>
    </li>
</ul>
HTML;
                    }
                }
            }

        }

        return $htmlFrag;

    }

    public function name(): string
    {
        return 'Tonics Amazon Affiliate Product Box';
    }

    public function canPreSaveFieldLogic(): bool
    {
        return true;
    }

    public function fieldSlug(): string
    {
        return 'app-tonicsamazonaffiliate-product-box';
    }
}