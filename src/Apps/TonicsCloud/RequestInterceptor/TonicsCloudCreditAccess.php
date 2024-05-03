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

use App\Apps\TonicsCloud\Controllers\BillingController;
use App\Apps\TonicsCloud\Controllers\TonicsCloudSettingsController;
use App\Modules\Core\Library\Authentication\Session;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInterceptorInterface;

class TonicsCloudCreditAccess implements TonicsRouterRequestInterceptorInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle(OnRequestProcess $request): void
    {
        if (TonicsCloudSettingsController::billingEnabled() === false) {
            return;
        }

        $remainingCredit = BillingController::RemainingCredit();
        if (helper()->moneyGreaterThan(TonicsCloudSettingsController::MinimumCredit(), $remainingCredit)){
            session()->flash(["Please Add Credit To Carry Out The Action"], [], Session::SessionCategories_FlashMessageInfo);
            redirect(route('tonicsCloud.billings.setting'));
        }

    }
}