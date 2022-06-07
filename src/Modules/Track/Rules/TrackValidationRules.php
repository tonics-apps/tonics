<?php

namespace App\Modules\Track\Rules;

use App\Library\Tables;

trait TrackValidationRules
{
    /**
     * @throws \Exception
     */
    public function licenseStoreRule(): array
    {
        $uniqueSlug = Tables::getTable(Tables::LICENSES) .':license_slug';
        return [
            'license_name' => ['required', 'string'],
            'license_slug' => ['required', 'string', 'unique' => [
                $uniqueSlug => input()->fromPost()->retrieve('license_slug', '')]
            ],
        ];
    }

    /**
     * @throws \Exception
     */
    public function licenseUpdateRule(): array
    {
        $widgetUniqueSlug = Tables::getTable(Tables::LICENSES) .':license_slug:license_id';
        return [
            'license_name' => ['required', 'string'],
            'license_slug' => ['required', 'string', 'unique' => [
                $widgetUniqueSlug => input()->fromPost()->retrieve('license_id', '')]
            ],
        ];
    }

    public function licenseItemsStoreRule(): array
    {
        return [
            'licenseSlug' => ['required', 'string'],
            'licenseDetails' => ['required', 'string'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function artistStoreRule(): array
    {
        $slugUnique = Tables::getTable(Tables::ARTISTS) .':artist_slug';
        return [
            'artist_name' => ['required', 'string'],
            'artist_bio' => ['string'],
            'artist_slug' => ['required', 'string', 'unique' => [
                $slugUnique => input()->fromPost()->retrieve('artist_slug', '')]
            ],
            'image_url' => ['string']
        ];
    }

    /**
     * @throws \Exception
     */
    public function artistUpdateRule(): array
    {
        $slugUnique = Tables::getTable(Tables::ARTISTS) .':artist_slug:artist_id';
        return [
            'artist_name' => ['required', 'string'],
            'artist_bio' => ['string'],
            'artist_slug' => ['required', 'string', 'unique' => [
                $slugUnique => input()->fromPost()->retrieve('artist_slug', '')]
            ],
            'image_url' => ['string']
        ];
    }

    /**
     * @throws \Exception
     */
    public function genreStoreRule(): array
    {
        $slugUnique = Tables::getTable(Tables::GENRES) .':genre_slug';
        return [
            'genre_name' => ['required', 'string'],
            'genre_description' => ['string'],
            'genre_slug' => ['required', 'string', 'unique' => [
                $slugUnique => input()->fromPost()->retrieve('genre_slug', '')]
            ]
        ];
    }

    /**
     * @throws \Exception
     */
    public function genreUpdateRule(): array
    {
        $slugUnique = Tables::getTable(Tables::GENRES) .':genre_slug:genre_id';
        return [
            'genre_name' => ['required', 'string'],
            'genre_description' => ['string'],
            'genre_slug' => ['required', 'string', 'unique' => [
                $slugUnique => input()->fromPost()->retrieve('genre_slug', '')]
            ]
        ];
    }

    /**
     * @throws \Exception
     */
    public function trackStoreRule(): array
    {
        $slugUnique = Tables::getTable(Tables::TRACKS) .':track_slug';
        return [
            'track_title' => ['required', 'string'],
            'track_content' => ['string'],
            'track_slug' => ['required', 'string', 'unique' => [
                $slugUnique => input()->fromPost()->retrieve('track_slug', '')]
            ],
            'fk_genre_id' => ['required', 'numeric'],
            'fk_license_id' => ['required', 'numeric'],
            'fk_artist_id' => ['required', 'numeric'],
            'created_at' => ['required', 'string'],
            'image_url' => ['string'],
            'audio_url' => ['string'],
            'license_attr_id_link' => ['required', 'string'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function trackUpdateRule(): array
    {
        $slugUnique = Tables::getTable(Tables::TRACKS) .':track_slug:track_id';
        return [
            'track_title' => ['required', 'string'],
            'track_content' => ['string'],
            'track_slug' => ['required', 'string', 'unique' => [
                $slugUnique => input()->fromPost()->retrieve('track_id', '')]
            ],
            'fk_genre_id' => ['required', 'numeric'],
            'fk_license_id' => ['required', 'numeric'],
            'fk_artist_id' => ['required', 'numeric'],
            'created_at' => ['required', 'string'],
            'image_url' => ['string'],
            'audio_url' => ['string'],
            'license_attr_id_link' => ['required', 'string'],
        ];
    }
}