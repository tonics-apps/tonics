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
        $couponItemImport = new CouponItemImport();
        $couponItemImport->setJobName('CouponItemImport');
        $couponItemImport->setJobStatus(Job::JobStatus_InProgress);
        $job = \job();
        $parentData = null;
        $job->enqueue($couponItemImport,
            afterEnqueue: function ($enqueueData) use (&$parentData) {
                $parentData = $enqueueData;
            });

        $couponNameField = $settings['app_tonicscoupon_coupon_page_import_mapField_couponName'] ?? 'coupon_name';
        $couponLabelField = $settings['app_tonicscoupon_coupon_page_import_mapField_couponLabel'] ?? 'coupon_label';
        $couponContentField = $settings['app_tonicscoupon_coupon_page_import_mapField_couponContent'] ?? 'coupon_content';
        $couponStatusField = $settings['app_tonicscoupon_coupon_page_import_mapField_couponStatus'] ?? 'coupon_status';
        $couponStatusDefaultToField = $settings['app_tonicscoupon_coupon_page_import_mapField_couponStatusDefaultTo'] ?? 0;
        $couponCreatedAtField = $settings['app_tonicscoupon_coupon_page_import_mapField_couponCreatedDate'] ?? 'created_at';
        $couponExpiredAtField = $settings['app_tonicscoupon_coupon_page_import_mapField_couponExpiredDate'] ?? 'expired_at';
        $couponImageURLField = $settings['app_tonicscoupon_coupon_page_import_mapField_couponImageURL'] ?? 'image_url';
        $couponTypeField = $settings['app_tonicscoupon_coupon_page_import_mapField_couponType'] ?? 'coupon_type';
        $couponTypeDefaultToField = $settings['app_tonicscoupon_coupon_page_import_mapField_couponTypeDefaultTo'] ?? null;

        if ($parentData){
            $items = Items::fromFile($filePath);
            foreach ($items as $item) {
                $couponItemImport->setJobName('CouponItemImport_Child');
                $couponItemImport->setJobStatus(Job::JobStatus_Queued);
                $couponItemImport->setJobParentID($parentData->job_id);
                $couponItemImport->setData($item);
                $job->enqueue($couponItemImport);
            }
        }
    }
}