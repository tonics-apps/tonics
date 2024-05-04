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

use App\Modules\Core\Library\View\Extensions\Interfaces\QueryModeHandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class TrackLoop implements QueryModeHandlerInterface
{

    public function handleQueryData(TonicsView $tonicsView, string $query_name, \stdClass $queryData, callable $callback = null): string
    {
        $frag = '';
        $trackPrefix = '/admin/tracks/';
        $tracks = (isset($queryData->data)) ? $queryData->data : [];
        foreach ($tracks as $k => $track) {
            $track->list_id = $k;
            $track->track_title = strip_tags($track->track_title);
            $track->edit_link = $trackPrefix . $track->track_slug . '/edit';
            $track->preview_link = '/tracks/' . $track->slug_id . '/' .$track->track_slug;

            if ($track->track_status === -1){
                $track->destroy_link = $trackPrefix . $track->track_slug . '/delete';
                $track->destroy_text = 'Delete';
                $track->button_data_attr = 'data-click-onconfirmdelete="true"';
            } else {
                $track->destroy_link = $trackPrefix . $track->track_slug . '/trash';
                $track->destroy_text = 'Trash';
                $track->button_data_attr = 'data-click-onconfirmtrash="true"';
            }

            if ($callback !== null){
                $queryMode = $tonicsView->getVariableData()['QUERY_MODE'];
                $queryMode[$query_name] = $track;
                $tonicsView->addToVariableData('QUERY_MODE', $queryMode);
                $frag .= $callback();
            }

        }
        return $frag;
    }
}