<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\Jobs;

use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use JsonMachine\Exception\InvalidArgumentException;
use JsonMachine\Items;

class TrackFileImporter extends AbstractJobInterface implements JobHandlerInterface
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

        throw new \Exception("No FileInfo or Settings Property Found in TrackFileImporter Data");
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
        $trackItemImport = container()->get(TrackItemImport::class);
        $trackItemImport->setJobName('TrackItemImport');
        $trackItemImport->setJobStatus(Job::JobStatus_InProgress);
        $job = \job();
        $parentData = null;
        $job->enqueue($trackItemImport,
            afterEnqueue: function ($enqueueData) use (&$parentData) {
                $parentData = $enqueueData;
            });
        
        if ($parentData){
            $items = Items::fromFile($filePath);
            foreach ($items as $item) {
                $trackItemImport->setJobName('TrackItemImport_Child');
                $trackItemImport->setJobStatus(Job::JobStatus_Queued);
                $trackItemImport->setJobParentID($parentData->job_id);
                $trackItemImport->setData($item);
                $job->enqueue($trackItemImport);
            }
        }
    }
}