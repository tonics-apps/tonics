<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
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