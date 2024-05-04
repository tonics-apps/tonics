<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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