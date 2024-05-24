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

namespace App\Modules\Core\Events\Licenses;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;
use stdClass;

class OnLicenseCreate implements EventInterface
{
    private stdClass $license;

    /**
     * @param stdClass $license
     */
    public function __construct (stdClass $license)
    {
        $this->license = $license;
        if (property_exists($license, 'created_at')) {
            $this->license->created_at = $this->getCatCreatedAt();
        }
        if (property_exists($license, 'updated_at')) {
            $this->license->updated_at = $this->getCatUpdatedAt();
        }
    }

    public function getCatCreatedAt (): string
    {
        return (isset($this->license->created_at)) ? str_replace(' ', 'T', $this->license->created_at) : '';
    }

    public function getCatUpdatedAt (): string
    {
        return (isset($this->license->updated_at)) ? str_replace(' ', 'T', $this->license->updated_at) : '';
    }

    public function getAll (): \stdClass
    {
        return $this->license;
    }

    public function getAllToArray (): array
    {
        return (array)$this->license;
    }

    public function getLicenseID (): string|int
    {
        return (isset($this->license->license_id)) ? $this->license->license_id : '';
    }

    public function getLicenseTitle (): string
    {
        return (isset($this->license->license_name)) ? $this->license->license_name : '';
    }

    public function getLicenseSlug (): string
    {
        return (isset($this->license->license_slug)) ? $this->license->license_slug : '';
    }

    public function getLicenseStatus (): string|int
    {
        return (isset($this->license->license_status)) ? $this->license->license_status : '';
    }

    public function getLicenseAttr (): mixed
    {
        return (isset($this->license->license_attr)) ? json_decode($this->license->license_attr) : '';
    }

    /**
     * @inheritDoc
     */
    public function event (): static
    {
        return $this;
    }

}