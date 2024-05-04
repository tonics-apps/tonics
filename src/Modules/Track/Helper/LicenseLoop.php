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