<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
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