<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Payment\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class OrderController
{
    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function index(): void
    {
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::PURCHASES . '::' . 'slug_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'slug_id'],
            ['type' => '', 'slug' => Tables::PURCHASES . '::' . 'payment_status', 'title' => 'Status', 'minmax' => '40px, .4fr', 'td' => 'payment_status'],
            ['type' => '', 'slug' => Tables::PURCHASES . '::' . 'total_price', 'title' => 'Total Price', 'minmax' => '50px, .5fr', 'td' => 'total_price'],
            ['type' => '', 'slug' => Tables::PURCHASES . '::' . '_solution', 'title' => 'Solution', 'minmax' => '50px, .5fr', 'td' => '_solution'],
            ['type' => '', 'slug' => Tables::PURCHASES . '::' . 'invoice_id', 'title' => 'Invoice', 'minmax' => '100px, 1fr', 'td' => 'invoice_id'],
            ['type' => '', 'slug' => Tables::PURCHASES . '::' . 'created_at', 'title' => 'Created At', 'minmax' => '70px, .6fr', 'td' => 'created_at'],
        ];

        $data = null;
        db(onGetDB: function ($db) use (&$data){
            $purchaseTable = Tables::getTable(Tables::PURCHASES);
            $data = $db->Select('*, JSON_UNQUOTE(JSON_EXTRACT(others, "$.tonics_solution")) as _solution')
                ->From($purchaseTable)
                ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                    $db->WhereLike('slug_id', url()->getParam('query'));
                })
                ->OrderByDesc(table()->pickTable($purchaseTable, ['created_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));
        });

        view('Modules::Payment/Views/Orders/order_index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'paginateData' => $data ?? [],
            ],
            'SiteURL' => AppConfig::getAppUrl(),
        ]);
    }
}