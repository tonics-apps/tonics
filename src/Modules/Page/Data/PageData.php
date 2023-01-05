<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Page\Data;

use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\Tables;

class PageData extends AbstractDataLayer
{
    use UniqueSlug;
    
    public function getPageTable(): string
    {
        return Tables::getTable(Tables::PAGES);
    }

    public function getPageColumns(): array
    {
        return Tables::$TABLES[Tables::PAGES];
    }

    /**
     * @throws \Exception
     */
    public function createPage(array $ignore = []): array
    {
        $slug = $this->generateUniqueSlug($this->getPageTable(),
            'page_slug', helper()->slugForPage(input()->fromPost()->retrieve('page_slug'), '-'));

        $_POST['field_settings'] = input()->fromPost()->all();
        unset($_POST['field_settings']['token']);

        $page = []; $postColumns = array_flip($this->getPageColumns());
        foreach (input()->fromPost()->all() as $inputKey => $inputValue){
            if (key_exists($inputKey, $postColumns) && input()->fromPost()->has($inputKey)){

                if($inputKey === 'created_at'){
                    $page[$inputKey] = helper()->date(datetime: $inputValue);
                    continue;
                }

                if ($inputKey === 'page_slug'){
                    $page[$inputKey] = $slug;
                    continue;
                }

                $page[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $page);
        if (!empty($ignores)){
            foreach ($ignores as $v){
                unset($page[$v]);
            }
        }

        if (isset($page['field_ids'])){
            $page['field_ids'] = array_values(array_flip(array_flip($page['field_ids'])));
            $page['field_ids'] = json_encode($page['field_ids']);
        }

        if (isset($page['field_settings'])){
            $page['field_settings'] = json_encode($page['field_settings']);
            if (isset($page['field_ids'])){
                $_POST['field_settings']['field_ids'] = $page['field_ids'];
            }
        }

        return $page;
    }
}