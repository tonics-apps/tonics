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

namespace App\Modules\Customer\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Services\CustomerService;

class CustomerController
{
    private string $errorMessage = '';

    public function __construct (private readonly CustomerService $customerService, private readonly AbstractDataLayer $abstractDataLayer) {}

    /**
     * @throws \Throwable
     */
    public function index (): void
    {
        view('Modules::Customer/Views/index', [
            'DataTable' => [
                'headers'      => $this->customerService::DataTableHeaders(),
                'paginateData' => $this->customerService->getCustomers() ?? [],

            ],
            'SiteURL'   => AppConfig::getAppUrl(),
        ]);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function dataTable (): void
    {
        $entityBag = null;
        if ($this->abstractDataLayer->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->deleteMultiple($entityBag)) {
                response()->onSuccess([], "Records Deleted", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500, $this->errorMessage);
            }
        } elseif ($this->abstractDataLayer->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            /*if ($this->updateMultiple($entityBag)) {
                response()->onSuccess([], "Records Updated", more: AbstractDataLayer::DataTableEventTypeUpdate);
            } else {
                response()->onError(500);
            }*/
        }
    }

    /**
     * @param $entityBag
     *
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function deleteMultiple ($entityBag): bool
    {
        return $this->abstractDataLayer->dataTableDeleteMultiple([
            'id'        => 'user_id',
            'table'     => Tables::getTable(Tables::CUSTOMERS),
            'entityBag' => $entityBag,
            'onError'   => function ($msg) {
                $this->errorMessage = $msg;
            },
        ]);
    }
}