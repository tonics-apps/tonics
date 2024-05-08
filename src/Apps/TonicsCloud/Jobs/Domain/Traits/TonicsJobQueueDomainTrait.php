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

namespace App\Apps\TonicsCloud\Jobs\Domain\Traits;

use App\Apps\TonicsCloud\Controllers\TonicsCloudSettingsController;
use App\Apps\TonicsCloud\Interfaces\CloudDNSInterface;
use App\Apps\TonicsCloud\Jobs\Domain\CloudJobQueueCreateDomainRecord;
use App\Apps\TonicsCloud\Jobs\Domain\CloudJobQueueDeleteDomainRecord;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

trait TonicsJobQueueDomainTrait
{
    /**
     * DNS ID from the database
     * @return mixed
     */
    public function getDNSID(): mixed
    {
        return $this->getDataAsArray()['dns_id'] ?? '';
    }

    /**
     * DNS ID from the database
     * @return mixed
     */
    public function getDNSRecordUniqueKey(): mixed
    {
        return $this->getDataAsArray()['record_unique_key'] ?? '';
    }

    /**
     * Domain ID from the cloud provider
     * @return string
     */
    public function getDomainID(): string
    {
        return $this->getDataAsArray()['domain_id'] ?? '';
    }

    public function getDomainData(): array
    {
        return (array)$this->getDataAsArray()['domain'] ?? [];
    }

    /**
     * Gets all to be inserted records
     * @return array
     */
    public function getAllToBeDeletedDomainRecords(): array
    {
        return (array)$this->getDataAsArray()['delete_records'] ?? [];
    }

    /**
     * Gets all to be inserted records
     * @return array
     */
    public function getAllToBeInsertedDomainRecords(): array
    {
        return (array)$this->getDataAsArray()['records'] ?? [];
    }

    /**
     * @param $domainID
     * @return void
     * @throws \Exception
     */
    public function enqueueDomainRecordsForCreate($domainID): void
    {
        foreach ($this->getAllToBeInsertedDomainRecords() as $recordKey => $record){
            $record = (array)$record;
            $record['domain_id'] = $domainID;
            $jobData = [
                'dns_id' => $this->getDNSID(),
                'domain_id' => $domainID,
                'record_unique_key' => $recordKey,
                'record' => $record
            ];
            $jobs = [
                [
                    'job' => new CloudJobQueueCreateDomainRecord(),
                    'children' => []
                ]
            ];

            TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
        }
    }

    /**
     * @throws \Exception
     */
    public function enqueueDomainRecordsForDelete(): void
    {
        foreach ($this->getAllToBeDeletedDomainRecords() as $recordKey => $record){
            $record = (array)$record;
            $record['domain_id'] = $this->getDomainID();
            $jobData = [
                'dns_id' => $this->getDNSID(),
                'domain_id' => $this->getDomainID(),
                'record_unique_key' => $recordKey,
                'record' => $record
            ];
            $jobs = [
                [
                    'job' => new CloudJobQueueDeleteDomainRecord(),
                    'children' => []
                ]
            ];

            TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
        }
    }

    /**
     * Get the single record type
     * @return array|mixed
     */
    public function getDomainRecord(): mixed
    {
        return (array)$this->getDataAsArray()['record'] ?? [];
    }


    /**
     * @param string $statusMsg
     * @param null $callableUpdateMore -- set more data, you'll be passed the TonicsQuery
     * @return void
     * @throws \Exception
     */
    public function updateDNSStatusMessage(string $statusMsg, $callableUpdateMore = null): void
    {
        db(onGetDB: function (TonicsQuery $db) use ($callableUpdateMore, $statusMsg) {
            $DNSID = $this->getDNSID();
            $table = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_DNS);
            $db->Update($table)
                ->Set('dns_status_msg', $statusMsg);
            if ($callableUpdateMore){
                $callableUpdateMore($db);
            }
            $db->WhereEquals('dns_id', $DNSID)->Exec();
        });
    }

    /**
     * @return CloudDNSInterface
     * @throws \Exception
     * @throws \Throwable
     */
    public function getCloudDNSHandler(): CloudDNSInterface
    {
        return TonicsCloudActivator::getCloudDNSHandler(TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::CloudDNSIntegrationType));

    }

}