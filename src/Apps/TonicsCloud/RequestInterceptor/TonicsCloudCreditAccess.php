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