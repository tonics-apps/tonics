<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\EventHandlers\CloudDNSHandler;

use App\Apps\TonicsCloud\Controllers\TonicsCloudSettingsController;
use App\Apps\TonicsCloud\Events\OnAddCloudDNSEvent;
use App\Apps\TonicsCloud\Interfaces\CloudDNSInterface;
use App\Apps\TonicsCloud\Library\Linode\LinodePricingServices;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Linode\Domains\Repository\DomainRecordRepository;
use Linode\Exception\LinodeException;
use Linode\LinodeClient;

require dirname(__FILE__, 3) . '/Library/Linode/Webinarium/vendor/autoload.php';

class LinodeCloudDNSHandler implements HandlerInterface, CloudDNSInterface
{
    const API_DOMAIN = '/domains';

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnAddCloudDNSEvent */
        $event->addCloudServerHandler($this);
    }

    public function name(): string
    {
        return LinodePricingServices::PermName;
    }

    /**
     * @param array $data
     * @return array
     * @throws LinodeException
     * @throws \Exception
     */
    public function createDomain(array $data): array
    {
        return $this->getLinodeClient()->domains->createDomain($data)->toArray();
    }

    /**
     * @throws LinodeException
     * @throws \Exception
     */
    public function getDomain(array $data): array
    {
        return $this->getLinodeClient()->domains->find($this->getID($data))->toArray();
    }

    /**
     * @param array $data
     * @return array|null
     * @throws LinodeException
     * @throws \Exception
     */
    public function updateDomain(array $data): ?array
    {
        return $this->getLinodeClient()->domains->updateDomain($this->getID($data), $data)->toArray();
    }

    /**
     * @param array $data
     * @return void
     * @throws LinodeException
     * @throws \Exception
     */
    public function deleteDomain(array $data): void
    {
        $this->getLinodeClient()->domains->deleteDomain($this->getID($data));
    }

    /**
     * @throws LinodeException
     * @throws \Exception
     */
    public function createDomainRecord(array $data): array
    {
        return $this->getLinodeClientDomainRecord($this->getID($data))->createDomainRecord($data)->toArray();
    }

    /**
     * @param array $data
     * @return array
     * @throws LinodeException
     * @throws \Exception
     */
    public function getDomainRecord(array $data): array
    {
        return $this->getLinodeClientDomainRecord($this->getID($data))->find($this->getRecordID($data))->toArray();
    }

    /**
     * @param array $data
     * @return array
     * @throws LinodeException
     * @throws \Exception
     */
    public function updateDomainRecord(array $data): array
    {
        return $this->getLinodeClientDomainRecord($this->getID($data))->updateDomainRecord($this->getRecordID($data), $data)->toArray();
    }

    /**
     * @param array $data
     * @return void
     * @throws LinodeException
     * @throws \Exception
     */
    public function deleteDomainRecord(array $data): void
    {
        $id = $this->getID($data);
        $recordID = $this->getRecordID($data);
        if (is_null($id) || is_null($recordID)) {
            return;
        }
        $this->getLinodeClientDomainRecord($id)->deleteDomainRecord($recordID);
    }

    /**
     * @param array $data
     * @param bool $unsetID
     * @return mixed|null
     */
    private function getID(array &$data, bool $unsetID = true): mixed
    {
        $id = null;
        $idString = null;
        if (isset($data['id'])){
            $id = $data['id'];
            $idString = 'id';
        } elseif (isset($data['domainID'])){
            $id = $data['domainID'];
            $idString = 'domainID';
        } elseif (isset($data['domain_id'])){
            $id = $data['domain_id'];
            $idString = 'domain_id';
        }

        if ($unsetID && $id){
            unset($data[$idString]);
        }

        return $id;
    }

    /**
     * @param array $data
     * @param bool $unsetID
     * @return mixed|null
     */
    private function getRecordID(array &$data, bool $unsetID = true): mixed
    {
        $id = null;
        $idString = null;
        if (isset($data['record_id'])){
            $idString = 'record_id';
            $id = $data[$idString];
        } elseif (isset($data['domainRecordID'])){
            $idString = 'domainRecordID';
            $id = $data[$idString];
        } elseif (isset($data['recordID'])){
            $idString = 'recordID';
            $id = $data[$idString];
        }

        if ($unsetID && $id){
            unset($data[$idString]);
        }

        return $id;
    }


    /**
     * @return LinodeClient
     * @throws \Exception
     */
    private function getLinodeClient(): LinodeClient
    {
        return new LinodeClient(TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::LinodeAPIToken));
    }

    /**
     * @param $domainID
     * @return DomainRecordRepository
     * @throws \Exception
     */
    private function getLinodeClientDomainRecord($domainID): DomainRecordRepository
    {
        $linodeClient = $this->getLinodeClient();
        return new DomainRecordRepository($linodeClient, $domainID);
    }

}