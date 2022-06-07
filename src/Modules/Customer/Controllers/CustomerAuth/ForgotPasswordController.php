<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Customer\Controllers\CustomerAuth;

use App\Modules\Core\Controllers\Controller;

class ForgotPasswordController extends Controller
{

    /**
     * Show the form to request a password reset link.
     * @throws \Exception
     */
    public function showLinkRequestForm()
    {
        view('Modules::Customer/Views/Auth/reset-password');
    }
}
