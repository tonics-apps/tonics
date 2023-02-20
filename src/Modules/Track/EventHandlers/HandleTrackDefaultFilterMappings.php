<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\EventHandlers;

use App\Modules\Track\Events\AbstractClasses\TrackDataAccessor;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleTrackDefaultFilterMappings implements HandlerInterface
{

    /**
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /**
         * @var TrackDataAccessor $event
         */
        $fieldSettings = null;
        if (isset($event->getAll()->field_settings)){
            $fieldSettings = json_decode($event->getAll()->field_settings);
        }

        $table = $event->getTrackData()::getTrackDefaultFiltersTrackTable();
        $filters = [
            'track_bpm' => 'bpm',
            'track_default_filter_mood' => 'mood',
            'track_default_filter_keys' => 'key',
            'track_default_filter_instruments' => 'instrument',
            'track_default_filter_samplePacks_Type' => 'samplePackType',
            'track_default_filter_acapella_gender' => 'acapellaGender',
            'track_default_filter_acapella_vocalStyle' => 'acapellaVocalStyle',
            'track_default_filter_acapella_emotion' => 'acapellaEmotion',
            'track_default_filter_acapella_scale' => 'acapellaScale',
            'track_default_filter_acapella_effects' => 'acapellaEffects'
        ];

        try {
            $db = db();
            $filtersTable = $event->getTrackData()::getTrackDefaultFiltersTable();
            $db->Select('tdf_id')->From($filtersTable);
            foreach ($filters as $filter => $type){
                if (isset($fieldSettings->{$filter})){
                    $db->OrWhereEquals('tdf_type', $type)->WhereIn('tdf_name', $fieldSettings->{$filter});
                }
            }

            $tdfIDS = $db->FetchResult();
            $toInsert = [];
            db()->FastDelete($table, db()->WhereIn('fk_track_id', $event->getTrackID()));
            foreach ($tdfIDS as $tdfID){
                $toInsert[] = [
                    'fk_track_id' => $event->getTrackID(),
                    'fk_tdf_id' => $tdfID->tdf_id,
                ];
            }
            db()->Insert($table, $toInsert);
        } catch (\Exception $exception){
            // Log..
        }
    }

}