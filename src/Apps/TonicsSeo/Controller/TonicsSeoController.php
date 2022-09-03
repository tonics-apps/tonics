<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsSeo\Controller;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Events\Tools\Sitemap\AbstractSitemapInterface;
use App\Modules\Core\Events\Tools\Sitemap\OnAddSitemap;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class TonicsSeoController
{
    private ?FieldData $fieldData;

    const CACHE_KEY = 'TonicsPlugin_TonicsSEOSettings';

    public function __construct(FieldData $fieldData = null)
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @throws \Exception
     */
    public function edit(): void
    {
        $fieldItems = $this->getFieldData()->generateFieldWithFieldSlug(
            ['app-tonicsseo-settings'],
            $this->getSettingsData()
        )->getHTMLFrag();

        view('Apps::TonicsSeo/Views/settings', [
                'FieldItems' => $fieldItems,
            ]
        );
    }


    /**
     * @throws \Exception
     */
    public function update()
    {
        try {
            $settings = FieldConfig::savePluginFieldSettings(self::getCacheKey(), $_POST);
            apcu_store(self::getCacheKey(), $settings);
            session()->flash(['Settings Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('tonicsSeo.settings'));
        }catch (\Exception){
            session()->flash(['An Error Occurred Saving Settings'], $_POST);
            redirect(route('tonicsSeo.settings'));
        }
    }

    /**
     * @throws \Exception
     */
    public function sitemap()
    {
        /** @var OnAddSitemap $sitemapTypeEvent */
        $sitemapTypeEvent = event()->dispatch(new OnAddSitemap());

        // response()->header("content-type: text/xml; charset=UTF-8");

        $sitemapIndexes = [];
        $typeNameFromParam = strtolower(url()->getParam('type', ''));
        if (url()->hasParam('type') && key_exists($typeNameFromParam, $sitemapTypeEvent->getSitemap())){
            /** @var AbstractSitemapInterface $sitemapHandlerObject */
            $sitemapHandlerObject = $sitemapTypeEvent->getSitemap()[$typeNameFromParam];
            $sitemapPerPage = (isset(self::getSettingsData()['app_tonicsseo_sitemap_per_page'])) ? (int)self::getSettingsData()['app_tonicsseo_sitemap_per_page'] : 1000;
            $sitemapHandlerObject->setLimit($sitemapPerPage);

            # If it includes a page param, then get the sitemap data
            if (url()->hasParam('page')){
                $data = view('Apps::TonicsSeo/Views/sitemap_entries', [
                    'sitemapData' => $sitemapHandlerObject->getData(),
                ], TonicsView::RENDER_CONCATENATE);
                exit();
            }

            # Sitemap Index for page type
            if ($sitemapHandlerObject->getDataCount() > $sitemapPerPage){
                $count = $sitemapHandlerObject->getDataCount();
                # Total Pages we can paginate through
                $totalPages = (int)ceil($count / $sitemapPerPage);
                for ($i = 1; $i <= $totalPages; ++$i){
                    $indexURL = AppConfig::getAppUrl(). url()->appendQueryString("page=" . $i)->getRequestURLWithQueryString();
                    $sitemapIndexes[] = $indexURL;
                }
            }

        } else {
            foreach ($sitemapTypeEvent->getSitemap() as $sitemapName => $sitemapValue){
                $indexURL = url()->getFullURL() . '?type=' .$sitemapName;
                $sitemapIndexes[] = $indexURL;
            }
        }

        view('Apps::TonicsSeo/Views/sitemap', [
            'sitemapIndexes' => $sitemapIndexes,
        ]);
    }

    public function robots()
    {

    }

    /**
     * @throws \Exception
     */
    public static function getSettingsData()
    {
        $settings = apcu_fetch(self::getCacheKey());
        if ($settings === false){
            $settings = FieldConfig::loadPluginSettings(self::getCacheKey());
        }

        return $settings ?? [];
    }

    public static function getCacheKey(): string
    {
        return AppConfig::getAppCacheKey() . self::CACHE_KEY;
    }

    /**
     * @return FieldData|null
     */
    public function getFieldData(): ?FieldData
    {
        return $this->fieldData;
    }

    /**
     * @param FieldData|null $fieldData
     */
    public function setFieldData(?FieldData $fieldData): void
    {
        $this->fieldData = $fieldData;
    }
}