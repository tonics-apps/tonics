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

use App\Modules\Core\Library\Tables;
use App\Modules\Field\Events\OnAfterPreSavePostEditorFieldItems;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleOldSeoURLForSEORedirection implements HandlerInterface
{

    /**
     * @inheritDoc
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

        $urlRedirection = [];
        if (isset($fieldSettings['seo_old_urls']) && !empty($canonical)){
            $oldURLS = explode(PHP_EOL, $fieldSettings['seo_old_urls']) ?? [];
            $canonical = parse_url($canonical);
            if (!isset($canonical['path'])){
                return;
            }
            $canonical = $canonical['path'];

            foreach ($oldURLS as $oldURL){
                $oldURL = filter_var($oldURL, FILTER_SANITIZE_URL);
                $urlRedirection[$oldURL] = $canonical;
            }

            try {
                $table = Tables::getTable(Tables::GLOBAL);
                db()->run(<<<SQL
UPDATE $table 
   SET value = JSON_MERGE_PATCH(value, ?) 
 WHERE `key` = 'url_redirections';
SQL, json_encode($urlRedirection, JSON_UNESCAPED_SLASHES));
            }catch (\Exception $exception){
                // log..
            }
        }
    }
}