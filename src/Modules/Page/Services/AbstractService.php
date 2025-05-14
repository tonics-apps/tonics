<?php
/*
 *     Copyright (c) 2025. Olayemi Faruq <olayemi@tonics.app>
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

use App\Modules\Core\Configs\AppConfig;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class AbstractService extends \App\Modules\Core\Library\AbstractService
{
    /**
     * @param array $tableHeaders
     * @param $data
     * @param string $tableType
     *
     * @return string
     * @throws \Throwable
     */
    public static function RenderTableRow(array $tableHeaders, $data, string $tableType = 'TONICS_CLOUD'): string
    {
        if (!is_array($data)) {
            $data = [$data];
        }

        /** @var TonicsView $viewRender */
        $viewRender = view('Modules::Core/Views/Templates/extends/_data_table_components/table_blocks', [
            'DataTable' => [
                'headers' => $tableHeaders,
                'paginateData' => ['data' => $data],
                'dataTableType' => $tableType,
            ],
            'SiteURL' => AppConfig::getAppUrl(),
        ], TonicsView::RENDER_TOKENIZE_ONLY);

        $viewRender->renderABlock('DataTable::Table_before_data_table_Hook');
        return $viewRender->renderABlock('DataTable::TableRow');
    }
}