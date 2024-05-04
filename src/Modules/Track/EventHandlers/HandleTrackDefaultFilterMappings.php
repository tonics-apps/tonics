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

namespace App\Modules\Track\EventHandlers;

use App\Modules\Core\Library\Tables;
use App\Modules\Track\Events\AbstractClasses\TrackDataAccessor;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

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
            'track_default_filter_acapella_effects' => 'acapellaEffects',
            'track_default_filter_genres' => 'genre',
            'track_default_filter_artists' => 'artist',
        ];

        try {
            db(onGetDB: function (TonicsQuery $db) use ($table, $filters, $event, &$fieldSettings) {

                if (isset($fieldSettings->fk_genre_id)){
                    $genres = $db->Select('genre_slug')->From(Tables::getTable(Tables::GENRES))
                        ->WhereIn('genre_id', $fieldSettings->fk_genre_id)
                        ->FetchResult();
                    $newGenre = [];
                    foreach ($genres as $genre){ $newGenre[] = $genre->genre_slug; }
                    $fieldSettings->track_default_filter_genres = $newGenre;
                }

                if (isset($fieldSettings->fk_artist_id)){
                    $artists = $db->Reset()->Select('artist_slug')->From(Tables::getTable(Tables::ARTISTS))
                        ->WhereIn('artist_id', $fieldSettings->fk_artist_id)
                        ->FetchResult();
                    $newArtists = [];
                    foreach ($artists as $artist){ $newArtists[] = $artist->artist_slug; }
                    $fieldSettings->track_default_filter_artists = $newArtists;
                }

                $filtersTable = $event->getTrackData()::getTrackDefaultFiltersTable();
                $db->Reset()->Select('tdf_id')->From($filtersTable);
                foreach ($filters as $filter => $type){
                    if (isset($fieldSettings->{$filter})){
                        $db->OrWhereEquals('tdf_type', $type)->WhereIn('tdf_name', $fieldSettings->{$filter});
                    }
                }

                $tdfIDS = $db->FetchResult();

                $db->FastDelete($table, db()->WhereIn('fk_track_id', $event->getTrackID()));

                $toInsert = [];
                foreach ($tdfIDS as $tdfID){
                    $toInsert[] = [
                        'fk_track_id' => $event->getTrackID(),
                        'fk_tdf_id' => $tdfID->tdf_id,
                    ];
                }

                $db->Insert($table, $toInsert);
            });

        } catch (\Exception $exception){
            // Log..
        }
    }

}