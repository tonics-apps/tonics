<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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

use App\Apps\TonicsSeo\Controller\TonicsSeoController;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Field\Events\FieldSelectionDropper\OnAddFieldSelectionDropperEvent;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TonicsSeoFieldSelectionHandler implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent (object $event): void
    {
        /** @var OnAddFieldSelectionDropperEvent $event */
        $event->onBeforeProcessLogic('TonicsSeoSettingFields', function (OnAddFieldSelectionDropperEvent $event) {
            $tonicsSeoSettings = TonicsSeoController::getSettingsData();
            $event->processLogic($tonicsSeoSettings['_fieldDetailsSorted']->{"app-tonicsseo-settings"} ?? []);
        });

        $event->addFields('tonicsSeoStructuredData',
            [
                'app-tonicsseo-structured-data-product-review-new'                 => [
                    'children' => function (OnAddFieldSelectionDropperEvent $event) {
                        $event->hookIntoFieldDataKey('field_input_name', [
                            'app_tonics_seo_structured_data_product_review_name'         => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'app_tonics_seo_structured_data_product_review_name', 'TonicsSeoProductReviewName');
                            },
                            'app_tonics_seo_structured_data_product_review_desc'         => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'app_tonics_seo_structured_data_product_review_desc', 'TonicsSeoProductReviewDesc');
                            },
                            'app_tonics_seo_structured_data_product_review_positiveNote' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'app_tonics_seo_structured_data_product_review_positiveNote', 'TonicsSeoProductReviewPositiveNote[]');
                            },
                            'app_tonics_seo_structured_data_product_review_negativeNote' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'app_tonics_seo_structured_data_product_review_negativeNote', 'TonicsSeoProductReviewNegativeNote[]');
                            },
                            'app_tonics_seo_structured_data_product_review_author'       => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'app_tonics_seo_structured_data_product_review_author', 'TonicsSeoProductReviewAuthor');
                            },
                            'app_tonics_seo_structured_data_product_review_rating_value' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'app_tonics_seo_structured_data_product_review_rating_value', 'TonicsSeoProductReviewRating');
                            },
                            'app_tonics_seo_structured_data_product_review_review_count' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'app_tonics_seo_structured_data_product_review_review_count', 'TonicsSeoProductReviewReviewCount');
                            },
                        ]);
                    },
                ],
                'app-tonicsseo-structured-data-article-new'                        => [
                    'children' => function (OnAddFieldSelectionDropperEvent $event) {
                        $event->hookIntoFieldDataKey('field_input_name', [
                            'app_tonics_seo_structured_data_article_article_type' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'app_tonics_seo_structured_data_article_article_type', 'TonicsSeoArticleType');
                            },
                            'app_tonics_seo_structured_data_article_headline'     => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'app_tonics_seo_structured_data_article_headline', 'TonicsSeoArticleArticleHeadline');
                            },
                            'app_tonics_seo_structured_data_article_image'        => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $image = $event->accessFieldData($field, 'app_tonics_seo_structured_data_article_image');
                                if (str_starts_with($image, '/serve_file_path')) {
                                    $image = AppConfig::GlobalVariableConfig(AppConfig::GLOBAL_APP_SITE_URL) . $image;
                                }
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), fn() => $image, 'TonicsSeoArticleArticleImage[]');
                            },
                            'app_tonics_seo_structured_data_article_author'       => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'app_tonics_seo_structured_data_article_author', 'TonicsSeoArticleArticleAuthor[]');
                            },
                        ]);
                    },
                ],
                'app-tonicsseo-structured-data-faq'                                => [
                    'children' => function (OnAddFieldSelectionDropperEvent $event) {
                        $event->hookIntoFieldDataKey('field_input_name', [
                            'app_tonics_seo_structured_data_faq_question' => [
                                'open' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                    $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'app_tonics_seo_structured_data_faq_question', 'TonicsSeoFAQQuestion');
                                },
                            ],

                            'app_tonics_seo_structured_data_faq_answer' => [
                                'open' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                    if (!$event->propertyExistInField('TonicsFAQS', $event->getPage())) {
                                        $event->getPage()->TonicsFAQS = [];
                                    }

                                    $question = $event->accessDataFromPage('TonicsSeoFAQQuestion');
                                    $answer = $event->accessFieldData($field, 'app_tonics_seo_structured_data_faq_answer');
                                    $event->getPage()->TonicsFAQS[] = [
                                        'Answer'              => $answer,
                                        'Question'            => $question,
                                        'StructuredFAQ'       => TonicsStructuredDataFAQHandlerAndSelection::StructuredFAQ($question, $answer),
                                        'StructuredFAQToHTML' => TonicsStructuredDataFAQHandlerAndSelection::StructuredFAQToHTML($question, $answer),
                                    ];
                                },
                            ],
                        ]);
                    },
                ],
                // This slug doesn't exist in field
                'app-tonicsseo-settings-[slug-not-exist]-SearchEngineVerification' => [
                    'children' => function (OnAddFieldSelectionDropperEvent $event) {
                        $event->hookIntoFieldDataKey('field_input_name', [
                            'app_tonicsseo_google_verification_code'    => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'app_tonicsseo_google_verification_code', 'TonicsSeoSettingsGoogleVerificationCode');
                            },
                            'app_tonicsseo_bing_verification_code'      => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'app_tonicsseo_bing_verification_code', 'TonicsSeoSettingsBingVerificationCode');
                            },
                            'app_tonicsseo_pinterest_verification_code' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $page = $event->getPage();
                                $event->autoAddFieldPropertyToElement($field, $page, 'app_tonicsseo_pinterest_verification_code', 'TonicsSeoSettingsPinterestVerificationCode');
                            },
                        ]);
                    },
                ],
                'app-tonicsseo-settings-[slug-not-exist]-SearchEngineOptimization' => [
                    'children' => function (OnAddFieldSelectionDropperEvent $event) {
                        $event->hookIntoFieldDataKey('field_input_name', [
                            'app_tonicsseo_in_head'                     => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'app_tonicsseo_in_head', 'TonicsSeoSettingsInHead', false);
                            },
                            'app_tonicsseo_disable_injection_logged_in' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), fn() => $event->accessFieldData($field, 'app_tonicsseo_disable_injection_logged_in') === '0', 'TonicsSeoSettingsDisableInjection');
                            },
                            'app_tonicsseo_site_title'                  => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'app_tonicsseo_site_title', 'TonicsSeoSettingsSiteTitle');
                            },
                            'app_tonicsseo_site_favicon'                => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'app_tonicsseo_site_favicon', 'TonicsSeoSettingsSiteFavicon');
                            },
                            'app_tonicsseo_site_title_separator'        => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'app_tonicsseo_site_title_separator', 'TonicsSeoSettingsSiteSeparator');
                            },
                            'app_tonicsseo_site_title_location'         => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), fn() => $event->accessFieldData($field, 'app_tonicsseo_site_title_location') === 'left', 'TonicsSeoSettingsSiteTitleLocationIsLeftSide');
                            },
                            'app_tonicsseo_robots_txt'                  => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'app_tonicsseo_robots_txt', 'TonicsSeoSettingsRobotsTxt');
                            },
                            'app_tonicsseo_ads_txt'                     => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'app_tonicsseo_ads_txt', 'TonicsSeoSettingsADSTxt');
                            },
                        ]);
                    },
                ],
                'app-tonicsseo-structured-data-product-review'                     => [], # Deprecated
                'app-tonicsseo-structured-data-article'                            => [], # Deprecated
            ],
        );

        $event->onAfterProcessLogic('TonicsSeoSettingFields', function (OnAddFieldSelectionDropperEvent $event) {

            # Only Applies on the HomePage
            if (url()->getRequestURL() === AppConfig::GlobalVariableConfig(AppConfig::GLOBAL_APP_SITE_URL)) {

                if ($event->stringIsNotEmpty($event->accessPageData('TonicsSeoSettingsGoogleVerificationCode'))) {
                    $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, "<meta name='google-site-verification' content='{$event->accessPageData('TonicsSeoSettingsGoogleVerificationCode')}' />");
                }

                if ($event->stringIsNotEmpty($event->accessPageData('TonicsSeoSettingsBingVerificationCode'))) {
                    $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, "<meta name='msvalidate.01' content='{$event->accessPageData('TonicsSeoSettingsBingVerificationCode')}' />");
                }

                if ($event->stringIsNotEmpty($event->accessPageData('TonicsSeoSettingsPinterestVerificationCode'))) {
                    $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, "<meta name='p:domain_verify' content='{$event->accessPageData('TonicsSeoSettingsPinterestVerificationCode')}' />");
                }

            }

            if ($event->stringIsNotEmpty($event->accessPageData('TonicsSeoSettingsSiteFavicon'))) {
                $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, "<link rel='icon' href='{$event->accessPageData('TonicsSeoSettingsSiteFavicon')}'>");
            }

            if ($event->accessPageData('TonicsSeoSettingsSiteTitleLocationIsLeftSide')) {
                $sep = $event->accessPageData('TonicsSeoSettingsSiteSeparator');
                if ($event->stringEmpty($event->accessPageData('TonicsSeoSettingsSiteTitle'))) {
                    $sep = '';
                }
                $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, "<title>{$event->accessPageData('TonicsSeoSettingsSiteTitle')} $sep {$event->accessPageData('seo_title')}</title>");
            } else {
                $sep = $event->accessPageData('TonicsSeoSettingsSiteSeparator');
                if ($event->stringEmpty($event->accessPageData('seo_title'))) {
                    $sep = '';
                }
                $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, "<title>{$event->accessPageData('seo_title')} $sep {$event->accessPageData('TonicsSeoSettingsSiteTitle')}</title>");
            }

            $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, [
                "<meta name='description' content='{$event->accessPageData('seo_description')}' />",
                "<meta name='og:site_name' content='{$event->accessPageData('TonicsSeoSettingsSiteTitle')}' />",
                "<meta name='og:title' content='{$event->accessPageData('seo_title')}' />",
                "<meta name='og:description' content='{$event->accessPageData('seo_description')}' />",
            ]);

            if (!$event->accessPageData('seo_indexing')) {
                $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, "<meta name='robots' content='noindex' />");
            }

            if (!$event->accessPageData('seo_following')) {
                $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, "<meta name='robots' content='nofollow' />");
            }

            if ($event->stringIsNotEmpty($event->accessPageData('seo_og_type'))) {
                $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, "<meta name='og:type' content='{$event->accessPageData('seo_og_type')}' />");
            }

            if ($event->stringIsNotEmpty($event->accessPageData('seo_og_image'))) {
                $image = $event->accessPageData('seo_og_image');
                if (str_starts_with($image, '/serve_file_path')) {
                    $image = AppConfig::GlobalVariableConfig(AppConfig::GLOBAL_APP_SITE_URL) . $image;
                }
                $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, "<meta name='og:image' content='$image' />");
            }

            if ($event->stringIsNotEmpty($event->accessPageData('seo_og_url'))) {
                $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, "<meta name='og:url' content='{$event->accessPageData('seo_og_url')}' />");
            }

            if ($event->stringIsNotEmpty($event->accessPageData('published_time'))) {
                $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, "<meta property='article:published_time' content='{$event->accessPageData('published_time')}' />");
            }

            if ($event->stringIsNotEmpty($event->accessPageData('modified_time'))) {
                $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, "<meta property='article:modified_time' content='{$event->accessPageData('modified_time')}' />");
            }

            if ($event->stringIsNotEmpty($event->accessPageData('seo_canonical_url'))) {
                $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, "<link rel='canonical' href='{$event->accessPageData('seo_canonical_url')}' />");
            }

            if (AppConfig::GlobalVariableConfig(AppConfig::GLOBAL_AUTH_LOGGED_IN)) {
                if (!$event->accessPageData('TonicsSeoSettingsDisableInjection')) {
                    $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, $event->accessPageData('TonicsSeoSettingsInHead'));
                }
            } else {
                $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, $event->accessPageData('TonicsSeoSettingsInHead'));
            }

            # FAQ STRUCTURED DATA
            $faqs = [];
            if (is_iterable($event->accessPageData('TonicsFAQS', []))) {
                foreach ($event->accessPageData('TonicsFAQS', []) as $faq) {
                    $faqs[] = $faq['StructuredFAQ'];
                }
                $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, TonicsStructuredDataFAQHandlerAndSelection::HandleStructuredData($faqs));
            }

            # ARTICLE STRUCTURED DATA
            if ($event->stringIsNotEmpty($event->accessPageData('TonicsSeoArticleType'))) {
                $structuredData = [
                    "@context"      => "https://schema.org",
                    "@type"         => $event->accessPageData('TonicsSeoArticleType'),
                    "headline"      => $event->accessPageData('TonicsSeoArticleArticleHeadline'),
                    "datePublished" => $event->accessPageData('published_time'),
                    "dateModified"  => $event->accessPageData('modified_time'),
                    "image"         => $event->accessPageData('TonicsSeoArticleArticleImage'), // Image array
                    "author"        => [
                        "@type" => "Person",
                        "name"  => $event->accessPageData('TonicsSeoArticleArticleAuthor')[0] ?? '', // Assuming the first author is the primary one
                        "url"   => AppConfig::GlobalVariableConfig(AppConfig::GLOBAL_APP_SITE_URL),
                    ],
                ];
                $structuredDataJson = json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, '<script type="application/ld+json">' . $structuredDataJson . '</script>');
            }

            # PRODUCT REVIEW STRUCTURED DATA
            if ($event->stringIsNotEmpty($event->accessPageData('TonicsSeoProductReviewName'))) {
                $structuredData = [
                    "@context"        => "https://schema.org",
                    "@type"           => "Product",
                    "name"            => $event->accessPageData('TonicsSeoProductReviewName'),
                    "description"     => $event->accessPageData('TonicsSeoProductReviewName'),
                    "review"          => [
                        "@type"  => "Review",
                        "author" => [
                            "@type" => "Person",
                            "name"  => $event->accessPageData('TonicsSeoProductReviewAuthor'),
                        ],
                    ],
                    "aggregateRating" => [
                        "@type"       => "AggregateRating",
                        "ratingValue" => $event->accessPageData('TonicsSeoProductReviewRating'),
                        "reviewCount" => $event->accessPageData('TonicsSeoProductReviewReviewCount'),
                    ],
                    "positiveNotes"   => [
                        '@type'           => 'ItemList',
                        'itemListElement' => $event->accessPageData('TonicsSeoProductReviewPositiveNote'),
                    ],
                    "negativeNotes"   => [
                        '@type'           => 'ItemList',
                        'itemListElement' => $event->accessPageData('TonicsSeoProductReviewNegativeNote'),
                    ],
                ];
                $structuredDataJson = json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $event->addContentIntoHookTemplate($event::HOOK_NAME_IN_HEAD, '<script type="application/ld+json">' . $structuredDataJson . '</script>');
            }
        });
    }
}