<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Track\Controllers\License;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Track\Data\TrackData;
use App\Modules\Track\Events\OnLicenseCreate;
use App\Modules\Track\Rules\TrackValidationRules;
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
        $cols = '`license_id`, `license_name`, `license_slug`, `license_status`, `license_attr`';
        $data = $this->getTrackData()->generatePaginationData(
            $cols,
            'license_name',
            $this->getTrackData()->getLicenseTable());

        $licenseListing = '';
        if ($data !== null){
            $licenseListing = $this->getTrackData()->adminLicenseListing($data->data);
            unset($data->data);
        }

        view('Modules::Track/Views/License/index', [
            'SiteURL' => AppConfig::getAppUrl(),
            'Data' => $data,
            'LicenseListing' => $licenseListing
        ]);
    }

    /**
     * @throws \Exception
     */
    public function create()
    {
        view('Modules::Track/Views/License/create', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone()
        ]);
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
            $insertReturning = db()->insertReturning($this->getTrackData()->getLicenseTable(), $widget, $this->getTrackData()->getLicenseColumns());

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
            'SiteURL' => AppConfig::getAppUrl(),
            'Data' => $onLicenseCreate->getAllToArray(),
            'TimeZone' => AppConfig::getTimeZone()
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
            $this->getTrackData()->updateWithCondition($licenseToUpdate, ['license_slug' => $slug], $this->getTrackData()->getLicenseTable());

            $slug = $licenseToUpdate['license_slug'];
            session()->flash(['License Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('licenses.edit', ['license' => $slug]));
        }catch (\Exception){
            session()->flash(['An Error Occurred Updating The License Item']);
            redirect(route('licenses.edit', [$slug]));
        }
    }

    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function delete(string $slug)
    {
        try {
            $this->getTrackData()->deleteWithCondition(whereCondition: "license_slug = ?", parameter: [$slug], table: $this->getTrackData()->getLicenseTable());
            session()->flash(['License Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('licenses.index'));
        } catch (\Exception){
            session()->flash(['Failed To Delete License']);
            redirect(route('licenses.index'));
        }
    }

    /**
     * @throws \Exception
     */
    public function deleteMultiple()
    {
        if (!input()->fromPost()->hasValue('itemsToDelete')){
            session()->flash(['Nothing To Delete'], type: Session::SessionCategories_FlashMessageInfo);
            redirect(route('licenses.index'));
        }

        $this->getTrackData()->deleteMultiple(
            $this->getTrackData()->getLicenseTable(),
            array_flip($this->getTrackData()->getLicenseColumns()),
            'license_id',
            onSuccess: function (){
                session()->flash(['License Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
                redirect(route('licenses.index'));
            },
            onError: function (){
                session()->flash(['Failed To Delete License']);
                redirect(route('licenses.index'));
            },
        );
    }

    /**
     * @return TrackData
     */
    public function getTrackData(): TrackData
    {
        return $this->trackData;
    }

}
