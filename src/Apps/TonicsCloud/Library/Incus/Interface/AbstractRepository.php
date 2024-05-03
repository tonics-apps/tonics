<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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