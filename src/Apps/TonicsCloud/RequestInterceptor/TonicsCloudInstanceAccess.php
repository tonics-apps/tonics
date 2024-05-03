<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\RequestInterceptor;

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInterceptorInterface;

class TonicsCloudInstanceAccess implements TonicsRouterRequestInterceptorInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handle(OnRequestProcess $request): void
    {
        $foundURLRequiredParam = $request->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams();
        $providerInstanceID = $foundURLRequiredParam[0];
        $serviceInstance = null;
        db(onGetDB: function (TonicsQuery $db) use ($providerInstanceID, &$serviceInstance) {
            $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
            if (\session()::getUserID() !== null){
                $serviceInstance = $db->Select('provider_instance_id')->From($serviceInstanceTable)
                    ->WhereEquals('fk_customer_id', \session()::getUserID())
                    // We might have multiple provider_instance_id that has been resized, WhereNull, ensure, we return the current or the resized one
                    ->WhereEquals('provider_instance_id', $providerInstanceID)->WhereNull('end_time')
                    ->FetchFirst();
            }
        });

        # If isset, then customer has access, we return, otherwise, we display UnauthorizedErrorMessage
        if (isset($serviceInstance->provider_instance_id)){
            return;
        }

        SimpleState::displayUnauthorizedErrorMessage();
    }
}