<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Customer\Controllers\CustomerAuth;

use App\Modules\Core\Controllers\Controller;

class LoginController extends Controller
{
    /***
     * @return void
     * @throws \Exception
     */
    public function showLoginForm(): void
    {
        view('Modules::Customer/Views/Auth/login');
    }

}
