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

namespace App\Apps\TonicsSeo\Controller;

use App\Apps\TonicsSeo\Schedules\PingSearchEngineForSitemap;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Events\Tools\Sitemap\AbstractSitemapInterface;
use App\Modules\Core\Events\Tools\Sitemap\OnAddSitemap;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Helper\FieldHelpers;
use App\Modules\Post\Data\PostData;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsRouterSystem\Exceptions\URLNotFound;
use JetBrains\PhpStorm\NoReturn;

class TonicsSeoController
{
    const CACHE_KEY = 'TonicsPlugin_TonicsSEOSettings';
    private ?FieldData $fieldData;
    private ?PostData  $postData;

    public function __construct (FieldData $fieldData = null, PostData $postData = null)
    {
        $this->fieldData = $fieldData;
        $this->postData = $postData;
    }

    /**
     * @throws \Exception|\Throwable
     */
    public function edit (): void
    {
        $fieldSettings = $this->getSettingsData();
        if (!isset($fieldSettings['app_tonicsseo_robots_txt'])) {
            $fieldSettings['app_tonicsseo_robots_txt'] = '';
        }
        if (isset($fieldSettings['app_tonicsseo_robots_txt']) && empty($fieldSettings['app_tonicsseo_robots_txt'])) {
            $fieldSettings['app_tonicsseo_robots_txt'] = $this->getDefaultRobots();
        }

        if (isset($fieldSettings['_fieldDetails'])) {
            addToGlobalVariable('Data', $fieldSettings);
            $fieldCategories = $this->getFieldData()->compareSortAndUpdateFieldItems(json_decode($fieldSettings['_fieldDetails']));
            $htmlFrag = $this->getFieldData()->getUsersFormFrag($fieldCategories);
        } else {
            $htmlFrag = $this->getFieldData()->generateFieldWithFieldSlug(
                ['app-tonicsseo-settings'],
                $fieldSettings,
            )->getHTMLFrag();
        }

        view('Apps::TonicsSeo/Views/settings', [
            'FieldItems' => $htmlFrag,
        ],
        );
    }


