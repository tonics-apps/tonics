<?php
/*
 *     Copyright (c) 2024-2025. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Page\Services;

use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class PageService extends AbstractService
{
    public function __construct(private readonly FieldData $fieldData)
    {
    }

    /**
     * @param array $pages
     *
     * @return array
     * @throws \Exception
     */
    public static function GetPagesAndLayoutSelectorForPages(array $pages): array
    {
        $pages = PageService::getPagesBy($pages) ?? [];
        return FieldConfig::LayoutSelectorsForPages($pages);
    }

    /**
     * @param array $pageIdentifiers
     * @param string $col
     * @param string $select
     *
     * @return array|null
     * @throws \Exception
     */
    public static function getPagesBy(array $pageIdentifiers, string $col = 'page_slug', string $select = '*'): ?array
    {
        if (empty($pageIdentifiers)) {
            return null;
        }

        $pages = null;
        $col = table()->pick([Tables::getTable(Tables::PAGES) => [$col]]);
        db(onGetDB: function (TonicsQuery $db) use ($select, $col, $pageIdentifiers, &$pages) {
            $pages = $db->Select($select)
                ->From(Tables::getTable(Tables::PAGES))
                ->WhereIn($col, $pageIdentifiers)->FetchResult();
        });

        return $pages;
    }

    /**
     * @return array[]
     */
    public static function DataTableHeaders(): array
    {
        return [
            ['type' => '', 'slug' => Tables::PAGES . '::' . 'page_id', 'hide' => true, 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'page_id'],
            ['type' => 'text', 'slug' => Tables::PAGES . '::' . 'page_title', 'title' => 'Title', 'minmax' => '150px, 1.6fr', 'td' => 'page_title'],
            ['type' => '', 'slug' => Tables::PAGES . '::' . 'created_at', 'title' => 'Created', 'minmax' => '150px, 1fr', 'td' => 'created_at'],
            ['type' => '', 'slug' => Tables::PAGES . '::' . 'updated_at', 'title' => 'Updated', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
        ];
    }

    /**
     * This would unwrap, compare and sort the fields, it would be appended under the field_settings as _fieldDetailsSorted,
     * the benefit of this is that we wouldn't need to sort it again when rendering the page for fronted user
     *
     * @param $page
     *
     * @return mixed
     * @throws \Exception
     */
    public function unwrapCompareAndSortPageFieldSettings($page): mixed
    {
        return $this->fieldData->unwrapCompareAndSortFieldSettings($page);
    }

    /**
     * @param $url
     * @return array|false
     * @throws \Throwable
     */
    public function PageLayout($url): false|array
    {
        try {
            $page = $this->getPagesBy([$url]);
            if (empty($page)) {
                return false;
            }

            if ($page[0]->page_status != 1) {
                return false;
            }

            $event = FieldConfig::getFieldSelectionDropper();
            $event->processLogicWithEarlyAndLateCallbacks(FieldConfig::LayoutSelectorsForPages($page));
            $logicData = $event->getProcessedLogicData();
            $logicData['__core::inline_styles'] = $event->processInlineStyles();

            if (empty($logicData)) {
                return false;
            }

            return $logicData;

        } catch (\RuntimeException|\Exception $e) {
            return false;
        }
    }
}