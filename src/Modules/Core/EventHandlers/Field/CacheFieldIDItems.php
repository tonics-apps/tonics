<?php

namespace App\Modules\Core\EventHandlers\Field;

use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class CacheFieldIDItems implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
       if (method_exists($event, 'getData') && isset($event->getData()['fieldSlug'])){
           $fieldData = new FieldData();
           $fieldSlug = $event->getData()['fieldSlug'];
           $fieldData->sortAndCacheFieldItemsForFrontEnd([$fieldSlug]);
       }
    }
}