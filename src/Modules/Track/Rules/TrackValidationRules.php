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

namespace App\Modules\Track\Rules;

use App\Modules\Core\Library\Tables;

trait TrackValidationRules
{

    /**
     * @throws \Exception
     */
    public function artistStoreRule (): array
    {
        $slugUnique = Tables::getTable(Tables::ARTISTS) . ':artist_slug';
        return [
            'artist_name' => ['required', 'string'],
            'artist_bio'  => ['string'],
            'artist_slug' => [
                'required', 'string', 'unique' => [
                    $slugUnique => input()->fromPost()->retrieve('artist_slug', ''),
                ],
            ],
            'image_url'   => ['string'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function artistUpdateRule (): array
    {
        $slugUnique = Tables::getTable(Tables::ARTISTS) . ':artist_slug:artist_id';
        return [
            'artist_name' => ['required', 'string'],
            'artist_bio'  => ['string'],
            'artist_slug' => [
                'required', 'string', 'unique' => [
                    $slugUnique => input()->fromPost()->retrieve('artist_id', ''),
                ],
            ],
            'image_url'   => ['string'],
        ];
    }

    /**
     * @return \string[][]
     */
    public function artistUpdateMultipleRule (): array
    {
        return [
            'artist_id'   => ['numeric'],
            'artist_name' => ['required', 'string'],
            'updated_at'  => ['required', 'string'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function genreStoreRule (): array
    {
        $slugUnique = Tables::getTable(Tables::GENRES) . ':genre_slug';
        return [
            'genre_name'        => ['required', 'string'],
            'genre_description' => ['string'],
            'genre_slug'        => [
                'required', 'string', 'unique' => [
                    $slugUnique => input()->fromPost()->retrieve('genre_slug', ''),
                ],
            ],
        ];
    }

    /**
     * @throws \Exception
     */
    public function genreUpdateRule (): array
    {
        $slugUnique = Tables::getTable(Tables::GENRES) . ':genre_slug:genre_id';
        return [
            'genre_name'        => ['required', 'string'],
            'genre_description' => ['string'],
            'genre_slug'        => [
                'required', 'string', 'unique' => [
                    $slugUnique => input()->fromPost()->retrieve('genre_id', ''),
                ],
            ],
        ];
    }

    /**
     * @return \string[][]
     */
    public function genreUpdateMultipleRule (): array
    {
        return [
            'genre_id'   => ['numeric'],
            'genre_name' => ['required', 'string'],
            'updated_at' => ['required', 'string'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function trackStoreRule (): array
    {
        $slugUnique = Tables::getTable(Tables::TRACKS) . ':track_slug';
        return [
            'track_title'   => ['required', 'string'],
            'track_content' => ['string'],
            'track_slug'    => [
                'required', 'string', 'unique' => [
                    $slugUnique => input()->fromPost()->retrieve('track_slug', ''),
                ],
            ],

            'fk_genre_id'     => ['required', 'array'],
            'fk_track_cat_id' => ['array'],

            'fk_license_id'        => ['required', 'numeric'],
            'fk_artist_id'         => ['required', 'numeric'],
            'created_at'           => ['required', 'string'],
            'image_url'            => ['string'],
            'audio_url'            => ['string'],
            'license_attr_id_link' => ['required', 'string'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function trackUpdateRule (): array
    {
        $slugUnique = Tables::getTable(Tables::TRACKS) . ':track_slug:track_id';
        return [
            'track_title'          => ['required', 'string'],
            'track_content'        => ['string'],
            'track_slug'           => [
                'required', 'string', 'unique' => [
                    $slugUnique => input()->fromPost()->retrieve('track_id', ''),
                ],
            ],
            'fk_genre_id'          => ['required', 'array'],
            'fk_license_id'        => ['required', 'numeric'],
            'fk_artist_id'         => ['required', 'numeric'],
            'created_at'           => ['required', 'string'],
            'image_url'            => ['string'],
            'audio_url'            => ['string'],
            'license_attr_id_link' => ['required', 'string'],
        ];
    }

    /**
     * @return \string[][]
     */
    public function trackUpdateMultipleRule (): array
    {
        return [
            'track_id'    => ['numeric'],
            'track_title' => ['required', 'string'],
            'updated_at'  => ['required', 'string'],
        ];
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function trackCategoryStoreRule (): array
    {
        $catSlugUnique = Tables::getTable(Tables::TRACK_CATEGORIES) . ':track_cat_slug';
        return [
            'track_cat_name'    => ['required', 'string'],
            'track_cat_content' => ['string'],
            'track_cat_slug'    => [
                'required', 'string', 'unique' => [
                    $catSlugUnique => input()->fromPost()->retrieve('track_cat_slug', ''),
                ],
            ],
            'created_at'        => ['required', 'string'],
        ];
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function trackCategoryUpdateRule (): array
    {
        $catSlugUnique = Tables::getTable(Tables::TRACK_CATEGORIES) . ':track_cat_slug:track_cat_id';
        return [
            'track_cat_name'    => ['required', 'string'],
            'track_cat_content' => ['string'],
            'track_cat_slug'    => [
                'required', 'string', 'unique' => [
                    $catSlugUnique => input()->fromPost()->retrieve('track_cat_id', ''),
                ],
            ],
            'created_at'        => ['required', 'string'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function trackCategoryUpdateMultipleRule (): array
    {
        return [
            'track_cat_id'   => ['numeric'],
            'track_cat_name' => ['required', 'string'],
            'updated_at'     => ['required', 'string'],
        ];
    }
}