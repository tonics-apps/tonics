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

namespace App\Apps\TonicsSeo\Schedules;

use App\Apps\TonicsSeo\Controller\TonicsSeoController;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\SchedulerSystem\AbstractSchedulerInterface;
use App\Modules\Core\Library\SchedulerSystem\ScheduleHandlerInterface;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;

class PingSearchEngineForSitemap extends AbstractSchedulerInterface implements ScheduleHandlerInterface
{
    use ConsoleColor;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setName('App_TonicsSeo_PingSearchEngineForSitemap');
        $this->setPriority(Scheduler::PRIORITY_LOW);

        $settingsData = TonicsSeoController::getSettingsData();
        $pingEvery = $settingsData['app_tonicsseo_ping_search_engine'] ?? '6hr';
        if (str_ends_with($pingEvery, 'hr')){
            $this->setEvery(Scheduler::everyHour((int)$pingEvery));
        } else {
            $this->setEvery(Scheduler::everyMinute((int)$pingEvery));
        }
    }

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $settingsData = TonicsSeoController::getSettingsData();
        $notifySearchEngine = $settingsData['app_tonicsseo_notify_search_engine'] ?? [];
        if (!is_array($notifySearchEngine)){
            $notifySearchEngine = [];
        }

        foreach ($notifySearchEngine as $engine){
            $sitemapURL = AppConfig::getAppUrl() . '/sitemap.xml';
            if ($engine === 'google'){
                $this->infoMessage("Pinging Google Sitemap Tool");
                $this->sendGetRequest("https://www.google.com/webmasters/sitemaps/ping?sitemap=$sitemapURL");
            }

            if ($engine === 'bing'){
                $this->infoMessage("Pinging Bing Sitemap Tool");
                $this->sendGetRequest("https://www.bing.com/ping?sitemap=$sitemapURL");
            }
        }
    }

    public function sendGetRequest(string $url): bool|string
    {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_PROXY_SSL_VERIFYPEER => false,
            CURLOPT_DNS_CACHE_TIMEOUT => false,
            CURLOPT_DNS_USE_GLOBAL_CACHE => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }


}