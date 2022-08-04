<?php

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