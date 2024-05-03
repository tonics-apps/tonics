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
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CloudJobQueueCreateDomain extends AbstractJobInterface implements JobHandlerInterface
{
    use TonicsJobQueueDomainTrait;

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $response = $this->getCloudDNSHandler()->createDomain($this->getDomainData());
        if (isset($response['id'])){
            $this->updateDNSStatusMessage("Domain Created...Creating Records", function (TonicsQuery $db) use ($response){
                $db->Set('others', db()->JsonSet('others', '$.domain_id', db()->JsonCompact($response['id'])));
            });

            $this->enqueueDomainRecordsForCreate($response['id']);
        }
    }
}