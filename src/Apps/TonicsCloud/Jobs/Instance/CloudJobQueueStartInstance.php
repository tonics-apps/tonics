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

namespace App\Apps\TonicsCloud\Jobs\Instance;

use App\Apps\TonicsCloud\Interfaces\CloudServerInterface;
use App\Apps\TonicsCloud\Jobs\Instance\Traits\TonicsJobQueueInstanceTrait;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;

class CloudJobQueueStartInstance extends AbstractJobInterface implements JobHandlerInterface
{
    use TonicsJobQueueInstanceTrait;

    /**
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle(): void
    {
        $handler = $this->getHandler();
        # Already Booted
        if ($handler->isStatus($this->getDataAsArray(), CloudServerInterface::STATUS_RUNNING)){
            return;
        }
        $data = $this->getDataAsArray();
        $data['service_instance_status_action'] = 'Start';
        $handler->changeInstanceStatus($data);
    }
}