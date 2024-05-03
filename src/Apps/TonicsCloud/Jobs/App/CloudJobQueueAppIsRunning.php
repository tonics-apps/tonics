<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Jobs\App;

use App\Apps\TonicsCloud\Interfaces\CloudAppSignalInterface;
use App\Apps\TonicsCloud\Jobs\App\Traits\TonicsJobQueueAppTrait;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CloudJobQueueAppIsRunning extends AbstractJobInterface implements JobHandlerInterface
{
    use TonicsJobQueueAppTrait;

    /**
     * @throws \ReflectionException
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle(): void
    {
        try {
            $appObject = $this->appObject();
            if ($appObject instanceof CloudAppSignalInterface){
                if ($appObject->isStatus(CloudAppSignalInterface::STATUS_RUNNING)){
                    $this->updateStatusMessage("Started", function (TonicsQuery $db){
                        $db->Set('app_status', 'Running');
                    });
                } else {
                    $this->updateStatusMessage("The check for application running returns negative");
                }
            }
        } catch (\Throwable $throwable){
            $this->updateStatusMessage($throwable->getMessage());
            throw $throwable;
        }

    }
}