<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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

}