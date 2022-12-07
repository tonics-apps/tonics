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

use App\Apps\TonicsCoupon\TonicsCouponActivator;
use App\Modules\Core\Library\Tables;

class CouponData extends \App\Modules\Core\Library\AbstractDataLayer
{
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
     * @return string
     */
    public function getCouponTypeTable(): string
    {
        return TonicsCouponActivator::couponTypeTableName();
    }
}