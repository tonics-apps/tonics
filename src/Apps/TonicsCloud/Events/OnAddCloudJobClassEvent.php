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

namespace App\Apps\TonicsCloud\Events;

use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueAppHasStopped;
use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueAppIsRunning;
use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueReloadApp;
use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueStartApp;
use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueStopApp;
use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueUpdateAppSettings;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueAddAppsContainersDb;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueContainerHasStopped;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueContainerIsRunning;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueCreateContainer;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueDeleteContainer;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueImageImported;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueImportImage;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueStartContainer;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueStopContainer;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueUpdateContainer;
use App\Apps\TonicsCloud\Jobs\Domain\CloudJobQueueCreateDomain;
use App\Apps\TonicsCloud\Jobs\Domain\CloudJobQueueCreateDomainRecord;
use App\Apps\TonicsCloud\Jobs\Domain\CloudJobQueueDeleteDomain;
use App\Apps\TonicsCloud\Jobs\Domain\CloudJobQueueDeleteDomainRecord;
use App\Apps\TonicsCloud\Jobs\Domain\CloudJobQueueUpdateDomain;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnAddCloudJobClassEvent implements EventInterface
{
    private array $jobClassName = [];

    public function __construct ()
    {
        # Core Jobs
        $this->jobClassName = [
            # Container
            'CloudJobQueueAddAppsContainersDb' => CloudJobQueueAddAppsContainersDb::class,
            'CloudJobQueueContainerHasStopped' => CloudJobQueueContainerHasStopped::class,
            'CloudJobQueueContainerIsRunning'  => CloudJobQueueContainerIsRunning::class,
            'CloudJobQueueCreateContainer'     => CloudJobQueueCreateContainer::class,
            'CloudJobQueueDeleteContainer'     => CloudJobQueueDeleteContainer::class,
            'CloudJobQueueImageImported'       => CloudJobQueueImageImported::class,
            'CloudJobQueueImportImage'         => CloudJobQueueImportImage::class,
            'CloudJobQueueStartContainer'      => CloudJobQueueStartContainer::class,
            'CloudJobQueueStopContainer'       => CloudJobQueueStopContainer::class,
            'CloudJobQueueUpdateContainer'     => CloudJobQueueUpdateContainer::class,
            # Domain
            'CloudJobQueueCreateDomain'        => CloudJobQueueCreateDomain::class,
            'CloudJobQueueCreateDomainRecord'  => CloudJobQueueCreateDomainRecord::class,
            'CloudJobQueueDeleteDomain'        => CloudJobQueueDeleteDomain::class,
            'CloudJobQueueDeleteDomainRecord'  => CloudJobQueueDeleteDomainRecord::class,
            'CloudJobQueueUpdateDomain'        => CloudJobQueueUpdateDomain::class,
            # App
            'CloudJobQueueAppHasStopped'       => CloudJobQueueAppHasStopped::class,
            'CloudJobQueueAppIsRunning'        => CloudJobQueueAppIsRunning::class,
            'CloudJobQueueReloadApp'           => CloudJobQueueReloadApp::class,
            'CloudJobQueueStartApp'            => CloudJobQueueStartApp::class,
            'CloudJobQueueStopApp'             => CloudJobQueueStopApp::class,
            'CloudJobQueueUpdateAppSettings'   => CloudJobQueueUpdateAppSettings::class,
        ];
    }

    public function event (): static
    {
        return $this;
    }

    public function addCloudServerHandler (JobHandlerInterface $jobHandler): static
    {
        $this->jobClassName[$jobHandler::class] = $jobHandler::class;
        return $this;
    }

    /**
     * Please do not pass a qualified class name, just pass the short class name
     *
     * @param string $name
     *
     * @return bool
     */
    public function exist (string $name): bool
    {
        return isset($this->jobClassName[$name]);
    }

    /**
     * Please do not pass a qualified class name, just pass the short class name
     * @throws \Exception
     */
    public function getJobClass (string $name): JobHandlerInterface
    {
        if (isset($this->jobClassName[$name])) {
            return container()->get($this->jobClassName[$name]);
        }

        throw new \Exception("$name is an unknown job handler name");
    }

    /**
     * @return array
     */
    public function getJobClassName (): array
    {
        return $this->jobClassName;
    }

    /**
     * @param array $jobClassName
     */
    public function setJobClassName (array $jobClassName): void
    {
        $this->jobClassName = $jobClassName;
    }
}