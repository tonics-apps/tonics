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

namespace App\Apps\TonicsCloud\Jobs\App;

use App\Apps\TonicsCloud\Interfaces\CloudAppSignalInterface;
use App\Apps\TonicsCloud\Jobs\App\Traits\TonicsJobQueueAppTrait;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CloudJobQueueAppHasStopped extends AbstractJobInterface implements JobHandlerInterface
{
    use TonicsJobQueueAppTrait;

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function handle(): void
    {
        $appObject = $this->appObject();
        if ($appObject instanceof CloudAppSignalInterface){
            if ($appObject->isStatus(CloudAppSignalInterface::STATUS_STOPPED)){
                $this->updateStatusMessage("Stopped", function (TonicsQuery $db){
                    $db->Set('app_status', 'Offline');
                });
            } else {
                $this->updateStatusMessage("The check for application stopped returns negative", function (TonicsQuery $db){
                    $db->Set('app_status', 'Error');
                });
            }
        }
    }
}