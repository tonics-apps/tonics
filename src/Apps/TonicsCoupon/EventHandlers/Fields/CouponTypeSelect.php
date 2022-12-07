<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCoupon\EventHandlers\Fields;

use App\Apps\TonicsCoupon\Data\CouponData;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Post\Data\PostData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class CouponTypeSelect implements HandlerInterface
{

    /**
     * @param object $event
     * @return void
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('CouponTypeSelect', 'Coupon Type HTML Selection', 'TonicsCoupon',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event){
                return $this->userForm($event, $data);
            },
            handleViewProcessing: function (){}
        );
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @return string
     * @throws \Exception
     */
    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName =  (isset($data->fieldName)) ? $data->fieldName : 'Coupon Type Select';
        $inputName =  (isset($data->inputName)) ? $data->inputName : '';
        $multipleSelection = (isset($data->multipleSelect)) ? $data->multipleSelect : '0';


        if ($multipleSelection === '1') {
            $typeName = <<<HTML
<option value="0">False</option>
<option value="1" selected>True</option>
HTML;
        } else {
            $typeName = <<<HTML
<option value="0" selected>False</option>
<option value="1">True</option>
HTML;
        }

        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $frag .= <<<FORM
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Field Name
            <input id="fieldName-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
    <label class="menu-settings-handle-name" for="inputName-$changeID">Input Name
            <input id="inputName-$changeID" name="inputName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$inputName" placeholder="(Optional) Input Name">
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="multipleSelect-$changeID">Multiple Selection ?
     <select name="multipleSelect" class="default-selector mg-b-plus-1" id="multipleSelect-$changeID">
        $typeName
     </select>
    </label>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @return string
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'CouponTypeSelect';
        $slug = $data->field_slug;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';

        $multipleSelection = (isset($data->multipleSelect)) ? $data->multipleSelect : '0';
        $inputName =  (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";
        $multipleAttr = '';
        $selectName = "$inputName";
        $height = '';
        if ($multipleSelection === '1'){
            $multipleAttr = 'multiple';
            $selectName = "{$inputName}[]";
            $height = 'height: 300px;';
        }

        $inputData = $event->getKeyValueInData($data, $selectName);
        $couponData = new CouponData();
        $categories = $couponData->getCouponTypeHTMLSelect($inputData ?: null);

        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $frag .= <<<FORM
<div class="form-group margin-top:0">
    <select style="$height" id="categories" data-widget-select-category="true" $multipleAttr name="$selectName" class="default-selector">
                    <option value="" style="color: #004085">-Parent Category-</option>
                    $categories
    </select>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }
}