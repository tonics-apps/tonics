<?php

namespace App\Themes\NinetySeven\Controller;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnFieldUserForm;
use App\Modules\Page\Data\PageData;

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
        addToGlobalVariable('Assets', ['css' => AppConfig::getThemesAsset('NinetySeven', 'css/styles.css')]);
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
        $onFieldUserForm = new OnFieldUserForm([], new FieldData());
        renderBaseTemplate($this->getCacheKey(), cacheNotFound: function () use ($onFieldUserForm, $fieldSettings) {
            $fieldSlugs = $this->getFieldSlug($fieldSettings);
            $onFieldUserForm->handleFrontEnd($fieldSlugs, $fieldSettings);
            $this->saveTemplateCache();
        }, cacheFound: function () use ($onFieldUserForm, $fieldSettings) {
            # quick check if single template parts have not been cached...if not we force parse it...
            if (!isset(getBaseTemplate()->getModeStorage('add_hook')['site_credits'])){
                $fieldSlugs = $this->getFieldSlug($fieldSettings);
                $onFieldUserForm->handleFrontEnd($fieldSlugs, $fieldSettings);
                // re-save cache data
                $this->saveTemplateCache();
            }
            getBaseTemplate()->addToVariableData('Data', $fieldSettings);
        });
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

        return $fieldSlugs;
    }

    /**
     * @throws \Exception
     */
    public function saveTemplateCache(): void
    {
        getBaseTemplate()->removeVariableData('BASE_TEMPLATE');
        apcu_store($this->getCacheKey(), [
            'contents' => getBaseTemplate()->getContent(),
            'modeStorage' => getBaseTemplate()->getModeStorages(),
            'variable' => getBaseTemplate()->getVariableData()
        ]);
    }

    /**
     * @throws \Exception
     */
    public function getCacheKey(): string
    {
        return 'Standalone_Page_' . url()->getRequestURL() ?: env('APP_NAME', 'Tonics') . 'Standalone_Page_Home';
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