<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Apps\TonicsCloud\Jobs\Container;

use App\Apps\TonicsCloud\Jobs\Container\Traits\TonicsJobQueueContainerTrait;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CloudJobQueueAddAppsContainersDb extends AbstractJobInterface implements JobHandlerInterface
{
    use TonicsJobQueueContainerTrait;

    /**
     * @return void
     * @throws \Exception
     */
    public function handle(): void
    {

        $imageApps = $this->getImageOthers()?->apps;

        if (!empty($imageApps)){
            db(onGetDB: function (TonicsQuery $db) use ($imageApps){
                $appTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_APPS);
                $apps = $db->Select('app_id')->From($appTable)->WhereIn('app_name', $imageApps)->FetchResult();

                $containerID = $this->getContainerID();
                $insert = [];
                foreach ($apps as $app){
                    $insert[] = [
                        'fk_container_id' => $containerID,
                        'fk_app_id' => $app->app_id,
                    ];
                }

                if (!empty($insert)){
                    $appsContainersTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_APPS_TO_CONTAINERS);
                    $db->Q()->Insert($appsContainersTable, $insert);
                }

            });
        }
    }
}