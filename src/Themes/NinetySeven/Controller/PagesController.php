<?php

namespace App\Themes\NinetySeven\Controller;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Field\Data\FieldData;
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
        $fieldIDS = ($page->field_ids === null) ? [] : json_decode($page->field_ids, true);
        $fieldItems = $this->fieldData->generateFieldWithFieldSlug($fieldIDS, $fieldSettings, viewProcessing: true)->getHTMLFrag();

        view('Themes::NinetySeven/Views/Page/single', [
            'SiteURL' => AppConfig::getAppUrl(),
            'Data' => $fieldSettings,
            'Assets' => [
                'css' => AppConfig::getThemesAsset('NinetySeven', 'css/styles.css')
            ],
            'TimeZone' => AppConfig::getTimeZone(),
            'FieldItems' => $fieldItems,
        ]);
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