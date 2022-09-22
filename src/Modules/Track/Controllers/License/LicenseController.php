<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\Controllers\License;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Track\Data\TrackData;
use App\Modules\Track\Events\OnLicenseCreate;
use App\Modules\Track\Rules\TrackValidationRules;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use JetBrains\PhpStorm\NoReturn;
use function view;

class LicenseController
{
    use TrackValidationRules, Validator;


    private TrackData $trackData;

    public function __construct(TrackData $trackData)
    {
        $this->trackData = $trackData;
    }

    /**
     * @throws \Exception
     */
    public function index()
    {
        $table = Tables::getTable(Tables::LICENSES);
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::LICENSES . '::' . 'license_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'license_id'],
            ['type' => 'text', 'slug' => Tables::LICENSES . '::' . 'license_name', 'title' => 'Title', 'minmax' => '150px, 1.6fr', 'td' => 'license_name'],
            ['type' => 'date_time_local', 'slug' => Tables::LICENSES . '::' . 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
        ];

        $tblCol = '*, CONCAT("/admin/tools/license/", license_slug, "/edit" ) as _edit_link, CONCAT("/admin/tools/license/items/", license_slug, "/builder") as _builder_link';

        $data = db()->Select($tblCol)
            ->From($table)
            ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                $db->WhereLike('license_name', url()->getParam('query'));

            })->when(url()->hasParamAndValue('start_date') && url()->hasParamAndValue('end_date'), function (TonicsQuery $db) use ($table) {
                $db->WhereBetween(table()->pickTable($table, ['created_at']), db()->DateFormat(url()->getParam('start_date')), db()->DateFormat(url()->getParam('end_date')));

            })->OrderByDesc(table()->pickTable($table, ['updated_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));

        view('Modules::Track/Views/License/index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'paginateData' => $data ?? [],
                'dataTableType' => 'EDITABLE_BUILDER',

            ],
            'SiteURL' => AppConfig::getAppUrl(),
        ]);
    }

    /**
     * @throws \Exception
     */
    public function dataTable(): void
    {
        $entityBag = null;
        if ($this->getTrackData()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->deleteMultiple($entityBag)) {
                response()->onSuccess([], "Records Deleted", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500);
            }
        } elseif ($this->getTrackData()->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
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
     * @throws \Exception
     */
    public function create()
    {
        view('Modules::Track/Views/License/create');
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function store()
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->licenseStoreRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('licenses.create'));
        }

        try {
            $widget = $this->getTrackData()->createLicense();
            $insertReturning = db()->insertReturning($this->getTrackData()->getLicenseTable(), $widget, $this->getTrackData()->getLicenseColumns(), 'license_id');

            $onLicenseCreate = new OnLicenseCreate($insertReturning, $this->getTrackData());
            event()->dispatch($onLicenseCreate);

            session()->flash(['License Created'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('licenses.edit', ['license' => $onLicenseCreate->getLicenseSlug()]));
        } catch (\Exception){
            session()->flash(['An Error Occurred Creating The License Item'], input()->fromPost()->all());
            redirect(route('licenses.create'));
        }
    }

    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function edit(string $slug)
    {
        $menu = $this->getTrackData()->selectWithCondition($this->getTrackData()->getLicenseTable(), ['*'], "license_slug = ?", [$slug]);
        if (!is_object($menu)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $onLicenseCreate = new OnLicenseCreate($menu, $this->getTrackData());
        view('Modules::Track/Views/License/edit', [
            'Data' => $onLicenseCreate->getAllToArray(),
        ]);
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    #[NoReturn] public function update(string $slug)
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->licenseUpdateRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors());
            redirect(route('licenses.edit', [$slug]));
        }

        try {
            $licenseToUpdate = $this->getTrackData()->createLicense();
            $licenseToUpdate['license_slug'] = helper()->slug(input()->fromPost()->retrieve('license_slug'));
            db()->FastUpdate($this->getTrackData()->getLicenseTable(), $licenseToUpdate, db()->Where('license_slug', '=', $slug));

            $slug = $licenseToUpdate['license_slug'];
            session()->flash(['License Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('licenses.edit', ['license' => $slug]));
        }catch (\Exception){
            session()->flash(['An Error Occurred Updating The License Item']);
            redirect(route('licenses.edit', [$slug]));
        }
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws \Exception
     */
    protected function updateMultiple($entityBag): bool
    {
        return $this->getTrackData()->dataTableUpdateMultiple('license_id', Tables::getTable(Tables::LICENSES), $entityBag, $this->licenseUpdateMultipleRule());
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws \Exception
     */
    public function deleteMultiple($entityBag): bool
    {
        return $this->getTrackData()->dataTableDeleteMultiple('license_id', Tables::getTable(Tables::LICENSES), $entityBag);
    }

    /**
     * @return TrackData
     */
    public function getTrackData(): TrackData
    {
        return $this->trackData;
    }

}
