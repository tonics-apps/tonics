<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsToc\Controller;

class TonicsTocController
{
    /**
     * @throws \Exception
     */
    public function settings(): void
    {
        view('Apps::TonicsToc/Views/settings');
    }

    public function saveSettings()
    {

    }
}