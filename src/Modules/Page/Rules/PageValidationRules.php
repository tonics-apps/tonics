<?php

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

}