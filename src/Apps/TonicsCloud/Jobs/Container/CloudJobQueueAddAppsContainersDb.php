<?php

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