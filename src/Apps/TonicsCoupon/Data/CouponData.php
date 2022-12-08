<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge, 
 * you shouldn't and can't freely copy, modify, merge, 
 * publish, distribute, sublicense, 
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCoupon\Data;

use App\Apps\TonicsCoupon\Events\OnCouponDefaultField;
use App\Apps\TonicsCoupon\Events\OnCouponTypeDefaultField;
use App\Apps\TonicsCoupon\TonicsCouponActivator;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Field\Data\FieldData;

class CouponData extends AbstractDataLayer
{
    use UniqueSlug;
    
    private ?FieldData $fieldData;
    private ?OnCouponDefaultField $onCouponDefaultField;
    private ?OnCouponTypeDefaultField $onCouponTypeDefaultField;

    public function __construct(FieldData $fieldData = null, OnCouponDefaultField $onCouponDefaultField = null, OnCouponTypeDefaultField $onCouponTypeDefaultField = null)
    {
        $this->fieldData = $fieldData;
        $this->onCouponDefaultField = $onCouponDefaultField;
        $this->onCouponTypeDefaultField = $onCouponTypeDefaultField;
    }
    /**
     * @return mixed
     * @throws \Exception
     */
    public function getCouponType(): mixed
    {
        $couponTypeTable = $this->getCouponTypeTable();
        // tcs stands for tonics category system ;)
        return db()->run("
        WITH RECURSIVE coupon_type_recursive AS 
	( SELECT coupon_type_id, coupon_type_parent_id, coupon_type_slug, coupon_type_name, CAST(coupon_type_slug AS VARCHAR (255))
            AS path
      FROM {$couponTypeTable} WHERE coupon_type_parent_id IS NULL
      UNION ALL
      SELECT tcs.coupon_type_id, tcs.coupon_type_parent_id, tcs.coupon_type_slug, tcs.coupon_type_name, CONCAT(path, '/' , tcs.coupon_type_slug)
      FROM coupon_type_recursive as fr JOIN {$couponTypeTable} as tcs ON fr.coupon_type_id = tcs.coupon_type_parent_id
      ) 
     SELECT * FROM coupon_type_recursive;
        ");
    }
    
    /**
     * @param null $currentCatData
     * @return string
     * @throws \Exception
     */
    public function getCouponTypeHTMLSelect($currentCatData = null): string
    {
        $categories = helper()->generateTree(['parent_id' => 'coupon_type_parent_id', 'id' => 'coupon_type_id'], $this->getCouponType());
        $catSelectFrag = '';
        if (count($categories) > 0) {
            foreach ($categories as $category) {
                $catSelectFrag .= $this->getCouponTypeHTMLSelectFrag($category, $currentCatData);
            }
        }

        return $catSelectFrag;
    }

    /**
     * @param $category
     * @param null $currentCatIDS
     * @return string
     * @throws \Exception
     */
    private function getCouponTypeHTMLSelectFrag($category, $currentCatIDS = null): string
    {
        $currentCatIDS = (is_object($currentCatIDS) && property_exists($currentCatIDS, 'coupon_type_parent_id')) ? $currentCatIDS->coupon_type_parent_id : $currentCatIDS;

        if (!is_array($currentCatIDS)){
            $currentCatIDS = [$currentCatIDS];
        }

        $catSelectFrag = '';
        $catID = $category->coupon_type_id;
        if ($category->depth === 0) {
            $catSelectFrag .= <<<CAT
    <option data-is-parent="yes" data-depth="$category->depth"
            data-slug="$category->coupon_type_slug" data-path="/$category->path/" value="$catID"
CAT;
            foreach ($currentCatIDS as $currentCatID){
                if ($currentCatID == $category->coupon_type_id) {
                    $catSelectFrag .= 'selected';
                }
            }

            $catSelectFrag .= ">" . $category->coupon_type_name;
        } else {
            $catSelectFrag .= <<<CAT
    <option data-slug="$category->coupon_type_slug" data-depth="$category->depth" data-path="/$category->path/"
            value="$catID"
CAT;
            foreach ($currentCatIDS as $currentCatID){
                if ($currentCatID == $category->coupon_type_id) {
                    $catSelectFrag .= 'selected';
                }
            }

            $catSelectFrag .= ">" . str_repeat("&nbsp;&nbsp;&nbsp;", $category->depth + 1);
            $catSelectFrag .= $category->coupon_type_name;
        }
        $catSelectFrag .= "</option>";

        if (isset($category->_children)) {
            foreach ($category->_children as $catChildren) {
                $catSelectFrag .= $this->getCouponTypeHTMLSelectFrag($catChildren, $currentCatIDS);
            }
        }

        return $catSelectFrag;

    }

    /**
     * @throws \Exception
     */
    public function createCouponType(array $ignore = [], bool $prepareFieldSettings = true): array
    {
        $slug = $this->generateUniqueSlug($this->getCouponTypeTable(),
            'coupon_type_slug',
            helper()->slug(input()->fromPost()->retrieve('coupon_type_slug')));

        $couponType = [];
        $couponTypeCols = array_flip($this->getCouponTypeColumns());
        if (input()->fromPost()->hasValue('coupon_type_parent_id')) {
            $couponType['coupon_type_parent_id'] = input()->fromPost()->retrieve('coupon_type_parent_id');
        }

        foreach (input()->fromPost()->all() as $inputKey => $inputValue) {
            if (key_exists($inputKey, $couponTypeCols) && input()->fromPost()->has($inputKey)) {
                if ($inputKey === 'coupon_type_parent_id' && empty($inputValue)) {
                    $couponType[$inputKey] = null;
                    continue;
                }

                if ($inputKey === 'created_at') {
                    $couponType[$inputKey] = helper()->date(datetime: $inputValue);
                    continue;
                }

                if ($inputKey === 'coupon_type_slug') {
                    $couponType[$inputKey] = $slug;
                    continue;
                }
                $couponType[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $couponType);
        if (!empty($ignores)) {
            foreach ($ignores as $v) {
                unset($couponType[$v]);
            }
        }

        if ($prepareFieldSettings){
            return $this->getFieldData()->prepareFieldSettingsDataForCreateOrUpdate($couponType, 'coupon_type_name', 'coupon_type_content');
        }

        return $couponType;
    }

    /**
     * @throws \Exception
     */
    public function createPost(array $ignore = [], bool $prepareFieldSettings = true): array
    {
        $slug = $this->generateUniqueSlug($this->getCouponTable(),
            'coupon_slug', helper()->slug(input()->fromPost()->retrieve('coupon_slug')));

        $coupon = [];
        $postColumns = array_flip($this->getCouponColumns());
        foreach (input()->fromPost()->all() as $inputKey => $inputValue) {
            if (key_exists($inputKey, $postColumns) && input()->fromPost()->has($inputKey)) {

                if ($inputKey === 'created_at') {
                    $coupon[$inputKey] = helper()->date(datetime: $inputValue);
                    continue;
                }

                if ($inputKey === 'coupon_slug') {
                    $coupon[$inputKey] = $slug;
                    continue;
                }
                $coupon[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $coupon);
        if (!empty($ignores)) {
            foreach ($ignores as $v) {
                unset($coupon[$v]);
            }
        }

        if ($prepareFieldSettings){
            return $this->getFieldData()->prepareFieldSettingsDataForCreateOrUpdate($coupon, 'coupon_name', 'coupon_content');
        }

        return $coupon;
    }

    /**
     * @return string
     */
    public function getCouponTable(): string
    {
        return TonicsCouponActivator::couponTableName();
    }
    
    /**
     * @return string
     */
    public function getCouponTypeTable(): string
    {
        return TonicsCouponActivator::couponTypeTableName();
    }

    /**
     * @return string
     */
    public function getCouponToTypeTable(): string
    {
        return TonicsCouponActivator::couponToTypeTableName();
    }

    public function getCouponTypeColumns(): array
    {
        return TonicsCouponActivator::$TABLES[TonicsCouponActivator::COUPON_TYPE];
    }

    public function getCouponColumns(): array
    {
        return TonicsCouponActivator::$TABLES[TonicsCouponActivator::COUPON];
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

    /**
     * @return OnCouponDefaultField|null
     */
    public function getOnCouponDefaultField(): ?OnCouponDefaultField
    {
        return $this->onCouponDefaultField;
    }

    /**
     * @param OnCouponDefaultField|null $onCouponDefaultField
     */
    public function setOnCouponDefaultField(?OnCouponDefaultField $onCouponDefaultField): void
    {
        $this->onCouponDefaultField = $onCouponDefaultField;
    }

    /**
     * @return OnCouponTypeDefaultField|null
     */
    public function getOnCouponTypeDefaultField(): ?OnCouponTypeDefaultField
    {
        return $this->onCouponTypeDefaultField;
    }

    /**
     * @param OnCouponTypeDefaultField|null $onCouponTypeDefaultField
     */
    public function setOnCouponTypeDefaultField(?OnCouponTypeDefaultField $onCouponTypeDefaultField): void
    {
        $this->onCouponTypeDefaultField = $onCouponTypeDefaultField;
    }
}