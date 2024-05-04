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

namespace App\Modules\Track\Events;

use App\Modules\Track\Data\TrackData;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;
use stdClass;

class OnLicenseCreate implements EventInterface
{
    private stdClass $license;
    private TrackData $licenseData;

    /**
     * @param stdClass $license
     * @param TrackData|null $trackData
     */
    public function __construct(stdClass $license, TrackData $trackData = null)
    {
        $this->license = $license;
        if (property_exists($license, 'created_at')){
            $this->license->created_at = $this->getCatCreatedAt();
        }
        if (property_exists($license, 'updated_at')){
            $this->license->updated_at = $this->getCatUpdatedAt();
        }

        if ($trackData){
            $this->licenseData = $trackData;
        }
    }

    public function getAll(): \stdClass
    {
        return $this->license;
    }

    public function getAllToArray(): array
    {
        return (array)$this->license;
    }

    public function getLicenseID(): string|int
    {
        return (isset($this->license->license_id)) ? $this->license->license_id : '';
    }

    public function getLicenseTitle(): string
    {
        return (isset($this->license->license_name)) ? $this->license->license_name : '';
    }

    public function getLicenseSlug(): string
    {
        return (isset($this->license->license_slug)) ? $this->license->license_slug : '';
    }


    public function getLicenseStatus(): string|int
    {
        return (isset($this->license->license_status)) ? $this->license->license_status : '';
    }

    public function getLicenseAttr(): mixed
    {
        return (isset($this->license->license_attr)) ? json_decode($this->license->license_attr) : '';
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    public function getCatCreatedAt(): string
    {
        return (isset($this->license->created_at)) ? str_replace(' ', 'T', $this->license->created_at) : '';
    }

    public function getCatUpdatedAt(): string
    {
        return (isset($this->license->updated_at)) ? str_replace(' ', 'T', $this->license->updated_at) : '';
    }

    /**
     * @return TrackData
     */
    public function getLicenseData(): TrackData
    {
        return $this->licenseData;
    }

    /**
     * @param TrackData $licenseData
     * @return OnLicenseCreate
     */
    public function setLicenseData(TrackData $licenseData): OnLicenseCreate
    {
        $this->licenseData = $licenseData;
        return $this;
    }

}