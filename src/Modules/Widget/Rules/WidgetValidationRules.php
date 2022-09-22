<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Widget\Rules;

use App\Modules\Core\Library\Tables;

trait WidgetValidationRules
{
    /**
     * @throws \Exception
     */
    public function widgetStoreRule(): array
    {
        $menuUniqueSlug = Tables::getTable(Tables::WIDGETS) .':widget_slug';
        return [
            'widget_name' => ['required', 'string'],
            'widget_slug' => ['required', 'string', 'unique' => [
                $menuUniqueSlug => input()->fromPost()->retrieve('widget_slug', '')]
            ],
        ];
    }

    /**
     * @throws \Exception
     */
    public function widgetUpdateRule(): array
    {
        $widgetUniqueSlug = Tables::getTable(Tables::WIDGETS) .':widget_slug:widget_id';
        return [
            'widget_name' => ['required', 'string'],
            'widget_slug' => ['required', 'string', 'unique' => [
                $widgetUniqueSlug => input()->fromPost()->retrieve('widget_id', '')]
            ],
        ];
    }

    /**
     * @return \string[][]
     */
    public function widgetUpdateMultipleRule(): array
    {
        return [
            'widget_id' => ['numeric'],
            'widget_name' => ['required', 'string'],
            'updated_at' => ['required', 'string'],
        ];
    }

    public function menuWidgetItemsStoreRule(): array
    {
        return [
            'menuWidgetSlug' => ['required', 'string'],
            'menuWidgetItems' => ['array']
        ];
    }
}