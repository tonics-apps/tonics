<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library\View\Extensions\Interfaces;

use Devsrealm\TonicsTemplateSystem\TonicsView;

interface QueryModeHandlerInterface
{
    public function handleQueryData(TonicsView $tonicsView, string $query_name, \stdClass $queryData, callable $callback = null): string;
}