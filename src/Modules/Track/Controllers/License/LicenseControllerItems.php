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
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Track\Data\TrackData;
use App\Modules\Track\Events\OnLicenseCreate;
use App\Modules\Track\Rules\TrackValidationRules;

class LicenseControllerItems
{
    use TrackValidationRules, Validator;

    private TrackData $trackData;

    /**
     * @param TrackData $trackData
     */
    public function __construct(TrackData $trackData)
    {
        $this->trackData = $trackData;
    }

    /**
     * @throws \Exception
     */
    public function index(string $slug)
    {

        $licenseID = $this->getTrackData()->getLicenseID($slug);
        if ($licenseID === null) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $licenseData =  $this->getTrackData()->selectWithCondition($this->getTrackData()::getLicenseTable(), ['*'], "license_id = ?", [$licenseID]);
        $onLicenseCreate = new OnLicenseCreate($licenseData, $this->getTrackData());

        view('Modules::Track/Views/License/Items/index', [
            'LicenseItemsListing' => $this->getTrackData()->getLicenseItemsListing($onLicenseCreate->getLicenseAttr()),
            'LicenseBuilderName' => ucwords(str_replace('-', ' ', $slug)),
            'LicenseSlug' => $slug,
            'LicenseID' => $licenseID,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function store()
    {
        $licenseSlug = input()->fromPost()->retrieve('licenseSlug', '');
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->licenseItemsStoreRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors(), $validator->getInputData());
            redirect(route('licenses.items.index', ['license' => $licenseSlug]));
        }

        try {
            $licenseDetails = input()->fromPost()->retrieve('licenseDetails');
            $licenseDetails = json_decode($licenseDetails);
            if (is_array($licenseDetails)){
                $licenseDetails = array_map(function ($license){
                    if (!isset($license->unique_id)){
                        $license->unique_id = helper()->randomString();
                    }
                    return $license;
                }, $licenseDetails);
            }
            $data = [
                'license_attr' => json_encode($licenseDetails)
            ];

            db(onGetDB: function ($db) use ($licenseSlug, $data) {
                $db->FastUpdate($this->getTrackData()::getLicenseTable(), $data, db()->Where('license_slug', '=', $licenseSlug));
            });

            session()->flash(['License Successfully Saved'], $menuDetails ?? [], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('licenses.items.index', ['license' => $licenseSlug]));
        }catch (\Exception){
            session()->flash(['An Error Occurred Saving License'], []);
            redirect(route('licenses.items.index', ['license' => $licenseSlug]));
        }
    }

        /**
     * @return TrackData
     */
    public function getTrackData(): TrackData
    {
        return $this->trackData;
    }

}