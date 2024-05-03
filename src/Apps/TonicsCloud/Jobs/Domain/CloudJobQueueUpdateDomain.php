<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Jobs\Domain;

use App\Apps\TonicsCloud\Jobs\Domain\Traits\TonicsJobQueueDomainTrait;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CloudJobQueueUpdateDomain extends AbstractJobInterface implements JobHandlerInterface
{
    use TonicsJobQueueDomainTrait;

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $data = $this->getDomainData();
        $data['domain_id'] = $this->getDomainID();
        $response = $this->getCloudDNSHandler()->updateDomain($data);
        if (isset($response['id'])) {
            $this->updateDNSStatusMessage("Updated Domain");
            $this->enqueueDomainRecordsForDelete();
            $this->enqueueDomainRecordsForCreate($this->getDomainID());
        }
    }
}