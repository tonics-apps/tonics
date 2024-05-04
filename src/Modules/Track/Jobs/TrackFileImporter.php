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
        if(isset($this->getData()->fileInfo)){
            $dataFileInfo = $this->getData()->fileInfo;
            if (isset($dataFileInfo->fullFilePath) && helper()->fileExists($dataFileInfo->fullFilePath)){
                $fileJsonFilePath = $dataFileInfo->fullFilePath;
                $this->handleFileImporting($fileJsonFilePath);
                return;
            }
        }

        if (isset($this->getData()->settings)){
            $this->handleFileImporting(null, $this->getData()->settings);
            return;
        }

        throw new \Exception("No FileInfo or Settings Property Found in TrackFileImporter Data");
    }

    /**
     * @param string|null $filePath
     * @param $settings
     * @return void
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    protected function handleFileImporting(string $filePath = null, $settings = null): void
    {
        /** @var TrackItemImport $trackItemImport */
        $trackItemImport = container()->get(TrackItemImport::class);
        $trackItemImport->setJobName('TrackItemImport');
        $trackItemImport->setJobStatus(Job::JobStatus_InProgress);
        $job = \job();
        $parentData = null;
        $job->enqueue($trackItemImport,
            afterEnqueue: function ($enqueueData) use (&$parentData) {
                $parentData = $enqueueData;
            });

        $helper = helper();
        if ($parentData){
            $items = [];
            if (!empty($settings) && isset($settings->track_page_import_text) && $helper->isJSON($settings->track_page_import_text)){
                $items = json_decode($settings->track_page_import_text);
            } elseif (!empty($filePath)){
                $items = Items::fromFile($filePath);
            }

            foreach ($items as $item) {
                $trackItemImport->setJobName('TrackItemImport_Child');
                $trackItemImport->setJobStatus(Job::JobStatus_Queued);
                $trackItemImport->setJobParent($parentData->job_id);
                $trackItemImport->setData($item);
                $job->enqueue($trackItemImport);
            }
        }
    }
}