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

namespace App\Modules\Core\Controllers\License;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\Licenses\OnLicenseCreate;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Services\LicenseService;
use App\Modules\Core\Validation\Traits\Validator;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use JetBrains\PhpStorm\NoReturn;

class LicenseController
{
    use Validator;

    private LicenseService    $licenseService;
    private AbstractDataLayer $abstractDataLayer;

    public function __construct (LicenseService $licenseService, AbstractDataLayer $abstractDataLayer,)
    {
        $this->licenseService = $licenseService;
        $this->abstractDataLayer = $abstractDataLayer;
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function index ()
    {
        $table = Tables::getTable(Tables::LICENSES);
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::LICENSES . '::' . 'license_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'license_id'],
            ['type' => 'text', 'slug' => Tables::LICENSES . '::' . 'license_name', 'title' => 'Title', 'minmax' => '150px, 1.6fr', 'td' => 'license_name'],
            ['type' => 'date_time_local', 'slug' => Tables::LICENSES . '::' . 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
        ];

        $data = null;
        db(onGetDB: function ($db) use ($table, &$data) {
            $tblCol = '*, CONCAT("/admin/tools/license/", license_slug, "/edit" ) as _edit_link, CONCAT("/admin/tools/license/items/", license_slug, "/builder") as _builder_link';
            $data = $db->Select($tblCol)
                ->From($table)
                ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                    $db->WhereLike('license_name', url()->getParam('query'));

                })->when(url()->hasParamAndValue('start_date') && url()->hasParamAndValue('end_date'), function (TonicsQuery $db) use ($table) {
                    $db->WhereBetween(table()->pickTable($table, ['created_at']), db()->DateFormat(url()->getParam('start_date')), db()->DateFormat(url()->getParam('end_date')));

                })->OrderByDesc(table()->pickTable($table, ['updated_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));
        });

        view('Modules::Core/Views/License/index', [
            'DataTable' => [
                'headers'       => $dataTableHeaders,
                'paginateData'  => $data ?? [],
                'dataTableType' => 'EDITABLE_BUILDER',

            ],
            'SiteURL'   => AppConfig::getAppUrl(),
        ]);
    }

    /**
     * @return void
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
                response()->onError(500);
            }
        } elseif ($this->abstractDataLayer->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->updateMultiple($entityBag)) {
                response()->onSuccess([], "Records Updated", more: AbstractDataLayer::DataTableEventTypeUpdate);
            } else {
                response()->onError(500);
            }
        }
    }

    /**
     * @param $entityBag
     *
     * @return bool
     * @throws \Exception
     */
    public function deleteMultiple ($entityBag): bool
    {
        return $this->abstractDataLayer->dataTableDeleteMultiple([
            'id'        => 'license_id',
            'table'     => Tables::getTable(Tables::LICENSES),
            'entityBag' => $entityBag,
        ]);
    }

    /**
     * @param $entityBag
     *
     * @return bool
     * @throws \Exception
     */
    protected function updateMultiple ($entityBag): bool
    {
        return $this->abstractDataLayer->dataTableUpdateMultiple([
            'id'        => 'license_id',
            'table'     => Tables::getTable(Tables::LICENSES),
            'rules'     => $this->licenseService->licenseUpdateMultipleRule(),
            'entityBag' => $entityBag,
        ]);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function create ()
    {
        view('Modules::Core/Views/License/create');
    }

    /**
     * @return void
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function store ()
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->licenseService->licenseStoreRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('admin.licenses.create'));
        }

        try {
            $widget = $this->licenseService->createLicense();
            $insertReturning = null;
            db(onGetDB: function ($db) use ($widget, &$insertReturning) {
                $insertReturning = $db->insertReturning($this->licenseService::getLicenseTable(), $widget, $this->licenseService->getLicenseColumns(), 'license_id');
            });

            $onLicenseCreate = new OnLicenseCreate($insertReturning);
            event()->dispatch($onLicenseCreate);

            session()->flash(['License Created'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('admin.licenses.edit', ['license' => $onLicenseCreate->getLicenseSlug()]));
        } catch (\Exception) {
            session()->flash(['An Error Occurred Creating The License Item'], input()->fromPost()->all());
            redirect(route('admin.licenses.create'));
        }
    }

    /**
     * @param string $slug
     *
     * @return void
     * @throws \Throwable
     */
    public function edit (string $slug)
    {
        $menu = $this->abstractDataLayer->selectWithCondition($this->licenseService::getLicenseTable(), ['*'], "license_slug = ?", [$slug]);
        if (!is_object($menu)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $onLicenseCreate = new OnLicenseCreate($menu);
        view('Modules::Core/Views/License/edit', [
            'Data' => $onLicenseCreate->getAllToArray(),
        ]);
    }

    /**
     * @param string $slug
     *
     * @return void
     * @throws \ReflectionException
     * @throws \Throwable
     */
    #[NoReturn] public function update (string $slug)
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->licenseService->licenseUpdateRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors());
            redirect(route('admin.licenses.edit', [$slug]));
        }

        try {
            $licenseToUpdate = $this->licenseService->createLicense();
            $licenseToUpdate['license_slug'] = helper()->slug(input()->fromPost()->retrieve('license_slug'));

            db(onGetDB: function ($db) use ($slug, $licenseToUpdate) {
                $db->FastUpdate($this->licenseService::getLicenseTable(), $licenseToUpdate, db()->Where('license_slug', '=', $slug));
            });

            $slug = $licenseToUpdate['license_slug'];
            session()->flash(['License Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('admin.licenses.edit', ['license' => $slug]));
        } catch (\Exception) {
            session()->flash(['An Error Occurred Updating The License Item']);
            redirect(route('admin.licenses.edit', [$slug]));
        }
    }

}
