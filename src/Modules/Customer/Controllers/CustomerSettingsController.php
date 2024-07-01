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

namespace App\Modules\Customer\Controllers;

use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Controllers\AbstractFieldSettingsController;

class CustomerSettingsController extends AbstractFieldSettingsController
{
    public const CACHE_KEY = 'TonicsModule_TonicsCustomerSettings';

    const        SpamProtection_HoneyPotTrapDecoyInput                       = 'customerSettingsSpamProtectionHoneyPotTrapDecoyInput';
    const        SpamProtection_GlobalVariablesCheckInput                    = 'customerSettingsSpamProtectionGlobalVariablesCheckInput';
    const        SpamProtection_PreventDisposableEmailsInput                 = 'customerSettingsSpamProtectionPreventDisposableEmailInput';
    const        SpamProtection_PreventDisposableEmailsMoreDisposableDomains = 'customerSettingsSpamProtectionPreventDisposableEmailMoreDisposableDomains';

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function edit (): void
    {
        $settingsData = self::getSettingsData();
        view('Modules::Customer/Views/settings', [
            'FieldItems' => FieldConfig::getSettingsHTMLFrag($this->getFieldData(), $settingsData, ['customer-settings']),
        ],
        );
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function update (): void
    {
        $this->updateSettings('admin.customer.settings');
    }
}