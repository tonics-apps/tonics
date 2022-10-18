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

            try {
                $jsonValues = db()->Select('*')->From(Tables::getTable(Tables::GLOBAL))->WhereEquals('`key`', 'url_redirections')->FetchFirst();
                $jsonValues = json_decode($jsonValues->value);

                # Remove The Old Canonical
                foreach ($jsonValues as $jsonKey => $jsonValue){
                    if ($jsonValue->to === $canonical){
                        unset($jsonValues[$jsonKey]);
                    }
                }

                # Push New to jsonValues
                foreach ($oldURLS as $oldURL){
                    $oldURL = filter_var($oldURL, FILTER_SANITIZE_URL);
                    $oldURL = rtrim($oldURL, '/');
                    $settings = [
                        'from' => $oldURL,
                        'to'   => $canonical,
                        'date' => helper()->date(),
                        'redirection_type' => 301
                    ];
                    $jsonValues[] = (object)$settings;
                }

                $jsonValues = array_values($jsonValues);
                $table = Tables::getTable(Tables::GLOBAL);
                db()->Update($table)
                    ->Set('value', json_encode($jsonValues, JSON_UNESCAPED_SLASHES))
                    ->WhereEquals('`key`', 'url_redirections')
                    ->FetchFirst();
            }catch (\Exception $exception){
                // Log..
            }

        }
    }
}