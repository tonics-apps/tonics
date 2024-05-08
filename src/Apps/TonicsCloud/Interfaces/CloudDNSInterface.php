<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Apps\TonicsCloud\Interfaces;

interface CloudDNSInterface
{

    public function name(): string;

    public function createDomain(array $data);

    public function getDomain(array $data);

    public function updateDomain(array $data);

    public function deleteDomain(array $data);

    /**
     * Create a domain record, pass the domain_id to identify the domain, among other info.
     * Example:
     *
     * ```
     * $data =  ["domain_id" => 2322017, "type" => ...];
     * ```
     * @param array $data
     * @return mixed
     */
    public function createDomainRecord(array $data): mixed;

    /**
     * Get a domain record, pass the domain_id to identify the domain, the record_id to identify the record you are updating among other info.
     * Example:
     *
     * ```
     * $data =  ["domain_id" => 2322017, "record_id" => 28394188];
     * ```
     * @param array $data
     * @return mixed
     */
    public function getDomainRecord(array $data): mixed;

    /**
     * Update a domain record, pass the domain_id to identify the domain, the record_id to identify the record you are updating among other info.
     * Example:
     *
     * ```
     * $data =  ["domain_id" => 2322017, "record_id" => 28394188, 'type' => ...,];
     * ```
     * @param array $data
     * @return mixed
     */
    public function updateDomainRecord(array $data): mixed;

    /**
     * Delete a domain record, pass the domain_id to identify the domain and the record_id to identify the record you are deleting.
     * Example:
     *
     * ```
     * $data =  ["domain_id" => 2322017, "record_id" => 28394188];
     * ```
     * @param array $data
     */
    public function deleteDomainRecord(array $data);

    /**
     * Return the nameservers of this DNS, e.g:
     *
     * ```
     * return [
     *  'ns1.linode.com',
     *  'ns2.linode.com',
     *  'ns3.linode.com',
     *  'ns4.linode.com',
     *  'ns5.linode.com'
     * ]
     * ```
     *
     * @return array
     */
    public function nameServers(): array;

}