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
                db()->InsertOnDuplicate(
                    Tables::getTable(Tables::BROKEN_LINKS),
                    $toInsert,
                    ['to']
                );
            } catch (\Exception $exception){
                dd($exception);
                // Log..
            }

        }
    }
}