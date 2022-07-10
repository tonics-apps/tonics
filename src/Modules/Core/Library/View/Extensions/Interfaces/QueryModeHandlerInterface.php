<?php

namespace App\Modules\Core\Library\View\Extensions\Interfaces;

use Devsrealm\TonicsTemplateSystem\TonicsView;

interface QueryModeHandlerInterface
{
    public function handleQueryData(TonicsView $tonicsView, \stdClass $queryData): string;
}