    /**
     * @throws \Exception|\Throwable
     */
    public function update ()
    {
        try {
            $settings = FieldConfig::savePluginFieldSettings(self::getCacheKey(), $_POST);
            apcu_store(self::getCacheKey(), $settings);

            $sitemapPingSchedule = new PingSearchEngineForSitemap();
            schedule()->enqueue($sitemapPingSchedule);

            session()->flash(['Settings Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('tonicsSeo.settings'));
        } catch (\Exception $exception) {
            session()->flash(['An Error Occurred Saving Settings'], $_POST);
            redirect(route('tonicsSeo.settings'));
        }
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function rssHomePage (): void
    {
        $settings = self::getSettingsData();
        $rssSettingsData = [
            'Title'       => $settings['app_tonicsseo_site_title'] ?? null,
            'Description' => null,
            'Logo'        => null,
            'RequestURL'  => AppConfig::getAppUrl(),
            'Language'    => null,
            'Query'       => [],
        ];
        if (isset($settings['_fieldDetails'])) {
            $fieldDetails = json_decode($settings['_fieldDetails']);
            $fieldDetails = helper()->generateTree(['parent_id' => 'field_parent_id', 'id' => 'field_id'], $fieldDetails, onData: function ($field) {
                if (isset($field->field_options) && helper()->isJSON($field->field_options)) {
                    $fieldOption = json_decode($field->field_options);
                    $field->field_options = $fieldOption;
                }
                return $field;
            });

            $app_tonicsseo_rss_settings_parent = 'app_tonicsseo_rss_settings_parent';
            $app_tonicsseo_rss_settings_logo = 'app_tonicsseo_rss_settings_logo';
            $app_tonicsseo_rss_settings_description = 'app_tonicsseo_rss_settings_description';
            $app_tonicsseo_rss_settings_language = 'app_tonicsseo_rss_settings_language';
            $app_tonicsseo_rss_settings_postQueryBuilder = 'app_tonicsseo_rss_settings_postQueryBuilder';

            if (isset($fieldDetails[0]->_children)) {
                foreach ($fieldDetails[0]->_children as $field) {
                    if (isset($field->field_options)) {
                        if ($field->field_input_name === $app_tonicsseo_rss_settings_parent && isset($field->_children)) {
                            foreach ($field->_children as $child) {

                                if ($child->field_input_name === $app_tonicsseo_rss_settings_logo) {
                                    $rssSettingsData['Logo'] = $child->field_options->app_tonicsseo_rss_settings_logo;
                                }

                                if ($child->field_input_name === $app_tonicsseo_rss_settings_description) {
                                    $rssSettingsData['Description'] = $child->field_options->app_tonicsseo_rss_settings_description;
                                }

                                if ($child->field_input_name === $app_tonicsseo_rss_settings_language) {
                                    $rssSettingsData['Language'] = trim($child->field_options->app_tonicsseo_rss_settings_language);
                                }

                                if ($child->field_input_name === $app_tonicsseo_rss_settings_postQueryBuilder) {
                                    if (isset($child->_children[0]->_children)) {
                                        $rssSettingsData['Query'] = FieldHelpers::postDataFromPostQueryBuilderField($child->_children[0]->_children, 'created_at');
                                    }
                                }
                            }

                        }
                    }
                }
            }
        }

        response()->header("content-type: text/xml; charset=UTF-8");
        view('Apps::TonicsSeo/Views/rss', [
            'rssData' => $rssSettingsData,
        ]);

    }

    /**
     * @param string $categoryName
     *
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function rssPostCategory (string $categoryName): void
    {
        $postTbl = Tables::getTable(Tables::POSTS);
        $postCatTbl = Tables::getTable(Tables::POST_CATEGORIES);
        $CatTbl = Tables::getTable(Tables::CATEGORIES);

        $categoryData = null;
        db(onGetDB: function (TonicsQuery $db) use ($categoryName, $CatTbl, &$categoryData) {
            $categoryData = $db
                ->Select('*, JSON_UNQUOTE(JSON_EXTRACT(field_settings, "$.seo_description")) as cat_description')
                ->From($CatTbl)->WhereEquals('cat_slug', $categoryName)->FetchFirst();
        });

        if ($categoryData) {

            $settings = self::getSettingsData();
            $rssSettingsData = [
                'Title'       => $settings['app_tonicsseo_site_title'] ?? null,
                'Description' => $categoryData->cat_description,
                'Logo'        => $settings['app_tonicsseo_rss_settings_logo'] ?? null,
                'RequestURL'  => AppConfig::getAppUrl(),
                'Language'    => $settings['app_tonicsseo_rss_settings_language'] ?? 'en',
            ];

            $queryData = null;
            db(onGetDB: function ($db) use ($CatTbl, $postCatTbl, $postTbl, $categoryData, &$queryData) {
                $postFieldSettings = $postTbl . '.field_settings';
                $tblCol = table()->pick([$postTbl => ['post_id', 'post_title', 'post_slug', 'field_settings', 'slug_id', 'created_at', 'updated_at', 'image_url']])
                    . ", $postTbl.post_title as _title, $postTbl.image_url as _image "
                    . ', CONCAT(cat_id, "::", cat_slug ) as fk_cat_id, CONCAT_WS("/", "/posts", post_slug) as _preview_link '
                    . ", JSON_UNQUOTE(JSON_EXTRACT($postFieldSettings, '$.seo_description')) as _description"
                    . ", DATE_FORMAT($postTbl.created_at, '%a, %d %b %Y %T') as rssPubDate";

                $catIDSResult = $this->getPostData()->getChildCategoriesOfParent($categoryData->cat_id);
                $catIDS = [];
                foreach ($catIDSResult as $catID) {
                    $catIDS[] = $catID->cat_id;
                }

                $queryData = $db->Select($tblCol)
                    ->From($postCatTbl)
                    ->Join($postTbl, table()->pickTable($postTbl, ['post_id']), table()->pickTable($postCatTbl, ['fk_post_id']))
                    ->Join($CatTbl, table()->pickTable($CatTbl, ['cat_id']), table()->pickTable($postCatTbl, ['fk_cat_id']))
                    ->WhereEquals('post_status', 1)
                    ->Where("$postTbl.created_at", '<=', helper()->date())
                    ->WhereIn('cat_id', $catIDS)->GroupBy('post_id')
                    ->OrderByDesc(table()->pickTable($postTbl, ['updated_at']))->SimplePaginate(30);
            });

            $rssSettingsData['Query'] = $queryData;
            response()->header("content-type: text/xml; charset=UTF-8");
            view('Apps::TonicsSeo/Views/rss', [
                'rssData' => $rssSettingsData,
            ]);
        } else {
            throw new URLNotFound('RSS Feed Not Found', SimpleState::ERROR_PAGE_NOT_FOUND__CODE);
        }
    }

    /**
     * @throws \Exception
     */
    private function getDefaultRobots ()
    {
        $sitemapURL = AppConfig::getAppUrl() . '/sitemap.xml';
        return <<<ROBOT
# EACH DAY IS A NEW BEGINNING - IrinAjobere
User-Agent: *
Disallow:
Sitemap: $sitemapURL
ROBOT;
    }

    /**
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function ads (): void
    {
        response()->header("content-type: text/plain; charset=UTF-8");
        $settings = self::getSettingsData();
        echo $settings['app_tonicsseo_ads_txt'] ?? '';
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    #[NoReturn] public function sitemap (): void
    {
        /** @var OnAddSitemap $sitemapTypeEvent */
        $sitemapTypeEvent = event()->dispatch(new OnAddSitemap());

        $includeSitemaps = self::getSettingsData()['app_tonicsseo_sitemap_handlers'] ?? [];
        $includeSitemaps = array_combine($includeSitemaps, $includeSitemaps);
        $includeSitemaps = array_change_key_case($includeSitemaps);

        $sitemapTypeEvent->setSitemap(helper()->mergeKeyIntersection($includeSitemaps, $sitemapTypeEvent->getSitemap()));

        response()->header("content-type: text/xml; charset=UTF-8");

        $sitemapIndexes = [];
        $typeNameFromParam = strtolower(url()->getParam('type', ''));
        if (url()->hasParam('type') && key_exists($typeNameFromParam, $includeSitemaps)) {
            /** @var AbstractSitemapInterface $sitemapHandlerObject */
            $sitemapHandlerObject = $sitemapTypeEvent->getSitemap()[$typeNameFromParam];
            $sitemapPerPage = (isset(self::getSettingsData()['app_tonicsseo_sitemap_per_page'])) ? (int)self::getSettingsData()['app_tonicsseo_sitemap_per_page'] : 1000;
            $sitemapHandlerObject->setLimit($sitemapPerPage);

            # If it includes a page param, then get the sitemap data
            if (url()->hasParam('page')) {
                $this->sitemapEntry($sitemapHandlerObject);
            }

            # Sitemap Index for page type
            if ($sitemapHandlerObject->getSitemapDataCount() > $sitemapPerPage) {
                $count = $sitemapHandlerObject->getSitemapDataCount();
                # Total Pages we can paginate through
                $totalPages = (int)ceil($count / $sitemapPerPage);
                for ($i = 1; $i <= $totalPages; ++$i) {
                    $indexURL = AppConfig::getAppUrl() . url()->appendQueryString("page=" . $i)->getRequestURLWithQueryString();
                    $sitemapIndexes[] = $indexURL;
                }
                $this->sitemapIndex($sitemapIndexes);
            }

            $this->sitemapEntry($sitemapHandlerObject);
        } else {
            foreach ($sitemapTypeEvent->getSitemap() as $sitemapName => $sitemapValue) {
                $indexURL = url()->getFullURL() . '?type=' . $sitemapName;
                $sitemapIndexes[] = $indexURL;
            }
            $this->sitemapIndex($sitemapIndexes);
        }
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    #[NoReturn] public function sitemapNews (): void
    {
        /** @var OnAddSitemap $sitemapTypeEvent */
        $sitemapTypeEvent = event()->dispatch(new OnAddSitemap());

        $includeSitemaps = self::getSettingsData()['app_tonicsseo_sitemap_handlers'] ?? [];
        $includeSitemaps = array_combine($includeSitemaps, $includeSitemaps);
        $includeSitemaps = array_change_key_case($includeSitemaps);

        $sitemapTypeEvent->setSitemap(helper()->mergeKeyIntersection($includeSitemaps, $sitemapTypeEvent->getSitemap()));

        response()->header("content-type: text/xml; charset=UTF-8");

        $sitemapIndexes = [];
        $typeNameFromParam = strtolower(url()->getParam('type', ''));
        if (url()->hasParam('type') && key_exists($typeNameFromParam, $includeSitemaps)) {
            /** @var AbstractSitemapInterface $sitemapHandlerObject */
            $sitemapHandlerObject = $sitemapTypeEvent->getSitemap()[$typeNameFromParam];
            $sitemapPerPage = (isset(self::getSettingsData()['app_tonicsseo_sitemap_per_page'])) ? (int)self::getSettingsData()['app_tonicsseo_sitemap_per_page'] : 1000;
            $sitemapHandlerObject->setLimit($sitemapPerPage);

            # If it includes a page param, then get the sitemap data
            if (url()->hasParam('page')) {
                $this->sitemapNewsEntry($sitemapHandlerObject);
            }

            # Sitemap Index for page type
            if ($sitemapHandlerObject->getSitemapNewsDataCount() > $sitemapPerPage) {
                $count = $sitemapHandlerObject->getSitemapNewsDataCount();
                # Total Pages we can paginate through
                $totalPages = (int)ceil($count / $sitemapPerPage);
                for ($i = 1; $i <= $totalPages; ++$i) {
                    $indexURL = AppConfig::getAppUrl() . url()->appendQueryString("page=" . $i)->getRequestURLWithQueryString();
                    $sitemapIndexes[] = $indexURL;
                }
                $this->sitemapIndex($sitemapIndexes);
            }

            $this->sitemapNewsEntry($sitemapHandlerObject);
        } else {
            foreach ($sitemapTypeEvent->getSitemap() as $sitemapName => $sitemapValue) {
                $indexURL = url()->getFullURL() . '?type=' . $sitemapName;
                $sitemapIndexes[] = $indexURL;
            }
            $this->sitemapIndex($sitemapIndexes);
        }
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    #[NoReturn] private function sitemapIndex ($sitemapIndexes): void
    {
        view('Apps::TonicsSeo/Views/sitemap', [
            'sitemapIndexes' => $sitemapIndexes,
        ]);
        exit();
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    #[NoReturn] private function sitemapEntry ($sitemapHandlerObject): void
    {
        view('Apps::TonicsSeo/Views/sitemap_entries', [
            'sitemapData' => $sitemapHandlerObject->getSitemapData(),
            'seoSettings' => self::getSettingsData(),
        ]);
        exit();
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    #[NoReturn] private function sitemapNewsEntry ($sitemapHandlerObject): void
    {
        view('Apps::TonicsSeo/Views/sitemap_news_entries', [
            'sitemapData' => $sitemapHandlerObject->getSitemapNewsData(),
            'seoSettings' => self::getSettingsData(),
        ]);
        exit();
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function robots (): void
    {
        $settings = self::getSettingsData();
        if (!isset($settings['app_tonicsseo_robots_txt'])) {
            $settings['app_tonicsseo_robots_txt'] = $this->getDefaultRobots();
        } elseif (empty($settings['app_tonicsseo_robots_txt'])) {
            $settings['app_tonicsseo_robots_txt'] = $this->getDefaultRobots();
        }
        response()->header("content-type: text/plain; charset=UTF-8");
        echo $settings['app_tonicsseo_robots_txt'];
    }

    /**
     * @throws \Exception
     */
    public static function getSettingsData (): array
    {
        return FieldConfig::loadPluginSettings(self::getCacheKey(), apcu_fetch(self::getCacheKey())) ?? [];
    }

    public static function getCacheKey (): string
    {
        return AppConfig::getAppCacheKey() . self::CACHE_KEY;
    }

    /**
     * @return FieldData|null
     */
    public function getFieldData (): ?FieldData
    {
        return $this->fieldData;
    }

    /**
     * @param FieldData|null $fieldData
     */
    public function setFieldData (?FieldData $fieldData): void
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @return PostData|null
     */
    public function getPostData (): ?PostData
    {
        return $this->postData;
    }

    /**
     * @param PostData|null $postData
     */
    public function setPostData (?PostData $postData): void
    {
        $this->postData = $postData;
    }
}