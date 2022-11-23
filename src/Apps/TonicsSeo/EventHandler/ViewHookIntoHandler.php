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
use App\Modules\Core\Configs\AppConfig;
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

        $event->hookInto('in_head', function (TonicsView $tonicsView){
            return $this->structuredData($tonicsView);
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
        $siteFavicon = helper()->htmlSpecChar($seoSettings['app_tonicsseo_site_favicon'] ?? "");
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

        if ($siteFavicon){
            $meta .=<<<SEO
<link rel="icon" href="$siteFavicon">

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

        $publishedTime = $this->seoTime($tonicsView, $tonicsView->accessArrayWithSeparator('Data.published_time') ?: $tonicsView->accessArrayWithSeparator('Data.created_at'));
        $createdTime = $this->seoTime($tonicsView, $tonicsView->accessArrayWithSeparator('Data.modified_time') ?: $tonicsView->accessArrayWithSeparator('Data.updated_at'));

        if ($ogURL){
            $meta .=<<<SEO
<meta property="article:published_time" content="$publishedTime" />
<meta property="article:modified_time" content="$createdTime" />

SEO;
        }

        $canonicalUrl = $tonicsView->accessArrayWithSeparator('Data.seo_canonical_url');
        $canonicalUrl = helper()->htmlSpecChar($canonicalUrl);
        $canonicalUrl = empty($canonicalUrl) ? AppConfig::getAppUrl() . $tonicsView->accessArrayWithSeparator('Data._preview_link') : $canonicalUrl;

        $meta .=<<<SEO
<link rel="canonical" href="$canonicalUrl" />

SEO;

        return $meta;
    }

    /**
     * @throws \Exception
     */
    private function structuredData(TonicsView $tonicsView): string
    {
        $meta = '';

        $fieldItems = $tonicsView->accessArrayWithSeparator('Data._fieldDetails');
        if (is_array($fieldItems)){
            $seoStructureData = 'seo_structured_data';
            $appTonicsseoStructuredDataFaqContainer = 'app-tonicsseo-structured-data-faq';
            $appTonicsseoStructuredDataFaqSchemaData = [];
            $appTonicsseoStructuredDataArticleContainer = 'app-tonicsseo-structured-data-article';
            $appTonicsseoStructuredDataArticleData = [
                'headline' => $tonicsView->accessArrayWithSeparator('Data.seo_title'),
                'datePublished' => $this->seoTime($tonicsView, $tonicsView->accessArrayWithSeparator('Data.published_time') ?: $tonicsView->accessArrayWithSeparator('Data.created_at')),
                'dateModified' => $this->seoTime($tonicsView, $tonicsView->accessArrayWithSeparator('Data.modified_time') ?: $tonicsView->accessArrayWithSeparator('Data.updated_at')),
                'images' => [],
                'authors' => [
                    $tonicsView->accessArrayWithSeparator('Data.user_name')
                ],
            ];
            $appTonicsseoStructuredDataProductReviewContainer = 'app-tonicsseo-structured-data-product-review';
            $appTonicsseoStructuredDataProductReviewData = [
                'name' => $tonicsView->accessArrayWithSeparator('Data.seo_title'),
                'description' => $tonicsView->accessArrayWithSeparator('Data.seo_title'),
                'positiveNotes' => [],
                'negativeNotes' => [],
                'author' => $tonicsView->accessArrayWithSeparator('Data.user_name'),
                'aggregate' => [
                    'ratingValue' => null,
                    'reviewCount' => null,
                ],
            ];

            foreach ($fieldItems as $fieldItem){
                if (isset($fieldItem->main_field_slug) && $fieldItem->main_field_slug === 'seo-settings' && $fieldItem->_children){
                    foreach ($fieldItem->_children as $child) {
                        if ($child->field_input_name === $seoStructureData) {
                            foreach ($child->_children ?? [] as $structuredChild){

                                if (isset($structuredChild->main_field_slug)){
                                    # Handle Collation of FAQ Structured Data
                                    if ($structuredChild->main_field_slug === $appTonicsseoStructuredDataFaqContainer){
                                        $question = (isset($structuredChild->_children[0]->field_options->app_tonics_seo_structured_data_faq_question))
                                            ? helper()->htmlSpecChar($structuredChild->_children[0]->field_options->app_tonics_seo_structured_data_faq_question)
                                            : null;

                                        $answer = (isset($structuredChild->_children[1]->field_options->app_tonics_seo_structured_data_faq_answer))
                                            ? helper()->htmlSpecChar($structuredChild->_children[1]->field_options->app_tonics_seo_structured_data_faq_answer)
                                            : null;

                                        if ($question && $answer){
                                            $appTonicsseoStructuredDataFaqSchemaData[] = <<<FAQ_SCHEMA
{
        "@type": "Question",
        "name": "$question",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "$answer"
        }
      }
FAQ_SCHEMA;
                                        }
                                    }

                                    # Handle Collation of Article Structured Data
                                    if ($structuredChild->main_field_slug === $appTonicsseoStructuredDataArticleContainer){
                                        $structuredDataArticleTypeFieldName = 'app_tonics_seo_structured_data_article_article_type';
                                        $structuredDataArticleHeadlineFieldName = 'app_tonics_seo_structured_data_article_headline';
                                        $structuredDataArticleImagesFieldName = 'app_tonics_seo_structured_data_article_image_repeater';
                                        $structuredDataArticleAuthorsFieldName = 'app_tonics_seo_structured_data_article_author_repeater';

                                        foreach ($structuredChild->_children as $articleField){

                                            if ($articleField->field_input_name === $structuredDataArticleTypeFieldName){
                                                $appTonicsseoStructuredDataArticleData['type'] = $articleField->field_options->{$structuredDataArticleTypeFieldName};
                                            }

                                            if ($articleField->field_input_name === $structuredDataArticleHeadlineFieldName){
                                                if (!empty($articleField->field_options->{$structuredDataArticleHeadlineFieldName})){
                                                    $appTonicsseoStructuredDataArticleData['headline'] = $articleField->field_options->{$structuredDataArticleHeadlineFieldName};
                                                }
                                            }

                                            if ($articleField->field_input_name === $structuredDataArticleImagesFieldName){
                                                if (isset($articleField->_children[0]->field_options->app_tonics_seo_structured_data_article_image)){
                                                    $imageURL = $articleField->_children[0]->field_options->app_tonics_seo_structured_data_article_image;
                                                    if (!empty($imageURL)){
                                                        $appTonicsseoStructuredDataArticleData['images'][]
                                                            = AppConfig::getAppUrl() . $articleField->_children[0]->field_options->app_tonics_seo_structured_data_article_image;
                                                    }
                                                }

                                            }
                                        }

                                        if (empty($appTonicsseoStructuredDataArticleData['images']) && !empty($tonicsView->accessArrayWithSeparator('Data.image_url'))){
                                            $appTonicsseoStructuredDataArticleData['images'][] =
                                                AppConfig::getAppUrl() . $tonicsView->accessArrayWithSeparator('Data.image_url');
                                        }
                                    }

                                    # Handle Collation of Product Review Data
                                    if ($structuredChild->main_field_slug === $appTonicsseoStructuredDataProductReviewContainer){
                                        $structuredDataProductReviewTypeFieldName = 'app_tonics_seo_structured_data_product_review_name';
                                        $structuredDataProductReviewTypeFieldDescription = 'app_tonics_seo_structured_data_product_review_desc';
                                        $structuredDataProductReviewTypeFieldPositiveContainer = 'app_tonics_seo_structured_data_product_review_positiveNoteContainer';
                                        $structuredDataProductReviewTypeFieldNegativeContainer = 'app_tonics_seo_structured_data_product_review_negativeNoteContainer';
                                        $structuredDataProductReviewTypeFieldAuthor = 'app_tonics_seo_structured_data_product_review_author';
                                        $structuredDataProductReviewTypeFieldAggregateRatingContainer = 'app_tonics_seo_structured_data_product_review_aggregateRatingContainer';

                                        foreach ($structuredChild->_children as $productReviewField){
                                            if ($productReviewField->field_input_name === $structuredDataProductReviewTypeFieldName){
                                                $appTonicsseoStructuredDataProductReviewData['name'] = $productReviewField->field_options->{$structuredDataProductReviewTypeFieldName};
                                            }
                                            if ($productReviewField->field_input_name === $structuredDataProductReviewTypeFieldDescription){
                                                $appTonicsseoStructuredDataProductReviewData['name'] = $productReviewField->field_options->{$structuredDataProductReviewTypeFieldDescription};
                                            }
                                            if ($productReviewField->field_input_name === $structuredDataProductReviewTypeFieldPositiveContainer){
                                                if (isset($productReviewField->_children[0]->field_options->app_tonics_seo_structured_data_product_review_positiveNote)){
                                                    $note = $productReviewField->_children[0]->field_options->app_tonics_seo_structured_data_product_review_positiveNote;
                                                    if (!empty($note)){
                                                        $appTonicsseoStructuredDataProductReviewData['positiveNotes'][]
                                                            = $productReviewField->_children[0]->field_options->app_tonics_seo_structured_data_product_review_positiveNote;
                                                    }
                                                }
                                            }
                                            if ($productReviewField->field_input_name === $structuredDataProductReviewTypeFieldNegativeContainer){
                                                if (isset($productReviewField->_children[0]->field_options->app_tonics_seo_structured_data_product_review_negativeNote)){
                                                    $note = $productReviewField->_children[0]->field_options->app_tonics_seo_structured_data_product_review_negativeNote;
                                                    if (!empty($note)){
                                                        $appTonicsseoStructuredDataProductReviewData['negativeNotes'][]
                                                            = $productReviewField->_children[0]->field_options->app_tonics_seo_structured_data_product_review_negativeNote;
                                                    }
                                                }
                                            }
                                            if ($productReviewField->field_input_name === $structuredDataProductReviewTypeFieldAggregateRatingContainer){
                                                $appTonicsseoStructuredDataProductReviewAggregateTypeRatingValue = 'app_tonics_seo_structured_data_product_review_rating_value';
                                                $appTonicsseoStructuredDataProductReviewAggregateTypeReviewCount = 'app_tonics_seo_structured_data_product_review_review_count';
                                                if (isset($productReviewField->_children)){
                                                    foreach ($productReviewField->_children as $aggregateChild){
                                                        if ($aggregateChild->field_input_name === $appTonicsseoStructuredDataProductReviewAggregateTypeRatingValue){
                                                            $appTonicsseoStructuredDataProductReviewData['aggregate']['ratingValue']
                                                                = $aggregateChild->field_options->{$appTonicsseoStructuredDataProductReviewAggregateTypeRatingValue};
                                                        }
                                                        if ($aggregateChild->field_input_name === $appTonicsseoStructuredDataProductReviewAggregateTypeReviewCount){
                                                            $appTonicsseoStructuredDataProductReviewData['aggregate']['reviewCount']
                                                                = $aggregateChild->field_options->{$appTonicsseoStructuredDataProductReviewAggregateTypeReviewCount};
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                            }
                        }
                    }
                }
            }

            # Handle FAQ Structured Data Fragment
            $FaqStructuredData = [...$appTonicsseoStructuredDataFaqSchemaData, ...$tonicsView->accessArrayWithSeparator('Structured_Data.FAQ') ?: []];
            $meta .= TonicsStructuredDataFAQHandlerAndSelection::handleStructuredData($FaqStructuredData);

            # Handle Article Structured Data Fragment
            if (!empty($appTonicsseoStructuredDataArticleData['authors'][0]) && isset($appTonicsseoStructuredDataArticleData['images'][0])){
                $type = trim($appTonicsseoStructuredDataArticleData['type']);
                $meta .= <<<ArticleStructuredData
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "{$type}",
    "headline": "{$appTonicsseoStructuredDataArticleData['headline']}",
    "datePublished": "{$appTonicsseoStructuredDataArticleData['datePublished']}",
    "dateModified": "{$appTonicsseoStructuredDataArticleData['dateModified']}",

ArticleStructuredData;

                # For Images
                $firstKey = array_key_first($appTonicsseoStructuredDataArticleData['images']);
                $lastKey = array_key_last($appTonicsseoStructuredDataArticleData['images']);
                foreach ($appTonicsseoStructuredDataArticleData['images'] as $key => $image){
                    if ($firstKey === $key){
                        $meta .= <<<ArticleStructuredData
    "image": [

ArticleStructuredData;
                    }
                    if ($lastKey === $key){
                        $meta .= <<<ArticleStructuredData
        "$image"
        ],
        
ArticleStructuredData;
                    } else {
                        $meta .= <<<ArticleStructuredData
        "$image",

ArticleStructuredData;
                    }
                }

                # For Authors
                $firstKey = array_key_first($appTonicsseoStructuredDataArticleData['authors']);
                $lastKey = array_key_last($appTonicsseoStructuredDataArticleData['authors']);
                $authorURLForNow = AppConfig::getAppUrl();
                foreach ($appTonicsseoStructuredDataArticleData['authors'] as $key => $author){
                    if ($firstKey === $key){
                        $meta .= <<<ArticleStructuredData
     "author": [

ArticleStructuredData;
                    }
                    if ($lastKey === $key){
                        $meta .= <<<ArticleStructuredData
        {
          "@type": "Person",
          "name": "$author",
          "url": "$authorURLForNow"
        }]
}
ArticleStructuredData;
                    } else {
                        $meta .= <<<ArticleStructuredData
        {
          "@type": "Person",
          "name": "$author",
          "url": "$authorURLForNow"
        },

ArticleStructuredData;
                    }
                }

                # Close Script
                $meta .= <<<ArticleStructuredData
</script>
ArticleStructuredData;
            }

            # Handle Product Review Structured Data Fragment
            if (!empty($appTonicsseoStructuredDataProductReviewData['aggregate']['ratingValue'])){
                $meta .= <<<ProductReviewStructuredData
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "{$appTonicsseoStructuredDataProductReviewData['name']}",
    "description": "{$appTonicsseoStructuredDataProductReviewData['description']}",
    "review": {
        "@type": "Review",
        "author": {
          "@type": "Person",
          "name": "{$appTonicsseoStructuredDataProductReviewData['author']}"
        }
      },
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "{$appTonicsseoStructuredDataProductReviewData['aggregate']['ratingValue']}",
        "reviewCount": "{$appTonicsseoStructuredDataProductReviewData['aggregate']['reviewCount']}"
      }
ProductReviewStructuredData;

                $positiveNoteFrag = '';
                if (!empty($appTonicsseoStructuredDataProductReviewData['positiveNotes'])){
                    $positiveNoteFrag .= <<<Note
,
      "positiveNotes": {
            "@type": "ItemList",
            "itemListElement": [
Note;

                    $lastKey = array_key_last($appTonicsseoStructuredDataProductReviewData['positiveNotes']);
                    foreach ($appTonicsseoStructuredDataProductReviewData['positiveNotes'] as $key => $positiveNote){
                        $position = $key + 1;
                        $positiveNoteFrag .= <<<Note

            {
                "@type": "ListItem",
                "position": $position,
                "name": "$positiveNote"
            }
Note;
                        if ($lastKey !== $key){
                            $positiveNoteFrag .= ',';
                        } else {
                            $positiveNoteFrag .= <<<Note
]
          }
Note;
                        }
                    }
                }


                $negativeNoteFrag = '';
                if (!empty($appTonicsseoStructuredDataProductReviewData['negativeNotes'])){
                    $negativeNoteFrag .= <<<Note
,
"negativeNotes": {
            "@type": "ItemList",
            "itemListElement": [
Note;
                    $lastKey = array_key_last($appTonicsseoStructuredDataProductReviewData['negativeNotes']);
                    foreach ($appTonicsseoStructuredDataProductReviewData['negativeNotes'] as $key => $positiveNote){
                        $position = $key + 1;
                        $negativeNoteFrag .= <<<Note

            {
                "@type": "ListItem",
                "position": $position,
                "name": "$positiveNote"
            }
Note;
                        if ($lastKey !== $key){
                            $negativeNoteFrag .= ',';
                        } else {
                            $negativeNoteFrag .= <<<Note
]
          }
Note;
                        }
                    }
                }

                $meta .= <<<ProductReviewStructuredData
        $positiveNoteFrag
        $negativeNoteFrag
    }
</script>
ProductReviewStructuredData;


            }
        }

        return $meta;
    }

    /**
     * @param $tonicsView
     * @param $time
     * @return string
     * @throws \Exception
     */
    private function seoTime($tonicsView, $time): string
    {
        $createdTime = str_replace(' ', 'T', $time). $tonicsView->accessArrayWithSeparator('App_Config.APP_TIME_ZONE_OFFSET');
        return helper()->htmlSpecChar($createdTime);
    }
}