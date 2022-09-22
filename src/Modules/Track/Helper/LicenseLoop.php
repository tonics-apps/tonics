<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\Helper;

use App\Modules\Core\Library\Tables;
use App\Modules\Core\Library\View\Extensions\Interfaces\QueryModeHandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class LicenseLoop implements QueryModeHandlerInterface
{

    public function handleQueryData(TonicsView $tonicsView, string $query_name, \stdClass $queryData, callable $callback = null): string
    {
        $frag = '';
        $licensePrefix = '/admin/tools/license/';
        $licenses = (isset($queryData->data)) ? $queryData->data : [];
        foreach ($licenses as $k => $license) {
            $license->list_id = $k;
            $license->license_name = strip_tags($license->license_name);
            $license->edit_link = $licensePrefix . $license->license_slug . '/edit';
            $license->builder_link = $licensePrefix . 'items/' . $license->license_slug . '/builder';
            $license->destroy_link = $licensePrefix . $license->license_slug . '/delete';
            $license->destroy_text = 'Delete';

            if ($callback !== null){
                $queryMode = $tonicsView->getVariableData()['QUERY_MODE'];
                $queryMode[$query_name] = $license;
                $tonicsView->addToVariableData('QUERY_MODE', $queryMode);
                $frag .= $callback();
            }
        }
        return $frag;
    }
}