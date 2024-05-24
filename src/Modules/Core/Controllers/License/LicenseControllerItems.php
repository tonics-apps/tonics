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

use App\Modules\Core\Events\Licenses\OnLicenseCreate;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Services\LicenseService;
use App\Modules\Core\Validation\Traits\Validator;

class LicenseControllerItems
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
     * @throws \Exception
     */
    public function index (string $slug): void
    {

        $licenseID = $this->licenseService->getLicenseID($slug);
        if ($licenseID === null) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $licenseData = $this->abstractDataLayer->selectWithCondition($this->licenseService::getLicenseTable(), ['*'], "license_id = ?", [$licenseID]);
        $onLicenseCreate = new OnLicenseCreate($licenseData);

        view('Modules::Core/Views/License/Items/index', [
            'LicenseItemsListing' => $this->licenseService->getLicenseItemsListing($onLicenseCreate->getLicenseAttr()),
            'LicenseBuilderName'  => ucwords(str_replace('-', ' ', $slug)),
            'LicenseSlug'         => $slug,
            'LicenseID'           => $licenseID,
        ]);
    }

    /**
     * @return void
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function store (): void
    {
        $licenseSlug = input()->fromPost()->retrieve('licenseSlug', '');
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->licenseService->licenseItemsStoreRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), $validator->getInputData());
            redirect(route('admin.licenses.items.index', ['license' => $licenseSlug]));
        }

        try {
            $licenseDetails = input()->fromPost()->retrieve('licenseDetails');
            $licenseDetails = json_decode($licenseDetails);
            if (is_array($licenseDetails)) {
                $licenseDetails = array_map(function ($license) {
                    if (!isset($license->unique_id)) {
                        $license->unique_id = helper()->randomString();
                    }
                    return $license;
                }, $licenseDetails);
            }
            $data = [
                'license_attr' => json_encode($licenseDetails),
            ];

            db(onGetDB: function ($db) use ($licenseSlug, $data) {
                $db->FastUpdate($this->licenseService::getLicenseTable(), $data, db()->Where('license_slug', '=', $licenseSlug));
            });

            session()->flash(['License Successfully Saved'], $menuDetails ?? [], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('admin.licenses.items.index', ['license' => $licenseSlug]));
        } catch (\Exception) {
            session()->flash(['An Error Occurred Saving License'], []);
            redirect(route('admin.licenses.items.index', ['license' => $licenseSlug]));
        }
    }


}