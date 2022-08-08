<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\NinetySeven\EventHandler;

use App\Modules\Core\Events\EditorsAsset;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class EditorsAssetsHandler implements HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var $event EditorsAsset */
        $event->addCSS('/serve_app_file_path_987654321/NinetySeven/?path=css/styles.min.css');
    }

}