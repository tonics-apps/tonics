<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Core\Validation\RulePlugins;

use App\Modules\Core\Data\UserData;
use Devsrealm\TonicsValidation\Interfaces\RuleInterface;
use Devsrealm\TonicsValidation\Rule;

/**
 *
 * USAGE: `input_key' => ['Validateuser' => ['email' => 'youremail@adres.com', 'pass' => 134]]`,
 * where email is your email address, pass is your password
 *
 */
class ValidateCustomer extends Rule implements RuleInterface
{

    protected string $message = "Email or Password is Incorrect";

    /**
     * @throws \Exception
     */
    public function check(...$param): bool
    {
        $param = (object)$param;
        if (count($param->rule) !== 2){
            return false;
        }

        if (!key_exists('email', $param->rule) || !key_exists('pass', $param->rule)){
            return false;
        }

        $userData = new UserData();
        return $userData->validateCustomer($param->rule['email'], $param->rule['pass']);
    }

    /**
     * @inheritDoc
     */
    public function ruleNames(): array
    {
        return ['validate-customer', 'validate_customer', 'validatecustomer'];
    }
}