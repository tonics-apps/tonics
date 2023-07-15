<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Customer\Controllers;

use App\Modules\Core\Configs\AppConfig;
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
        $purchaseTable = Tables::getTable(Tables::PURCHASES);
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::PURCHASES . '::' . 'slug_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'slug_id'],
            ['type' => '', 'slug' => Tables::PURCHASES . '::' . 'payment_status', 'title' => 'Status', 'minmax' => '40px, .4fr', 'td' => 'payment_status'],
            ['type' => '', 'slug' => Tables::PURCHASES . '::' . 'total_price', 'title' => 'Total Price', 'minmax' => '50px, .5fr', 'td' => 'total_price'],
            ['type' => '', 'slug' => Tables::PURCHASES . '::' . 'invoice_id', 'title' => 'Invoice', 'minmax' => '100px, 1fr', 'td' => 'invoice_id'],
            ['type' => '', 'slug' => Tables::PURCHASES . '::' . 'created_at', 'title' => 'Created At', 'minmax' => '70px, .6fr', 'td' => 'created_at'],
        ];

        if (\session()::getUserID() !== null){
            $data = null;
            db(onGetDB: function ($db) use ($purchaseTable, &$data){
                $data = $db->Select('*, CONCAT("/customer/order/", LOWER(JSON_UNQUOTE(JSON_EXTRACT(others, "$.tonics_solution"))), "/", slug_id ) as _view')
                    ->From($purchaseTable)
                    ->WhereEquals('fk_customer_id', \session()::getUserID())
                    ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                        $db->WhereLike('slug_id', url()->getParam('query'));
                    })->OrderByDesc(table()->pickTable($purchaseTable, ['created_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));
            });
        }

        view('Modules::Customer/Views/Orders/order_index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'paginateData' => $data ?? [],
                'dataTableType' => 'VIEW_LINK',
            ],
            'SiteURL' => AppConfig::getAppUrl(),
        ]);
    }


    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function audioTonicsPurchaseDetails($slugID): void
    {
        $purchaseRecord = null;
        db(onGetDB: function ($db) use ($slugID, &$purchaseRecord){
            $purchaseTable = Tables::getTable(Tables::PURCHASES);
            $customerTable = Tables::getTable(Tables::CUSTOMERS);
            $select = "total_price, email, $purchaseTable.others, $purchaseTable.slug_id, invoice_id";
            $purchaseRecord = $db->row(<<<SQL
SELECT $select
FROM $purchaseTable
JOIN $customerTable c ON c.user_id = $purchaseTable.fk_customer_id
WHERE $purchaseTable.`slug_id` = ? AND `payment_status` = ?
SQL, $slugID, 'completed');
        });

        if (isset($purchaseRecord->others)){
            $purchaseRecord->others = json_decode($purchaseRecord->others);
        }

        view('Modules::Customer/Views/Orders/AudioTonics/order_details', [
            'OrderDetails' => $purchaseRecord,
            'SlugID' => $slugID,
            'SiteURL' => AppConfig::getAppUrl(),
        ]);
    }

    /**
     * @param $slugID
     * @return void
     * @throws \Throwable
     */
    public function tonicsCloudPurchaseDetails($slugID): void
    {
        $purchaseRecord = null;
        db(onGetDB: function ($db) use ($slugID, &$purchaseRecord){
            $purchaseTable = Tables::getTable(Tables::PURCHASES);
            $customerTable = Tables::getTable(Tables::CUSTOMERS);
            $select = "total_price, email, $purchaseTable.others, $purchaseTable.slug_id, invoice_id";
            $purchaseRecord = $db->row(<<<SQL
SELECT $select
FROM $purchaseTable
JOIN $customerTable c ON c.user_id = $purchaseTable.fk_customer_id
WHERE $purchaseTable.`slug_id` = ? AND `payment_status` = ?
SQL, $slugID, 'completed');
        });

        if (isset($purchaseRecord->others)){
            $purchaseRecord->others = json_decode($purchaseRecord->others);
        }

        view('Modules::Customer/Views/Orders/TonicsCloud/order_details', [
            'OrderDetails' => $purchaseRecord,
            'SlugID' => $slugID,
            'SiteURL' => AppConfig::getAppUrl(),
        ]);
    }
}