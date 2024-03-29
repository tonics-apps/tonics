<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\EventHandlers;

use App\Modules\Core\Library\Tables;
use App\Modules\Payment\Events\OnPurchaseCreate;
use App\Modules\Post\Events\OnPostCreate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleNewPurchaseSlugIDGeneration implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        ## The iteration should only go once, but in an unlikely case that a collision occur, we try force updating the slugID until we max out 10 iterations
        ## but it should never happen even if you have 10Million posts
        $iterations = 10;
        for ($i = 0; $i < $iterations; ++$i) {
            try {
                $this->updateSlugID($event);
                break;
            } catch (\Exception $exception){
                // Log..
                // Collision occur message
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function updateSlugID($event)
    {
        /**
         * @var OnPurchaseCreate $event
         */
        $slugGen = helper()->generateUniqueSlugID($event->getPurchaseID());
        $purchaseToUpdate['slug_id'] = $slugGen;
        db(onGetDB: function ($db) use ($event, $purchaseToUpdate) {
            $db->FastUpdate(Tables::getTable(Tables::PURCHASES), $purchaseToUpdate, db()->Where('purchase_id', '=', $event->getPurchaseID()));
        });

        $event->getPurchase()->slug_id = $slugGen;
    }
}