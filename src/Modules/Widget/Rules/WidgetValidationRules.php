<?php

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

    public function menuWidgetItemsStoreRule(): array
    {
        return [
            'menuWidgetSlug' => ['required', 'string'],
            'menuWidgetItems' => ['array']
        ];
    }
}