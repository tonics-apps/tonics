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