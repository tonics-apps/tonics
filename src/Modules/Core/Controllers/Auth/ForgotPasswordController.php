<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Controllers\Auth;

use App\Modules\Core\Controllers\Controller;

class ForgotPasswordController extends Controller
{
    /**
     * @throws \Exception
     */
    public function showLinkRequestForm()
    {
       view('Modules::Core/Views/Auth/reset-password');
    }
}
