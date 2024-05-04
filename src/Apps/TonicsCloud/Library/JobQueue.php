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

namespace App\Apps\TonicsCloud\Library;

use App\Apps\TonicsCloud\Events\OnAddCloudJobQueueTransporter;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobTransporterInterface;
use Devsrealm\TonicsEventSystem\Interfaces\EventDispatcherInterface;
use Test\A;


/**
 * The JobQueue System is an event dispatcher in disguise, except that different transporter dispatches or transports
 * events differently, some might use a database in place of a queue, some might use a real queue system, etc.
 *
 * <br>
 * The way the `JobEventDispatcher` works is you first enqueue a possible job you are planning to dispatch,
 * then depending on whether the transporter is async or sync, the object is then dispatched to handlers that want to handle the event.
 *
 * If async, you call them, and it would be place in queue waiting to process or directly if you prefer sync call perhaps for testing...
 *
 */
class JobQueue
{
    private string $transporterName = '';
    private object|null $transporter = null;

    private OnAddCloudJobQueueTransporter $onAddJobTransporter;

    /**
     * @throws \Exception
     */
    public function __construct(string $transporterName = '')
    {
        $this->transporterName = $transporterName;
        $this->onAddJobTransporter = event()->dispatch(new OnAddCloudJobQueueTransporter())->event();
        $this->setTransport($transporterName);
    }

    /**
     * @param string $transporterName
     * @throws \Exception
     */
    public function setTransport(string $transporterName): void
    {
        if ($this->getOnAddJobTransporter()->exist($transporterName)){
            $this->transporterName = $transporterName;
            $this->transporter = $this->getOnAddJobTransporter()->getTransporter($transporterName);
        }
    }

    /**
     * Handle the Enqueueing of Nested Jobs
     * @param array $jobs
     * @param null $defaultData -- adds the defaultData to job if job doesn't have data
     * @return void
     * @throws \Exception
     */
    public function enqueueBatch(array $jobs, $defaultData = null): void
    {
        $chains = AbstractJobInterface::addChains($jobs, null, $defaultData);
        foreach ($chains as $chain){
            $this->enqueue($chain['job']);
        }
    }

    public function enqueue(AbstractJobInterface $jobEvent, callable $beforeEnqueue = null, callable $afterEnqueue = null): void
    {
        $this->transporter->enqueue($jobEvent, $beforeEnqueue, $afterEnqueue);
    }

    public function runJob(): void
    {
        $this->transporter->runJob();
    }

    /**
     * @return object
     * @throws \Exception
     */
    public function getTransporter(): object
    {
        return $this->transporter;
    }

    /**
     * @param EventDispatcherInterface&JobTransporterInterface $transporter
     */
    private function setTransporter(EventDispatcherInterface&JobTransporterInterface $transporter): void
    {
        $this->transporter = $transporter;
    }

    /**
     * @return string
     */
    public function getTransporterName(): string
    {
        return $this->transporterName;
    }

    /**
     * @param string $transporterName
     */
    public function setTransporterName(string $transporterName): void
    {
        $this->transporterName = $transporterName;
    }

    /**
     * @return OnAddCloudJobQueueTransporter
     */
    private function getOnAddJobTransporter(): OnAddCloudJobQueueTransporter
    {
        return $this->onAddJobTransporter;
    }

    /**
     * @param OnAddCloudJobQueueTransporter $onAddJobTransporter
     */
    private function setOnAddJobTransporter(OnAddCloudJobQueueTransporter $onAddJobTransporter): void
    {
        $this->onAddJobTransporter = $onAddJobTransporter;
    }

}