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

use App\Apps\TonicsCloud\Jobs\Instance\Traits\TonicsJobQueueInstanceTrait;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;

class CloudJobQueueResizeInstance extends AbstractJobInterface implements JobHandlerInterface
{
    use TonicsJobQueueInstanceTrait;

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle(): void
    {
        $this->getHandler()->resizeInstance($this->getDataAsArray());
        sleep(60); # Sleep for 30 seconds before quitting, which would give time upfront for the resize operation to have been started...
    }
}