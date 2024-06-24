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

use App\Apps\TonicsCloud\EventHandlers\Messages\TonicsCloudDomainMessage;
use App\Apps\TonicsCloud\Jobs\Domain\Traits\TonicsJobQueueDomainTrait;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Events\OnAddMessageType;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CloudJobQueueDeleteDomain extends AbstractJobInterface implements JobHandlerInterface
{
    use TonicsJobQueueDomainTrait;

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle (): void
    {
        $this->getCloudDNSHandler()->deleteDomain($this->getDataAsArray());
        db(onGetDB: function (TonicsQuery $db) {
            $table = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_DNS);
            $db->FastDelete($table, db()->WhereEquals('dns_id', $this->getDNSID()));

            message()->send(
                ['dns_id' => $this->getDNSID(), 'eventType' => OnAddMessageType::EVENT_TYPE_DELETE],
                TonicsCloudDomainMessage::MessageTypeKey($this->getCustomerID()),
            );

        });
    }
}