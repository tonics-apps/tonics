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

use App\Modules\Core\EventHandlers\JobTransporter\DatabaseJobTransporter;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use JsonMachine\Items;

class CouponFileImporter extends AbstractJobInterface implements JobHandlerInterface
{

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        if (isset($this->getData()->fullFilePath) && helper()->fileExists($this->getData()->fullFilePath)){
            $couponJsonFilePath = $this->getData()->fullFilePath;
            $this->handleFileImporting($couponJsonFilePath);
        }
    }

    /**
     * @param string $filePath
     * @return void
     * @throws \Exception
     */
    protected function handleFileImporting(string $filePath): void
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