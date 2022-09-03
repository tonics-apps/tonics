<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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