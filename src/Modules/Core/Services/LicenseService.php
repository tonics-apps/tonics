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

use App\Modules\Core\Library\AbstractService;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\Tables;

class LicenseService extends AbstractService
{
    use UniqueSlug;

    /**
     * @param string $slug
     *
     * @return mixed
     * @throws \Exception
     */
    public function getLicenseID (string $slug): mixed
    {
        $result = null;
        db(onGetDB: function ($db) use ($slug, &$result) {
            $table = self::getLicenseTable();
            $result = $db->row("SELECT `license_id` FROM $table WHERE `license_slug` = ?", $slug)->license_id ?? null;
        });

        return $result;
    }

    public static function getLicenseTable (): string
    {
        return Tables::getTable(Tables::LICENSES);
    }

    /**
     * @param int|null $currentLicenseID
     *
     * @return string
     * @throws \Exception
     */
    public function licenseSelectListing (int $currentLicenseID = null): string
    {
        $htmlFrag = '';

        $licenses = null;
        db(onGetDB: function ($db) use (&$licenses) {
            $table = self::getLicenseTable();
            $licenses = $db->run("SELECT * FROM $table");
        });

        foreach ($licenses as $license) {
            if ($currentLicenseID === $license->license_id) {
                $htmlFrag .= <<<HTML
<option class="license-selector-value" data-action="license" value='$license->license_id' selected>$license->license_name</option>
HTML;
            } else {
                $htmlFrag .= <<<HTML
<option class="license-selector-value" data-action="license" value='$license->license_id'>$license->license_name</option>
HTML;
            }

        }
        return $htmlFrag;
    }

    public function getLicenseItemsListing ($licenses): string
    {
        $frag = '';
        foreach ($licenses as $license) {
            $uniqueID = (isset($license->unique_id)) ? $license->unique_id : '';
            $name = (isset($license->name)) ? $license->name : '';
            $price = (isset($license->price)) ? $license->price : '';
            $isEnabled = (isset($license->is_enabled)) ? $license->is_enabled : '';
            $contract = (isset($license->licence_contract)) ? $license->licence_contract : '';

            if ($isEnabled === '0' || $isEnabled === false) {
                $isEnabledSelect = "<option value='1'>True</option> <option value='0' selected>False</option>";
            } else {
                $isEnabledSelect = "<option value='1' selected>True</option> <option value='0'>False</option>";
            }

            $frag .= <<<HTML
<li tabIndex="0"
               class="width:100% draggable menu-arranger-li cursor:move">
        <fieldset
            class="width:100% padding:default d:flex justify-content:center pointer-events:none">
            <legend class="bg:pure-black color:white padding:default pointer-events:none d:flex flex-gap:small align-items:center">
                <span class="menu-arranger-text-head">$name</span>
                <button class="dropdown-toggle bg:transparent border:none pointer-events:all cursor:pointer"
                        aria-expanded="false" aria-label="Expand child menu">
                    <svg class="icon:admin tonics-arrow-down color:white">
                        <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
                    </svg>
                </button>
            </legend>
            <form class="widgetSettings d:none flex-d:column license-widget-information pointer-events:all owl width:100%">
                <div class="form-group">
                    <label class="menu-settings-handle-name" for="license-name">License Name
                        <input id="license-name" name="name" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray" 
                        value="$name" placeholder="Overwrite the license name">
                        <input name="unique_id" type="hidden" value="$uniqueID" placeholder="Overwrite the license name">
                    </label>
                </div>
                <div class="form-group">
                    <label class="menu-settings-handle-name" for="license-price">Price
                        <input id="license-price" name="price" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray" 
                        value="$price">
                    </label>
                </div>        
                <div class="form-group position:relative">
                    <label class="menu-settings-handle-name screen-reader-text" for="license-contract">Licence Contract</label>
                        <input type="url" class="form-control input-checkout bg:white-one color:black border-width:default border:black license-contract" id="license-contract" 
                        name="licence_contract" placeholder="Upload Licence Contract, Can Be Empty" value="$contract">
                    <button aria-pressed="false" type="button" class="license-contract-button act-like-button text show-password bg:pure-black color:white cursor:pointer">Upload Contract</button>
                </div>
                <div class="form-group">
                    <label class="menu-settings-handle-name" for="is_enabled">Enable License
                         <select name="is_enabled" class="default-selector" id="is_enabled">
                                    $isEnabledSelect
                          </select>
                    </label>
                </div>
                <div class="form-group">
                    <button name="delete" class="delete-license-button listing-button border:none bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cursor:pointer act-like-button">
                        Delete License Item
                    </button>
                </div>
            </form>
        </fieldset>
    </li>
HTML;

        }

        return $frag;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function createLicense (array $ignore = []): array
    {
        $slug = $this->generateUniqueSlug(self::getLicenseTable(),
            'license_slug', helper()->slug(input()->fromPost()->retrieve('license_slug')));

        $license = [];
        $postColumns = array_flip($this->getLicenseColumns());
        foreach (input()->fromPost()->all() as $inputKey => $inputValue) {
            if (key_exists($inputKey, $postColumns) && input()->fromPost()->has($inputKey)) {

                if ($inputKey === 'created_at') {
                    $license[$inputKey] = helper()->date(datetime: $inputValue);
                    continue;
                }
                if ($inputKey === 'license_slug') {
                    $license[$inputKey] = $slug;
                    continue;
                }
                $license[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $license);
        if (!empty($ignores)) {
            foreach ($ignores as $v) {
                unset($license[$v]);
            }
        }

        return $license;
    }

    public function getLicenseColumns (): array
    {
        return Tables::$TABLES[Tables::LICENSES];
    }

    /**
     * @throws \Exception
     */
    public function licenseStoreRule (): array
    {
        $uniqueSlug = Tables::getTable(Tables::LICENSES) . ':license_slug';
        return [
            'license_name' => ['required', 'string'],
            'license_slug' => [
                'required', 'string', 'unique' => [
                    $uniqueSlug => input()->fromPost()->retrieve('license_slug', ''),
                ],
            ],
        ];
    }

    /**
     * @throws \Exception
     */
    public function licenseUpdateRule (): array
    {
        $widgetUniqueSlug = Tables::getTable(Tables::LICENSES) . ':license_slug:license_id';
        return [
            'license_name' => ['required', 'string'],
            'license_slug' => [
                'required', 'string', 'unique' => [
                    $widgetUniqueSlug => input()->fromPost()->retrieve('license_id', ''),
                ],
            ],
        ];
    }

    /**
     * @return \string[][]
     */
    public function licenseUpdateMultipleRule (): array
    {
        return [
            'license_id'   => ['numeric'],
            'license_name' => ['required', 'string'],
            'updated_at'   => ['required', 'string'],
        ];
    }

    public function licenseItemsStoreRule (): array
    {
        return [
            'licenseSlug'    => ['required', 'string'],
            'licenseDetails' => ['required', 'string'],
        ];
    }

}