<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Core\Services;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CustomerService
{
    /**
     * @return array[]
     */
    public static function DataTableHeaders (): array
    {
        return [
            ['type' => '', 'hide' => true, 'slug' => Tables::CUSTOMERS . '::' . 'user_id', 'title' => 'Status', 'minmax' => '40px, .4fr', 'td' => 'user_id',],
            ['type' => '', 'slug' => Tables::CUSTOMERS . '::' . 'user_name', 'title' => 'UserName', 'minmax' => '50px, .5fr', 'td' => 'user_name',],
            ['type' => '', 'slug' => Tables::CUSTOMERS . '::' . 'email', 'title' => 'Email', 'minmax' => '40px, .4fr', 'td' => 'email',],
            ['type' => '', 'slug' => Tables::CUSTOMERS . '::' . 'email_verified_at', 'title' => 'Email Verified At', 'minmax' => '40px, .4fr', 'td' => 'email_verified_at',],
            ['type' => '', 'slug' => Tables::CUSTOMERS . '::' . 'is_guest', 'title' => 'Guest', 'minmax' => '20px, .1fr', 'td' => 'is_guest',],
            ['type' => '', 'slug' => Tables::CUSTOMERS . '::' . 'role', 'title' => 'Role', 'minmax' => '20px, .1fr', 'td' => 'role',],
            ['type' => '', 'slug' => Tables::CUSTOMERS . '::' . 'created_at', 'title' => 'Created At', 'minmax' => '50px, .4fr', 'td' => 'created_at'],

        ];
    }

    /**
     * @return object|null
     * @throws \Throwable
     */
    public function getCustomers (): ?object
    {
        $data = null;
        db(onGetDB: function (TonicsQuery $db) use (&$data) {

            $columns = table()->except([Tables::getTable(Tables::CUSTOMERS) => ['user_password', 'settings']]);
            $table = Tables::getTable(Tables::CUSTOMERS);
            $data = $db->Select($columns)
                ->From($table)
                ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                    $db->WhereLike('user_name', url()->getParam('query'));

                })->when(url()->hasParamAndValue('start_date') && url()->hasParamAndValue('end_date'), function (TonicsQuery $db) use ($table) {
                    $db->WhereBetween(table()->pickTable($table, ['created_at']), db()->DateFormat(url()->getParam('start_date')), db()->DateFormat(url()->getParam('end_date')));

                })->OrderByDesc(table()->pickTable($table, ['updated_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));
        });

        return $data;
    }
}