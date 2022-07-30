<?php

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
                $tonicsView->setDontCacheVariable(true);
                $tonicsView->addToVariableData('QUERY_MODE', $queryMode);
                $frag .= $callback();
            }

        }
        return $frag;
    }
}