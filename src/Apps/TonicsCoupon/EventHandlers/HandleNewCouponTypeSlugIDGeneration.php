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

namespace App\Apps\TonicsCoupon\EventHandlers;

use App\Apps\TonicsCoupon\Events\OnCouponTypeCreate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleNewCouponTypeSlugIDGeneration implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        ## The iteration should only go once, but in a unlikely case that a collision occur, we try force updating the slugID until we max out 10 iterations
        ## but it should never happen even if you have 10Million posts
        $iterations = 10;
        for ($i = 0; $i < $iterations; ++$i) {
            try {
                $this->updateSlugID($event);
                break;
            } catch (\Exception){
                // log.. Collision occur message
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function updateSlugID($event)
    {
        /**
         * @var OnCouponTypeCreate $event
         */
        $slugGen = helper()->generateUniqueSlugID($event->getCouponTypeID());
        $updateChanges = $event->getCouponData()->createCouponType(['coupon_type_slug'], false);
        $updateChanges['slug_id'] = $slugGen;
        db(onGetDB: function ($db) use ($updateChanges, $event) {
            $db->FastUpdate($event->getCouponData()->getCouponTypeTable(), $updateChanges, db()->Where('coupon_type_id', '=', $event->getCouponTypeID()));
        });
        $event->getCouponType()->slug_id = $slugGen;
    }
}