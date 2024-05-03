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

class CloudJobQueueCreateDomainRecord extends AbstractJobInterface implements JobHandlerInterface
{
    use TonicsJobQueueDomainTrait;

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $record = $this->getDomainRecord();
        $type = $record['type'] ?? '';
        try {
            $response = $this->getCloudDNSHandler()->createDomainRecord($record);
            $uniqueKey = $this->getDNSRecordUniqueKey();
            $this->updateDNSStatusMessage("Created Record $type", function (TonicsQuery $db) use ($uniqueKey, $response){
                $db->Set('others', db()->JsonSet('others', "$.records.$uniqueKey.record_id", db()->JsonCompact($response['id'])));
            });
        } catch (\Exception $exception){
            $msg = $exception->getMessage();
            $this->updateDNSStatusMessage("Error: $msg | $type");
            throw $exception;
        }
    }
}