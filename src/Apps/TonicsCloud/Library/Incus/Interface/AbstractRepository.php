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

namespace App\Apps\TonicsCloud\Library\Incus\Interface;

use App\Apps\TonicsCloud\Library\Incus\Client;

abstract class AbstractRepository
{
    private array $params = [];


    /**
     * AbstractRepository constructor.
     *
     * @param Client $client incus client
     */
    public function __construct(protected Client $client)
    {

    }

    /**
     * Returns base URI to the repository-specific API.
     */
    abstract protected function getEndPoint(): string;

    /**
     * Converts an array to a query string.
     *
     * ```
     * $data = array(
     *     'name' => 'John Doe',
     *     'age' => 30,
     *     'email' => 'john@example.com',
     *     'interests' => array('programming', 'music', 'sports')
     * );
     *
     * $queryString = arrayToQueryString($data);
     * echo $queryString; // name=John+Doe&interests%5B0%5D=programming&interests%5B1%5D=music&interests%5B2%5D=sports
     * ```
     *
     * @param array $data The array to be converted.
     * @param array $includedKeys Optional. An array of keys to be included in the query string.
     * @return string The generated query string.
     *
     *
     */
    protected function convertArrayToQueryString(array $data, array $includedKeys = []): string
    {
        $query = '';
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $query .= $this->convertArrayToQueryString($value, $includedKeys);
            } elseif (empty($includedKeys) || in_array($key, $includedKeys)) {
                $key = urlencode($key);
                $value = urlencode($value);
                $query .= $key . '=' . $value . '&';
            }
        }

        return rtrim($query, '&');
    }

    /**
     * @param $data
     * @param array $includedKeys
     * @return array
     */
    protected function convertArrayToHttpHeader($data, array $includedKeys = []): array
    {
        $headers = array();

        foreach ($data as $key => $value) {
            if (empty($includedKeys) || in_array($key, $includedKeys)) {
                $headers[] =  $key . ": " . $value;
            }
        }

        return $headers;
    }

}