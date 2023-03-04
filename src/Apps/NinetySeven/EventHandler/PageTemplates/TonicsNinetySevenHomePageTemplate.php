<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\NinetySeven\EventHandler\PageTemplates;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Helper\FieldHelpers;
use App\Modules\Page\Events\AbstractClasses\PageTemplateInterface;
use App\Modules\Page\Events\OnPageTemplate;
use App\Modules\Post\Data\PostData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class TonicsNinetySevenHomePageTemplate implements PageTemplateInterface, HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var OnPageTemplate $event */
        $event->addTemplate($this);
    }

    public function name(): string
    {
        return 'TonicsNinetySeven_HomePageTemplate';
    }

    /**
     * @throws \Exception
     */
    public function handleTemplate(OnPageTemplate $pageTemplate): void
    {

        $postTbl = Tables::getTable(Tables::POSTS);
        $postCatTbl = Tables::getTable(Tables::POST_CATEGORIES);
        $CatTbl = Tables::getTable(Tables::CATEGORIES);

        try {
            $pageTemplate->setViewName('Apps::NinetySeven/Views/Page/single');

            $postData = db()->Select(PostData::getPostPaginationColumns())
                ->From($postCatTbl)
                ->Join($postTbl, table()->pickTable($postTbl, ['post_id']), table()->pickTable($postCatTbl, ['fk_post_id']))
                ->Join($CatTbl, table()->pickTable($CatTbl, ['cat_id']), table()->pickTable($postCatTbl, ['fk_cat_id']))
                ->WhereEquals('post_status', 1)
                ->Where("$postTbl.created_at", '<=', helper()->date())
                ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                    $db->WhereLike('post_title', url()->getParam('query'));
                })->when(url()->hasParamAndValue('cat'), function (TonicsQuery $db) {
                    $db->WhereIn('cat_id', url()->getParam('cat'));
                })
                ->GroupBy('post_id')
                ->OrderByDesc(table()->pickTable($postTbl, ['updated_at']))
                ->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));

            $categories = db()->Select(table()->pickTableExcept($CatTbl, ['field_settings', 'created_at', 'updated_at']))
                ->From(Tables::getTable(Tables::CATEGORIES))->FetchResult();

            $fieldSettings = $pageTemplate->getFieldSettings();
            $fieldSettings['NinetySevenPostData'] = $postData;
            $fieldSettings['NinetySevenPostCategoryData'] = (new PostData())->categoryCheckBoxListing($categories, url()->getParam('cat') ?? [], type: 'checkbox');
            $pageTemplate->setFieldSettings($fieldSettings);

        } catch (\Exception $exception){
            // Log..
        }


    }
}