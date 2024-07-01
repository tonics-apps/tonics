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

namespace App\Modules\Customer\EventHandlers\SpamProtections;

use App\Modules\Customer\Controllers\CustomerSettingsController;
use App\Modules\Customer\Interfaces\CustomerSpamProtectionInterfaceAbstract;

class HoneyPotTrap extends CustomerSpamProtectionInterfaceAbstract
{

    /**
     * @inheritDoc
     */
    public function name (): string
    {
        return 'customer-settings-spam-protection-honeypot-trap';
    }

    /**
     * @inheritDoc
     */
    public function displayName (): string
    {
        return 'HoneyPot Trap';
    }

    /**
     * @param array $data
     *
     * @return bool
     * @throws \Throwable
     */
    public function isSpam (array $data): bool
    {
        $content = $data[CustomerSettingsController::SpamProtection_HoneyPotTrapDecoyInput] ?? null;
        if ($content === null) {
            return false;
        }

        $inputs = explode(',', $content);
        $isSpam = false;
        foreach ($inputs as $input) {
            if (input()->fromPost()->has($input) === false) {
                $isSpam = true;
                break;
            }

            if (input()->fromPost()->hasValue($input)) {
                $isSpam = true;
                break;
            }
        }

        return $isSpam;
    }
}