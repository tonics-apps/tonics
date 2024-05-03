<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Jobs\Instance;

use App\Apps\TonicsCloud\Controllers\TonicsCloudSettingsController;
use App\Apps\TonicsCloud\Interfaces\CloudServerInterface;
use App\Apps\TonicsCloud\Jobs\Instance\Traits\TonicsJobQueueInstanceTrait;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;

class CloudJobQueueStartInstance extends AbstractJobInterface implements JobHandlerInterface
{
    use TonicsJobQueueInstanceTrait;

    /**
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle(): void
    {
        $handler = $this->getHandler();
        # Already Booted
        if ($handler->isStatus($this->getDataAsArray(), CloudServerInterface::STATUS_RUNNING)){
            return;
        }
        $data = $this->getDataAsArray();
        $data['service_instance_status_action'] = 'Start';
        $handler->changeInstanceStatus($data);
    }
}