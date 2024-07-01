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

namespace App\Modules\Customer\RequestInterceptor;

use App\Modules\Customer\Controllers\CustomerSettingsController;
use App\Modules\Customer\Events\OnAddCustomerSpamProtectionEvent;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInterceptorInterface;

class SpamProtection implements TonicsRouterRequestInterceptorInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle (OnRequestProcess $request): void
    {
        $onAddSpam = new OnAddCustomerSpamProtectionEvent();
        event()->dispatch($onAddSpam);
        if (!empty($onAddSpam->getCustomerSpamProtections())) {
            $settingsData = CustomerSettingsController::getSettingsData();
            foreach ($onAddSpam->getCustomerSpamProtections() as $customerSpamProtection) {
                if ($customerSpamProtection->isSpam($settingsData)) {
                    exit(200);
                }
            }
        }
    }
}