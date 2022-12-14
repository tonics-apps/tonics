<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCoupon\Jobs;

use App\Apps\TonicsCoupon\TonicsCouponActivator;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use JsonMachine\Exception\InvalidArgumentException;
use JsonMachine\Items;

class CouponFileImporter extends AbstractJobInterface implements JobHandlerInterface
{

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        if(isset($this->getData()->fileInfo) && isset($this->getData()->settings)){
            $dataFileInfo = $this->getData()->fileInfo;
            if (isset($dataFileInfo->fullFilePath) && helper()->fileExists($dataFileInfo->fullFilePath)){
                $couponJsonFilePath = $dataFileInfo->fullFilePath;
                $this->handleFileImporting($couponJsonFilePath, $this->getData()->settings);
                return;
            }
        }

        throw new \Exception("No FileInfo or Settings Property Found in CouponFileImporter Data");
    }

    /**
     * @param string $filePath
     * @param $settings
     * @return void
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    protected function handleFileImporting(string $filePath, $settings): void
    {
        $couponItemImport = container()->get(CouponItemImport::class);
        $couponItemImport->setJobName('CouponItemImport');
        $couponItemImport->setJobStatus(Job::JobStatus_InProgress);
        $job = \job();
        $parentData = null;
        $job->enqueue($couponItemImport,
            afterEnqueue: function ($enqueueData) use (&$parentData) {
                $parentData = $enqueueData;
            });

        $couponNameField = $settings->app_tonicscoupon_coupon_page_import_mapField_couponName ?? 'coupon_name';
        $couponLabelField = $settings->app_tonicscoupon_coupon_page_import_mapField_couponLabel ?? 'coupon_label';
        $couponContentField = $settings->app_tonicscoupon_coupon_page_import_mapField_couponContent ?? 'coupon_content';
        $couponStatusField = $settings->app_tonicscoupon_coupon_page_import_mapField_couponStatus ?? 'coupon_status';
        $couponURLField = $settings->app_tonicscoupon_coupon_page_import_mapField_couponURL ?? 'coupon_url';
        $couponStatusDefaultToField = $settings->app_tonicscoupon_coupon_page_import_mapField_couponStatusDefaultTo ?? 0;
        $couponCreatedAtField = $settings->app_tonicscoupon_coupon_page_import_mapField_couponCreatedDate ?? 'created_at';
        $couponExpiredAtField = $settings->app_tonicscoupon_coupon_page_import_mapField_couponExpiredDate ?? 'expired_at';
        $couponImageURLField = $settings->app_tonicscoupon_coupon_page_import_mapField_couponImageURL ?? 'image_url';
        $couponTypeField = $settings->app_tonicscoupon_coupon_page_import_mapField_couponType ?? 'coupon_type';
        $couponTypeDefaultToField = $settings->app_tonicscoupon_coupon_page_import_mapField_couponTypeDefaultTo ?? null;
        $couponTypeUserID = $settings->app_tonicscoupon_coupon_page_import_mapField_couponTypeUserID ?? null;

        $helper = helper();
        if ($parentData){
            $items = Items::fromFile($filePath);
            foreach ($items as $item) {
                $couponItemImport->setJobName('CouponItemImport_Child');
                $couponItemImport->setJobStatus(Job::JobStatus_Queued);
                $couponItemImport->setJobParentID($parentData->job_id);
                $newItem = [];
                if (isset($item->{$couponNameField})){
                    $newItem['coupon_name'] = $item->{$couponNameField};
                    $newItem['coupon_slug'] = $helper->slug($item->{$couponNameField});
                    $newItem['user_id'] = $couponTypeUserID;

                    if (isset($item->{$couponLabelField})){
                        $newItem['coupon_label'] = $item->{$couponLabelField};
                    }
                    if (isset($item->{$couponContentField})){
                        $newItem['coupon_content'] = $item->{$couponContentField};
                    }
                    if (isset($item->{$couponStatusField})){
                        $newItem['coupon_status'] = $item->{$couponStatusField};
                    } else {
                        $newItem['coupon_status'] = $couponStatusDefaultToField;
                    }
                    if (isset($item->{$couponURLField})){
                        $newItem['coupon_url'] = $item->{$couponURLField};
                    }
                    if (isset($item->{$couponCreatedAtField})){
                        $newItem['created_at'] = $item->{$couponCreatedAtField};
                    } else {
                        $newItem['created_at'] = helper()->date();
                    }

                    if (isset($item->{$couponExpiredAtField})){
                        $newItem['expired_at'] = $item->{$couponExpiredAtField};
                    }

                    if (isset($item->{$couponImageURLField})){
                        $newItem['image_url'] = $item->{$couponImageURLField};
                    }

                    if (isset($item->{$couponTypeField})){
                        $newItem[$couponTypeField] = $item->{$couponTypeField};
                        $fkCouponTypeID = db()->Select('coupon_type_id')->From(TonicsCouponActivator::couponTypeTableName())
                            ->WhereEquals('coupon_type_slug', $item->{$couponTypeField})
                            ->OrWhereEquals('coupon_type_name', $item->{$couponTypeField})
                            ->FetchFirst();
                        if (isset($fkCouponTypeID->coupon_type_id)){
                            $newItem['fk_coupon_type_id'] = [$fkCouponTypeID->coupon_type_id];
                        }
                    } else {
                        $newItem['fk_coupon_type_id'] = [$couponTypeDefaultToField];
                    }

                    $couponItemImport->setData($newItem);
                    $job->enqueue($couponItemImport);
                }
            }
        }
    }
}