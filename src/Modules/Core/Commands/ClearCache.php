<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Commands;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\ConsoleColor;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * To clear a cache with key, use: `php bin/console --clear --cache=cache_key -warm`.
 * <br>
 * To clear all cache data, use `php bin/console --cache --clear --warm`
 *<br>
 * To warm a cache use `php bin/console --cache --clear --warm=1`
 */
class ClearCache implements ConsoleCommand
{
    use ConsoleColor;

    public function required(): array
    {
        return [
            "--cache",
            "--clear",
            "--warm"
        ];
    }

    /**
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        $appURL = AppConfig::getAppUrl(); $helper = helper();
        if (!key_exists('host', parse_url($appURL))){
            $this->errorMessage("Host URL Is Invalid");
            return;
        }

        $host = parse_url($appURL, PHP_URL_HOST);
        # adding a trailing avoid returning server ip, so, it would return the domain ip (which is what we want)
        $domainIP = gethostbyname($host . '.');
        if (!filter_var($domainIP, FILTER_VALIDATE_IP)) {
            $this->errorMessage("`$domainIP` is Not a Valid Domain IP");
            return;
        }
        $cacheUrl = $appURL . "/admin/cache/clear". "?token=".AppConfig::getKey();
        if (!empty($commandOptions['--cache'])){
            $cacheKey = $commandOptions['--cache'];
            $cacheUrl = $appURL . "/admin/cache/clear". "?token=".AppConfig::getKey() . "&cache-key=$cacheKey";
        }

        $warmCache = (int)$commandOptions['--warm'];
        if ($warmCache === 1){
            $cacheUrl = $appURL . "/admin/cache/warm-template". "?token=".AppConfig::getKey();
        }


        $appURLPort = AppConfig::getAppUrlPort();
        $resolveUrl = ["$host:$appURLPort:$domainIP"];
        $curl = curl_init($cacheUrl);
        $headers = [];
        curl_setopt_array($curl, [
            // FOR DOWNLOAD:
            // CURLOPT_BUFFERSIZE => 8096,
            // CURLOPT_CONNECTTIMEOUT => 0, // 0 means forever

            // 0 mean the byte to start chunking from and 4096 means the byte to end the chunking.
            // you start the next slice from 4097-whatever
            // CURLOPT_RANGE => "0-4096"

            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_PROXY_SSL_VERIFYPEER => false,
            CURLOPT_DNS_CACHE_TIMEOUT => false,
            CURLOPT_DNS_USE_GLOBAL_CACHE => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_RESOLVE => $resolveUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADERFUNCTION =>  function($curl, $header) use ($helper, &$headers)
            {
                return $helper->getCurlHeaders($curl, $header, $headers, ['cache-result']);
            },
            // CURLOPT_VERBOSE => true,
        ]);
        $response = curl_exec($curl);
        ## $http_status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        if (!key_exists('cache-result', $headers)){
            $this->errorMessage("Failed To Clear Cache: No `cache-result` Key");
            return;
        }

        $cacheResult = (int)$headers['cache-result'][0];

        if ($response === false){
            $this->errorMessage("Curl Can't Connect To $cacheUrl");
            return;
        }

        if (!empty($commandOptions['--warm']) && $cacheResult === 1){
            $this->successMessage("Cache Warmed");
            return;
        }

        if ($commandOptions['--cache'] && $cacheResult === 1){
            $this->successMessage("Cache {$commandOptions['--cache']} Successfully Cleared");
            return;
        }

        if (empty($commandOptions['--cache']) && $cacheResult === 1){
            $this->successMessage("All Cache Cleared");
            return;
        }

        $this->errorMessage("Failed To Clear Cache, Cache Result Returned $cacheResult");
    }
}