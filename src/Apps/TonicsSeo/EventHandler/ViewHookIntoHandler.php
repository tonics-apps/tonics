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

namespace App\Apps\TonicsSeo\EventHandler;

use App\Modules\Core\Configs\AppConfig;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class ViewHookIntoHandler implements HandlerInterface
{
    /**
     * @inheritDoc
     * @throws \Exception
     * @throws \Throwable
     */
    public function handleEvent (object $event): void
    {
        $event->hookInto('sitemap_in_image_loc', function (TonicsView $tonicsView) {
            $currentImageInLoop = $tonicsView->accessArrayWithSeparator('sitemap._image');
            if (str_starts_with($currentImageInLoop, 'http')) {
                return $currentImageInLoop;
            }

            return AppConfig::getAppUrl() . $currentImageInLoop;
        });

        $event->hookInto('sitemap_lastmod', function (TonicsView $tonicsView) {
            return date_create($tonicsView->accessArrayWithSeparator('sitemap._lastmod'))->format('c');
        });


        $event->hookInto('rss_in_image_url', function (TonicsView $tonicsView) {
            $currentImageInLoop = $tonicsView->accessArrayWithSeparator('rssQueryData._image');
            if (str_starts_with($currentImageInLoop, 'http')) {
                return $currentImageInLoop;
            }
            return AppConfig::getAppUrl() . $currentImageInLoop;
        });

    }
}