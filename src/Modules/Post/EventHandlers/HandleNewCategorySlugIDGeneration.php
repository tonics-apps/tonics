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

namespace App\Modules\Post\EventHandlers;

use App\Modules\Post\Events\OnPostCategoryCreate;
use App\Modules\Post\Events\OnPostCreate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleNewCategorySlugIDGeneration implements HandlerInterface
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
         * @var OnPostCategoryCreate $event
         */
        $slugGen = helper()->generateUniqueSlugID($event->getCatID());
        $postToUpdate = $event->getPostData()->createCategory(['cat_slug'], false);
        $postToUpdate['slug_id'] = $slugGen;
        db(onGetDB: function ($db) use ($postToUpdate, $event) {
            $db->FastUpdate($event->getPostData()->getCategoryTable(), $postToUpdate, db()->Where('cat_id', '=', $event->getCatID()));
        });
        $event->getCategory()->slug_id = $slugGen;
    }
}