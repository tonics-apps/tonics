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

namespace App\Apps\NinetySeven\Controller;

use App\Modules\Core\Library\SimpleState;
use App\Modules\Page\Services\PageService;
use App\Modules\Track\Services\TrackService;
use Devsrealm\TonicsRouterSystem\Exceptions\URLNotFound;

class TracksController
{
    public function __construct(private readonly PageService $pageService, private readonly TrackService $trackService)
    {
    }

    /**
     * @param string $slugID
     * @return mixed
     * @throws URLNotFound
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function trackCategoryPage(string $slugID)
    {
        $trackCategory = $this->trackService->TrackCategoryPageLayout($slugID);
        if ($trackCategory) {
            view('Apps::NinetySeven/Views/Track/index', [
                'SvgDefs' => implode('', helper()->iconSymbols()),
                'Settings' => $trackCategory,
            ]);
            return;
        }

        throw new URLNotFound(SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE, SimpleState::ERROR_PAGE_NOT_FOUND__CODE);
    }

    /**
     * @param string $slugID
     * @return void
     * @throws URLNotFound
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function trackPage(string $slugID)
    {
        $track = $this->trackService->TrackPageLayout($slugID);
        if ($track) {
            view('Apps::NinetySeven/Views/Track/index', [
                'SvgDefs' => implode('', helper()->iconSymbols()),
                'Settings' => $track,
            ]);
            return;
        }

        throw new URLNotFound(SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE, SimpleState::ERROR_PAGE_NOT_FOUND__CODE);
    }

    /**
     * @return void
     * @throws URLNotFound
     * @throws \Throwable
     */
    public function trackHome(): void
    {
        $layoutData = $this->pageService->PageLayout(request()->getRequestURL());

        if ($layoutData) {
            view('Apps::NinetySeven/Views/Track/index', [
                'SvgDefs' => implode('', helper()->iconSymbols()),
                'Settings' => $layoutData,
                'SettingsJSON' => json_encode($layoutData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE),
            ]);
            return;
        }

        throw new URLNotFound(SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE, SimpleState::ERROR_PAGE_NOT_FOUND__CODE);
    }
}