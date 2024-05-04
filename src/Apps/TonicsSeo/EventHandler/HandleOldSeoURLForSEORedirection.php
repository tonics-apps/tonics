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

namespace App\Apps\TonicsSeo\EventHandler;

use App\Modules\Core\Library\Tables;
use App\Modules\Field\Events\OnAfterPreSavePostEditorFieldItems;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class HandleOldSeoURLForSEORedirection implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnAfterPreSavePostEditorFieldItems */
        $fieldSettings = $event->getFieldSettings();
        $canonical = '';
        if (isset($fieldSettings['seo_canonical_url'])){
            $canonical = trim($fieldSettings['seo_canonical_url']);
            $canonical = filter_var($canonical, FILTER_SANITIZE_URL);
        }

        if (isset($fieldSettings['seo_old_urls']) && !empty($canonical)){
            $oldURLS = explode(PHP_EOL, $fieldSettings['seo_old_urls']) ?? [];
            $canonical = parse_url($canonical);
            if (!isset($canonical['path'])){
                return;
            }
            $canonical = $canonical['path'];

            $toInsert = [];
            foreach ($oldURLS as $oldURL){
                $oldURL = filter_var($oldURL, FILTER_SANITIZE_URL);
                $oldURL = rtrim($oldURL, '/');
                if (!empty($oldURL)){
                    $settings = [
                        'from' => $oldURL,
                        'to'   => $canonical,
                    ];
                    $toInsert[] = $settings;
                }
            }

            try {
                db(onGetDB: function (TonicsQuery $db) use ($toInsert) {
                    $db->InsertOnDuplicate(
                        Tables::getTable(Tables::BROKEN_LINKS),
                        $toInsert,
                        ['to']
                    );
                });
            } catch (\Exception $exception){
                // Log..
            }

        }
    }
}