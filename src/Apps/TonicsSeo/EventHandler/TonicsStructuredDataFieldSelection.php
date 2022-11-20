<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsSeo\EventHandler;

use App\Modules\Field\Events\OnEditorFieldSelection;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TonicsStructuredDataFieldSelection implements HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var $event OnEditorFieldSelection */
        $event->addField('FAQ', 'app-tonicsseo-structured-data-faq', category: OnEditorFieldSelection::CATEGORY_StructuredData);
    }
}