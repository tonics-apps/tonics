<?php
/*
 *     Copyright (c) 2025. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Track\Controllers;

use App\Modules\Track\Services\TrackService;

readonly class TracksControllerAPI
{
    public function __construct(private TrackService $trackService)
    {
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function QueryTrack(): void
    {
        try {
            $body = url()->getEntityBody();
            $body = json_decode($body, true) ?? [];
            $track = $this->trackService::QueryLoop($body);
            helper()->onSuccess($track);
        } catch (\Exception $e) {
            helper()->onError(500, $e->getMessage());
        }
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function QueryTrackCategory(): void
    {
        try {
            $body = url()->getEntityBody();
            $body = json_decode($body, true) ?? [];
            $trackCategory = $this->trackService::QueryLoopCategory($body);
            helper()->onSuccess($trackCategory);
        } catch (\Exception $e) {
            helper()->onError(500, $e->getMessage());
        }
    }

    public function QueryTrackBySlugID(string $slugID)
    {
    }

    /**
     * @throws \Throwable
     */
    public function TrackPageLayout(string $slugID): void
    {
        $layout = $this->trackService->TrackPageLayout($slugID);
        if ($layout) {
            helper()->onSuccess($layout);
        }
        helper()->onError(500, 'Invalid Track SlugID');
    }

    /**
     * @param string $slugID
     *
     * @return void
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function TrackCategoryPageLayout(string $slugID): void
    {
        $layout = $this->trackService->TrackCategoryPageLayout($slugID);
        if ($layout) {
            helper()->onSuccess($layout);
        }

        helper()->onError(500, 'Invalid TrackCategory SlugID');
    }
}