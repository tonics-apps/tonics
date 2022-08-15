<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\NinetySeven\Controller;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnFieldFormHelper;
use App\Modules\Page\Data\PageData;
use App\Modules\Page\Events\OnPageDefaultField;

class PagesController
{
    private PageData $pageData;
    private ?FieldData $fieldData;

    public function __construct(PageData $pageData, FieldData $fieldData = null)
    {
        $this->pageData = $pageData;
        $this->fieldData = $fieldData;
    }

    /**
     * @throws \Exception
     */
    public function viewPage()
    {
        addToGlobalVariable('Assets', ['css' => AppConfig::getAppAsset('NinetySeven', 'css/styles.min.css')]);
        $foundURL = url()->getRouteObject()->getRouteTreeGenerator()->getFoundURLNode();
        $page = $foundURL->getMoreSettings('GET');
        if (!is_object($page)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $fieldSettings = json_decode($page->field_settings, true);
        if (empty($fieldSettings)) {
            $fieldSettings = (array)$page;
        } else {
            $fieldSettings = [...$fieldSettings, ...(array)$page];
        }
        # Load Some Settings Option From Theme
        $fieldSettings = [...$fieldSettings, ...NinetySevenController::getSettingData()];
        $onFieldUserForm = new OnFieldFormHelper([], new FieldData());

        $fieldSlugs = $this->getFieldSlug($fieldSettings);
        $onFieldUserForm->handleFrontEnd($fieldSlugs, $fieldSettings);

        $pagePath = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLNode()?->getFullRoutePath();

        switch ($pagePath){
            case '/'; view('Apps::NinetySeven/Views/Page/single'); break;
            case '/categories'; view('Apps::NinetySeven/Views/Page/category-page'); break;
            case '/posts'; view('Apps::NinetySeven/Views/Page/post-page'); break;
        }
    }

    /**
     * @throws \Exception
     */
    public function getFieldSlug($page): array
    {
        $slug = $page['field_ids'];
        $fieldSlugs = json_decode($slug) ?? [];
        if (is_object($fieldSlugs)){
            $fieldSlugs = (array)$fieldSlugs;
        }

        if (empty($fieldSlugs) || !is_array($fieldSlugs)){
            // re-save default fields
            $default = ["default-page-field","post-home-page"];
            $updatePage = ['field_ids' => json_encode($default)];
            $this->pageData->updateWithCondition($updatePage, ['page_id' => (int)$page['page_id']], Tables::getTable(Tables::PAGES));
            return $default;
        }

        $hiddenSlug = event()->dispatch(new OnPageDefaultField())->getHiddenFieldSlug();
        return [...$fieldSlugs, ...$hiddenSlug];
    }

    /**
     * @return PageData
     */
    public function getPageData(): PageData
    {
        return $this->pageData;
    }

    /**
     * @return FieldData|null
     */
    public function getFieldData(): ?FieldData
    {
        return $this->fieldData;
    }
}