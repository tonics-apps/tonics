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

interface CloudServerInterface
{
    public function name(): string;

    public function createInstance(array $data);

    public function destroyInstance(array $data);

    public function resizeInstance(array $data);

    public function changeInstanceStatus(array $data);

    /**
     * Fetches Linode instances using pagination and returns a generator.
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
    public function getInstances(array $data): \Generator;

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
}