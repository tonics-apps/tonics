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

use App\Apps\TonicsCloud\Services\AppService;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;

class CloudJobQueueUpdateApp extends AbstractJobInterface implements JobHandlerInterface
{
    private AppService $appService;

    public function __construct (AppService $appService)
    {
        $this->appService = $appService;
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle (): void
    {
        if (is_array($this->getDataAsArray()['appsToUpdate']) && !empty($this->getDataAsArray()['appsToUpdate'])) {

            $data = $this->getDataAsArray();
            $appLeftToUpdate = $data['appsToUpdate'];
            $appData = array_shift($appLeftToUpdate);
            $appData = (array)$appData;
            $data['appsToUpdate'] = $appLeftToUpdate;
            $this->setData($data);

            if (empty($appLeftToUpdate)) {
                $this->appService->updateApp($appData);
            } else {
                $this->appService->updateApp($appData, ['job' => $this]);
            }

            if ($this->appService->fails()) {
                throw new \Exception(...$this->appService->getErrors());
            }
        }

    }
}