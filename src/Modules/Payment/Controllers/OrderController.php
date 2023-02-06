<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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
     */
    public function index()
    {
        $purchaseTable = Tables::getTable(Tables::PURCHASES);
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::PURCHASES . '::' . 'slug_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'slug_id'],
            ['type' => '', 'slug' => Tables::PURCHASES . '::' . 'payment_status', 'title' => 'Status', 'minmax' => '40px, .4fr', 'td' => 'payment_status'],
            ['type' => '', 'slug' => Tables::PURCHASES . '::' . 'total_price', 'title' => 'Total Price', 'minmax' => '50px, .5fr', 'td' => 'total_price'],
            ['type' => '', 'slug' => Tables::PURCHASES . '::' . '_solution', 'title' => 'Solution', 'minmax' => '50px, .5fr', 'td' => '_solution'],
            ['type' => '', 'slug' => Tables::PURCHASES . '::' . 'invoice_id', 'title' => 'Invoice', 'minmax' => '100px, 1fr', 'td' => 'invoice_id'],
            ['type' => '', 'slug' => Tables::PURCHASES . '::' . 'created_at', 'title' => 'Created At', 'minmax' => '70px, .6fr', 'td' => 'created_at'],
        ];

        $data = db()->Select('*, JSON_UNQUOTE(JSON_EXTRACT(others, "$.tonics_solution")) as _solution')
            ->From($purchaseTable)
            ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                $db->WhereLike('slug_id', url()->getParam('query'));
            })
            ->OrderByDesc(table()->pickTable($purchaseTable, ['created_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));

        view('Modules::Payment/Views/Orders/order_index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'paginateData' => $data ?? [],
            ],
            'SiteURL' => AppConfig::getAppUrl(),
        ]);
    }
}