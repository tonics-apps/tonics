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

namespace App\Modules\Media\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Media\FileManager\LocalDriver;
use Devsrealm\TonicsFileManager\StorageDriver\StorageDriver;
use Devsrealm\TonicsFileManager\StorageDriver\StorageDriverInterface;
use JetBrains\PhpStorm\NoReturn;

class MediaControllers
{
    private LocalDriver $localDriver;
    private StorageDriver $storageDriver;

    /**
     * MediaControllers constructor.
     * @param LocalDriver $localDriver
     * @param StorageDriver $storageDriver
     */
    public function __construct(LocalDriver $localDriver, StorageDriver $storageDriver)
    {
        $this->localDriver = $localDriver;
        $this->storageDriver = $storageDriver;

    }


    /*
    |--------------------------------------------------------------------------
    | MEDIA CONTROLLER WEB METHODS
    |--------------------------------------------------------------------------
    |
    | This below serves as the media web methods, this is would serve as our
    | traditional web request methods
    |
    */

    /**
     * @throws \Exception
     */
    public function showMediaManager()
    {
        view('Modules::Media/Views/file-manager',
            [
                'DropboxDiskDrive' => DriveConfig::getDropBoxKey()
            ]
        );
    }

    /**
     * @param $fileUniqueID
     */
    #[NoReturn] public function serveFiles($fileUniqueID)
    {
        $this->getStorageDriver()->serveFile($fileUniqueID);
    }


    /*
    |--------------------------------------------------------------------------
    | MEDIA CONTROLLER API METHODS
    |--------------------------------------------------------------------------
    |
    | This below serves as the media api methods, this is a bit different from
    | the web methods in that you'll reach them from the api requests.
    |
    */

    /**
     * @throws \Exception
     */
    public function getAppURL()
    {
        response()->onSuccess(data: [], message: AppConfig::getAppUrl());
    }

    /**
     * @throws \Exception
     */
    public function getFiles(): void
    {
        $path = url()->getParam('path');
        $id = url()->getParam('id');

        $list = $this->getStorageDriver()
            ->list($id, $path);

        if ($list === false) {
            response()->onError(404, 'Media Files is Either Empty or Something Went Wrong');
        }

        if (sizeof($list) === 0) {
            $path = ['current_path' => $path ?? 'uploads', 'drive_id' => $id ?? ''];
            response()->onSuccess(data: $list, message: 'Media Files is Empty', more: $path);
        } else {
            $more = ['current_path' => $list['folderPath'], 'drive_id' => $list['folderID'], 'next_page_url' => $list['next_page_url'], 'has_more' => $list['has_more']];
            response()->onSuccess(data: $list['data'], message: 'Media Files Successfully Retrieved', more: $more);
        }
    }


    /**
     * Dangerous as it would re-arrange the drive_unique_id on new added files
     * @throws \Exception
     */
    public function reIndex()
    {
        $reIndex = $this->getStorageDriver()
            ->reIndex();
        if ($reIndex) {
            response()->onSuccess([], message: 'Files Successfully Indexed');
        } else {
            response()->onError(400, message: 'Indexing Failed');
        }
    }


    /**
     * @throws \Exception
     */
    public function searchFiles()
    {
        $path = url()->getParam('path');
        $id = url()->getParam('id');
        $searchInfo = [
            'path' => $path, // path to search
            'id' => $id, // path id
            'query' => url()->getParam('query'), // search query
        ];
        $list = $this->getStorageDriver()->searchFiles($searchInfo);
        if ($list === false) {
            response()->onError(404, 'Error Searching File');
        }
        if (sizeof($list) === 0) {
            response()->onSuccess(data: $list, message: 'No Result Found');
        } else {
            $more = ['current_path' => $list['folderPath'], 'drive_id' => $list['folderID'], 'next_page_url' => $list['next_page_url'], 'has_more' => $list['has_more']];
            response()->onSuccess(data: $list['data'], message: 'Media Files Successfully Retrieved', more: $more);
        }
    }


    /**
     * @throws \Exception
     */
    public function getPreflightFile()
    {
        $keys = request()->getAPIHeaderKey(['Chunkstosend', 'Uploadto', 'Filetype', 'Filename', 'Totalblobsize', 'Byteperchunk']);
        if ($keys) {
            if ($data = $this->getStorageDriver()->preFlight($keys)) {
                $preFlight = $data['preflightData'];
                response()->onSuccess($preFlight, 'Pre-flight Data Successful Fetched', more: ['filename' => $data['filename']]);
            }
        } else {
            response()->onError(400, 'Failed Fetching Pre-flight Data');
        }
    }

    /**
     * @throws \Exception
     */
    public function createFile()
    {
        $requestBody = request()->getEntityBody();
        if ($createFile = $this->getStorageDriver()->create($requestBody)) {
            response()->onSuccess($createFile, 'File successfully Updated');
        }
        response()->onError(400, 'Cant Upload/Update File');
    }

    /**
     * @throws \Exception
     */
    public function cancelFileCreate()
    {
        $requestBody = request()->getEntityBody();
        if ($createFile = $this->getStorageDriver()->cancelFileCreate($requestBody)) {
            response()->onSuccess($createFile, 'File Upload Successfully Cancelled');
        }
        response()->onError(400, 'Failed To Cancel File Upload');
    }

    /**
     * @throws \Exception
     */
    public function createFolder()
    {
        $requestBody = request()->getEntityBody();
        if ($createFile = $this->getStorageDriver()->createFolder($requestBody)) {
            response()->onSuccess($createFile, 'Folder Successfully Created');
        }
        response()->onError(400, 'Failed To Create Folder');
    }

    /**
     * @throws \Exception
     */
    public function renameFile()
    {

        $requestBody = json_decode(request()->getEntityBody());
        $renamedFile = $this->getStorageDriver()->rename($requestBody);
        if ($renamedFile) {
            response()->onSuccess($renamedFile, 'File successfully renamed');
        }
        response()->onError(400, 'Failed To Rename File');
    }

    /**
     * @throws \Exception
     */
    public function moveFiles()
    {
        $requestBody = json_decode(request()->getEntityBody());

        if ($this->getStorageDriver()->moveFiles($requestBody)) {
            response()->onSuccess('', 'Files successfully Moved To Destination');
        }

        response()->onError(400, 'Failed To Move Files');
    }

    /**
     * @throws \Exception
     */
    public function deleteFile()
    {
        $requestBody = json_decode(request()->getEntityBody());
        if ($requestBody) {
            $deleteFile = $this->getStorageDriver()->clear($requestBody);
            if ($deleteFile === false) {
                response()->onError(400, 'Error Deleting File');
            }

            if ($deleteFile> 1) {
                response()->onSuccess($deleteFile, 'Files successfully Deleted');
            } else {
                response()->onSuccess($deleteFile, 'File successfully Deleted');
            }

        }
        response()->onError(400, 'Error Deleting File');
    }

    /**
     * @return StorageDriverInterface
     */
    public function getStorageDriver(): StorageDriverInterface
    {
        return $this->storageDriver
            ->setStorageDriver($this->getLocalDriver())
            ->getStorageDriver();
    }

    /**
     * @return LocalDriver
     */
    public function getLocalDriver(): LocalDriver
    {
        return $this->localDriver;
    }

}