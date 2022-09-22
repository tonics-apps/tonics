<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Page\Rules;

use App\Modules\Core\Library\Tables;

trait PageValidationRules
{
    /**
     * @throws \Exception
     */
    public function pageStoreRule(): array
    {
        $postSlugUnique = Tables::getTable(Tables::PAGES) .':page_slug';
        return [
            'page_title' => ['required', 'string'],
            'page_slug' => ['required', 'string', 'unique' => [
                    $postSlugUnique => input()->fromPost()->retrieve('page_slug', '')]
            ],
            'page_status' => ['required', 'numeric'],
            'created_at' => ['required', 'string']
        ];
    }

    /**
     * @throws \Exception
     */
    public function pageUpdateRule(): array
    {
        $postSlugUnique = Tables::getTable(Tables::PAGES) .':page_slug:page_id';
        return [
            'page_title' => ['required', 'string'],
            'page_slug' => ['required', 'string',
                'unique' => [
                    $postSlugUnique => input()->fromPost()->retrieve('page_id', '')]
            ],
            'page_status' => ['required', 'numeric'],
            'created_at' => ['required', 'string'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function pageUpdateMultipleRule(): array
    {
        return [
            'page_id' => ['numeric'],
            'page_title' => ['required', 'string'],
            'updated_at' => ['required', 'string'],
        ];
    }

}