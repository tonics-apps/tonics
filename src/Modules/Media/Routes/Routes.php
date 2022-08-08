<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Media\Routes;

use App\Modules\Core\Configs\AuthConfig;
use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\RequestInterceptor\Authenticated;
use App\Modules\Core\RequestInterceptor\CSRFGuard;
use App\Modules\Core\RequestInterceptor\StartSession;
use App\Modules\Media\Controllers\MediaControllers;
use App\Modules\Media\RequestInterceptor\MediaAccess;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{
    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function routeWeb(Route $route): Route
    {
        // for serving files
        $route->get(DriveConfig::serveFilePath() .':fileUniqueID', [MediaControllers::class, 'serveFiles'], alias: 'media.serve');
        // admin facing
        $route->group('/admin/media', function (Route $route) {
            $route->get('file-manager', [MediaControllers::class, 'showMediaManager'], alias: 'show');
        }, AuthConfig::getAuthRequestInterceptor([MediaAccess::class]), alias: 'media');
        return $route;
    }


    /**
     * @throws \ReflectionException
     */
    public function routeApi(Route $route): Route
    {
        // app url
        $route->get('/api/media/app_url', [MediaControllers::class, 'getAppURL']);

        $route->group('/api/media',  function (Route $route){
            // get all files
            $route->get('files', [MediaControllers::class, 'getFiles']);
            // re-index drive files in db (dangerous as it would re-arrange the drive_unique_id on new added files)
           // $route->get('files/re_index', [MediaControllers::class, 'reIndex']);
            // search file
            $route->get('files/search', [MediaControllers::class, 'searchFiles']);
            // delete files
            $route->delete('files', [MediaControllers::class, 'deleteFile']);
            // create or update a file
            $route->post('files', [MediaControllers::class, 'createFile']);
            // create a folder
            $route->post('files/create_folder', [MediaControllers::class, 'createFolder']);
            // cancel file creation (more like, cancel upload)
            $route->delete('files/cancel_create', [MediaControllers::class, 'cancelFileCreate']);
            // rename a file
            $route->put('files/rename', [MediaControllers::class, 'renameFile']);
            // move files to dest
            $route->put('files/move', [MediaControllers::class, 'moveFiles']);
            // pre-flight check for resumable upload
            $route->get('files/preflight', [MediaControllers::class, 'getPreflightFile']);
        }, AuthConfig::getAuthRequestInterceptor([MediaAccess::class]));

        return $route;
    }

}