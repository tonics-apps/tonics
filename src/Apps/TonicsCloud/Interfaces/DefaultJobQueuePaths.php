<?php
/*
 * Copyright (c) 2024. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Interfaces;

use App\Apps\TonicsCloud\Jobs\Instance\CloudJobQueueCreateInstance;
use App\Apps\TonicsCloud\Jobs\Instance\CloudJobQueueDestroyInstance;
use App\Apps\TonicsCloud\Jobs\Instance\CloudJobQueueInstanceAndIncusIsRunning;
use App\Apps\TonicsCloud\Jobs\Instance\CloudJobQueueInstanceHasDeleted;
use App\Apps\TonicsCloud\Jobs\Instance\CloudJobQueueInstanceHasStopped;
use App\Apps\TonicsCloud\Jobs\Instance\CloudJobQueueInstanceIsRunning;
use App\Apps\TonicsCloud\Jobs\Instance\CloudJobQueueRebootInstance;
use App\Apps\TonicsCloud\Jobs\Instance\CloudJobQueueResizeInstance;
use App\Apps\TonicsCloud\Jobs\Instance\CloudJobQueueStartInstance;
use App\Apps\TonicsCloud\Jobs\Instance\CloudJobQueueStopInstance;

abstract class DefaultJobQueuePaths
{
    const PATH_INSTANCE_TERMINATE = 'TerminateInstanceJobQueuePaths';
    const PATH_INSTANCE_REBOOT = 'RebootInstanceJobQueuePaths';
    const PATH_INSTANCE_SHUT_DOWN = 'ShutDownInstanceJobQueuePaths';
    const PATH_INSTANCE_START = 'StartInstanceJobQueuePaths';
    const PATH_INSTANCE_RESIZE = 'ResizeInstanceJobQueuePaths';
    const PATH_INSTANCE_CREATE = 'CreateInstanceJobQueuePaths';

    public static function TerminateInstanceJobQueuePaths(): array
    {
        return [
            [
                'job' => new CloudJobQueueDestroyInstance(),
                'children' => [
                    ['job' => new CloudJobQueueInstanceHasDeleted()]
                ]
            ]
        ];
    }

    public static function RebootInstanceJobQueuePaths(): array
    {
        return [
            [
                'job' => new CloudJobQueueRebootInstance(),
                'children' => [
                    ['job' => new CloudJobQueueInstanceIsRunning()]
                ]
            ]
        ];
    }

    public static function ShutDownInstanceJobQueuePaths(): array
    {
        return [
            [
                'job' => new CloudJobQueueStopInstance(),
                'children' => [
                    ['job' => new CloudJobQueueInstanceHasStopped()]
                ]
            ]
        ];
    }

    public static function StartInstanceJobQueuePaths(): array
    {
        return [
            [
                'job' => new CloudJobQueueStartInstance(),
                'children' => [
                    ['job' => new CloudJobQueueInstanceIsRunning()]
                ]
            ]
        ];
    }

    public static function ResizeInstanceJobQueuePaths(): array
    {
        $resizeJobs = [
            [
                'job' => new CloudJobQueueResizeInstance(),
                'children' => [
                    [
                        'job' => new CloudJobQueueInstanceHasStopped(),
                        'children' => self::StartInstanceJobQueuePaths()
                    ]
                ]
            ]
        ];

        return [
            [
                'job' => new CloudJobQueueStopInstance(),
                'children' => [
                    [
                        'job' => new CloudJobQueueInstanceHasStopped(),
                        'children' => $resizeJobs
                    ]
                ]
            ]
        ];
    }

    public static function CreateInstanceJobQueuePaths(): array
    {
        return [
            [
                'job' => new CloudJobQueueCreateInstance(),
                'children' => [
                    ['job' => new CloudJobQueueInstanceAndIncusIsRunning()]
                ]
            ]
        ];
    }
}