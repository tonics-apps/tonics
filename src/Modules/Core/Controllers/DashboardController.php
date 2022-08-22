<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Controllers;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     * @throws \Exception
     */
    public function index()
    {
       view('Modules::Core/Views/Dashboard/home');
    }
}
