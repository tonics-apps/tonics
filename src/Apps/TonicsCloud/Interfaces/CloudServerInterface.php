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

interface CloudServerInterface
{
    const STATUS_STOPPED = 'STATUS_STOPPED';
    const STATUS_RUNNING = 'STATUS_RUNNING';

    /**
     * The provider name, can be same as `name()` if you do not know what to use.
     * @return string
     */
    public function displayName(): string;

    /**
     * Ensure this is non-changeable and unique to the server provider
     * @return string
     */
    public function name(): string;

    public function createInstance(array $data);

    public function destroyInstance(array $data);

    public function resizeInstance(array $data);

    public function changeInstanceStatus(array $data);

    /**
     * Check if instance status is equal to $statusString
     * @param array $data
     * @param string $statusString
     * To promote, interoperability, let this be any of CloudServerInterface::STATUS_STOPPED, CloudServerInterface::STATUS_RUNNING, or any
     * from CloudServerInterface
     * @return bool
     */
    public function isStatus(array $data, string $statusString): bool;

    /**
     * Gets instance status
     * @param array $data
     * @return mixed
     */
    public function instanceStatus(array $data): mixed;

    /**
     * Get instance data, if instance can't be found, throw an exception with message: Not found or exception code: 404.
     *
     * <br>
     * The difference between this method and the `info()` method is that this method should return the instance info from the cloud server,
     * while the info method would typically be from the db (but not required), however, ensure this method always returns the info from the db
     * @param array $data
     * @return mixed
     */
    public function instance(array $data): mixed;

    /**
     * Get instance info, if instance can't be found, return empty array, otherwise, return the following array key and value:
     *
     * <code>
     * [
     * 'ipv4'   => 'xx.xx.xx.xxx',
     * 'ipv6'   => 'xxxx:xxxx::xxxx:xxxx:xxxx:xxxx/128'],
     * 'region' =>  'us-central'
     * ];
     * </code>
     * @param array $data
     * @return array
     */
    public function info(array $data): array;

    /**
     * Fetches instances using pagination and returns a generator.
     *
     * @param array $data Optional parameters:
     * <br>
     * - 'page' (int): The starting page for fetching instances (default: 1),
     * <br>
     * - 'errorHandler' (callable): A callable function to handle errors or rate limit exceeded situations.
     * <br>
     * - 'maxPages' (int): The maximum number of pages to fetch (default: null).
     * <br>
     * - 'nextPageHandler' (callable): A callable function to handle the next page action.
     * <br>
     * - 'uri' (optional): The URI you want to make a request to
     *
     * @return \Generator The generator that yields instances.
     */
    public function instances(array $data): \Generator;

    /**
     * Return an array of region id and label, e.g:
     *
     * <code>
     * [
            ['label' => 'Mumbai, IN', 'id' => 'ap-west'],
            ['label' => 'Toronto, CA', 'id' => 'ca-central'],
            [..],
        ];
     * </code>
     * @return array
     */
    public function regions(): array;

    /**
     * Return an array of prices in the following format:
     *
     * ```
     * 'g6-nanode-1' => [
     *     'service_type' => 'Server',
     *     'description' => 'Shared 1GB RAM - 1CPU Core - 25GB SSD',
     *     'price' => [
     *         "monthly" => 12.0
     *     ],
     *     "memory" => 1024,
     *     "disk" => 25600
     * ],
     * 'g6-standard-1' => [
     *     'service_type' => 'Server',
     *     'description' => 'Shared 2GB RAM - 1CPU Core - 50GB SSD',
     *     'price' => [
     *         "monthly" => 20
     *     ],
     *     "memory" => 2048,
     *     "disk" => 51200
     * ],
     * [...]
     * ```
     * @return array
     */
    public function prices(): array;
}