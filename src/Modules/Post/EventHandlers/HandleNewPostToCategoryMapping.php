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

use App\Modules\Post\Data\PostData;
use App\Modules\Post\Events\OnPostCreate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleNewPostToCategoryMapping implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {

        /**
         * @var OnPostCreate $event
         */
        $toInsert = [];
        foreach ($event->getPostCatIDS() as $catID){
            $toInsert[] = [
                'fk_cat_id' => $catID,
                'fk_post_id' => $event->getPostID(),
            ];
        }

        db(onGetDB: function ($db) use ($event, $toInsert){
            $table = $event->getPostData()->getPostToCategoryTable();
            $db->Insert($table, $toInsert);
        });

    }
}