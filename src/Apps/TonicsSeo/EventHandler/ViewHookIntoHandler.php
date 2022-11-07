<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsSeo\EventHandler;

use App\Apps\TonicsSeo\Controller\TonicsSeoController;
use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class ViewHookIntoHandler implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnHookIntoTemplate */

        # If requestURL is homePage
        if (url()->getRequestURL() === ''){
            $event->hookInto('in_head', function (TonicsView $tonicsView){
                return $this->searchEngineVerification();
            });
        }

        $event->hookInto('in_head', function (TonicsView $tonicsView){
            return $this->searchEngineOptimization($tonicsView);
        });

    }

    /**
     * @throws \Exception
     */
    private function searchEngineVerification(): string
    {
        $meta = '';
        $seoSettings = TonicsSeoController::getSettingsData();
        if (isset($seoSettings['app_tonicsseo_google_verification_code']) && !empty($seoSettings['app_tonicsseo_google_verification_code'])){
            $googleVerificationCode = helper()->htmlSpecChar($seoSettings['app_tonicsseo_google_verification_code']);
            $meta .=<<<HTML
<meta name="google-site-verification" content="$googleVerificationCode" />

HTML;
        }

        if (isset($seoSettings['app_tonicsseo_bing_verification_code']) && !empty($seoSettings['app_tonicsseo_bing_verification_code'])){
            $bingVerificationCode = helper()->htmlSpecChar($seoSettings['app_tonicsseo_bing_verification_code']);
            $meta .=<<<HTML
<meta name="msvalidate.01" content="$bingVerificationCode" />

HTML;
        }

        if (isset($seoSettings['app_tonicsseo_pinterest_verification_code']) && !empty($seoSettings['app_tonicsseo_pinterest_verification_code'])){
            $pinterestVerificationCode = helper()->htmlSpecChar($seoSettings['app_tonicsseo_pinterest_verification_code']);
            $meta .=<<<HTML
<meta name="p:domain_verify" content="$pinterestVerificationCode" />

HTML;
        }

        return $meta;
    }

    /**
     * @throws \Exception
     */
    private function searchEngineOptimization(TonicsView $tonicsView): string
    {
        $meta = '';
        $seoSettings = TonicsSeoController::getSettingsData();
        $isUserLoggedIn = $tonicsView->accessArrayWithSeparator('Auth.Logged_In');
        $inHead = $seoSettings['app_tonicsseo_in_head'] ?? "";
        $disableInjection = $seoSettings['app_tonicsseo_disable_injection_logged_in'] ?? "";
        if ($isUserLoggedIn === false){
            $meta .= $inHead . "\n";
        }elseif ($isUserLoggedIn === true && $disableInjection === '0'){
            $meta .= $inHead . "\n";
        }

        $siteTitle = helper()->htmlSpecChar($seoSettings['app_tonicsseo_site_title'] ?? "");
        $separator = helper()->htmlSpecChar($seoSettings['app_tonicsseo_site_title_separator'] ?? "");
        $titlePositionIsLeft = helper()->htmlSpecChar($seoSettings['app_tonicsseo_site_title_location'] ?? "") === 'left';

        $seoTitle = $tonicsView->accessArrayWithSeparator('Data.seo_title') ?: $tonicsView->accessArrayWithSeparator('Data.og_title');
        $seoTitle = helper()->htmlSpecChar($seoTitle);
        $seoDescription = $tonicsView->accessArrayWithSeparator('Data.seo_description') ?: $tonicsView->accessArrayWithSeparator('Data.og_description');
        $seoDescription = helper()->htmlSpecChar($seoDescription);

        if ($titlePositionIsLeft){
            $meta .=<<<SEO
<title>$siteTitle $separator $seoTitle</title>

SEO;
        } else {
            $meta .=<<<SEO
<title>$seoTitle $separator $siteTitle </title>

SEO;
        }

        $meta .=<<<SEO
<meta name="description" content='$seoDescription' />
<meta property="og:site_name" content="$siteTitle" />
<meta property="og:title" content='$seoTitle' />
<meta property="og:description" content='$seoDescription' />

SEO;


        $seoNoIndex = $tonicsView->accessArrayWithSeparator('Data.seo_indexing') === '0';
        $seoNoFollow = $tonicsView->accessArrayWithSeparator('Data.seo_following') === '0';

        if ($seoNoIndex){
            $meta .=<<<SEO
<meta name="robots" content="noindex" />

SEO;
        }

        if ($seoNoFollow){
            $meta .=<<<SEO
<meta name="robots" content="nofollow" />

SEO;
        }

        $ogType = helper()->htmlSpecChar($tonicsView->accessArrayWithSeparator('Data.seo_open_graph_type'));
        $ogImage = helper()->htmlSpecChar($tonicsView->accessArrayWithSeparator('Data.image_url'));
        $ogURL = helper()->htmlSpecChar($tonicsView->accessArrayWithSeparator('URL.FULL_URL'));

        if ($ogType){
            $meta .=<<<SEO
<meta name="og:type" content="$ogType" />

SEO;
        }

        if ($ogImage){
            $ogImage = $tonicsView->accessArrayWithSeparator('App_Config.SiteURL') . $ogImage;
            $meta .=<<<SEO
<meta name="og:image" content="$ogImage" />

SEO;
        }

        if ($ogURL){
            $meta .=<<<SEO
<meta name="og:url" content="$ogURL" />

SEO;
        }

        $publishedTime = $tonicsView->accessArrayWithSeparator('Data.published_time') ?: $tonicsView->accessArrayWithSeparator('Data.created_at');
        $publishedTime = str_replace(' ', 'T', $publishedTime) . $tonicsView->accessArrayWithSeparator('App_Config.APP_TIME_ZONE_OFFSET');
        $publishedTime = helper()->htmlSpecChar($publishedTime);

        $createdTime = $tonicsView->accessArrayWithSeparator('Data.modified_time') ?: $tonicsView->accessArrayWithSeparator('Data.updated_at');
        $createdTime = str_replace(' ', 'T', $createdTime). $tonicsView->accessArrayWithSeparator('App_Config.APP_TIME_ZONE_OFFSET');
        $createdTime = helper()->htmlSpecChar($createdTime);

        if ($ogURL){
            $meta .=<<<SEO
<meta property="article:published_time" content="$publishedTime" />
<meta property="article:modified_time" content="$createdTime" />

SEO;
        }

        $canonicalUrl = $tonicsView->accessArrayWithSeparator('Data.seo_canonical_url');
        $canonicalUrl = helper()->htmlSpecChar($canonicalUrl);

        if ($canonicalUrl){
            $meta .=<<<SEO
<link rel="canonical" href="$canonicalUrl" />

SEO;
        }

        return $meta;
    }
}