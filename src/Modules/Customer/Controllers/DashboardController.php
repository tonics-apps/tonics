<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Customer\Controllers;

use App\Modules\Core\Library\Authentication\Session;

class DashboardController
{
    /**
     * @throws \Exception
     */
    public function index()
    {
        $dataInfo = \session()->retrieve(Session::SessionCategories_AuthInfo);
        if (helper()->isJSON($dataInfo)){
            $dataInfo = json_decode($dataInfo);
        }

        view(
            'Modules::Customer/Views/Dashboard/home',
            ['UserInfo' => $dataInfo]
        );
    }
}