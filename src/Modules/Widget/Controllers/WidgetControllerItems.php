<?php

namespace App\Modules\Widget\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Controllers\Controller;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Widget\Data\WidgetData;
use App\Modules\Widget\Events\OnMenuWidgetMetaBox;
use App\Modules\Widget\Rules\WidgetValidationRules;

class WidgetControllerItems extends Controller
{
    use WidgetValidationRules, Validator;

    private WidgetData $widgetData;

    public function __construct(WidgetData $widgetData)
    {
        $this->widgetData = $widgetData;
    }

    /**
     * @throws \Exception
     */
    public function index(string $slug)
    {

        $widgetID = $this->getWidgetData()->getWidgetID($slug);
        if ($widgetID === null){
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $onMenuWidgetMetaBox = new OnMenuWidgetMetaBox();
        $dispatched = event()->dispatch($onMenuWidgetMetaBox);

        if (url()->getParam('action')){
            $action = url()->getParam('action');
            $slug = url()->getParam('slug');
            if ($action === 'getForm' && $slug){
                helper()->onSuccess($onMenuWidgetMetaBox->getWidgetForm($slug));
            }
        }

        view('Modules::Widget/Views/Items/index', [
            'SiteURL' => AppConfig::getAppUrl(),
            'MetaBox' => $dispatched->generateMenuWidgetMetaBox(),
            'MenuWidgetItems' => $this->getWidgetData()->getWidgetItemsListing($this->getWidgetData()->getWidgetItems($widgetID)),
            'MenuWidgetLocation' => $this->getWidgetData()->getWidgetLocationListing($this->getWidgetData()->getWidgetLocationRows(), $widgetID),
            'MenuWidgetBuilderName' => ucwords(str_replace('-', ' ', $slug)),
            'MenuWidgetSlug' => $slug,
            'MenuWidgetID' => $widgetID,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function store()
    {
        $menuWidgetSlug = input()->fromPost()->retrieve('menuWidgetSlug', '');

        # Stage One: Extract The menuWidgetDetails...
        try {
            $menuDetails = json_decode(input()->fromPost()->retrieve('menuWidgetDetails'), true);
            $validator = $this->getValidator()->make($menuDetails, $this->menuWidgetItemsStoreRule());
        }catch (\Exception){
            session()->flash(['An Error Occurred Extracting Menu Widget Data'], []);
            redirect(route('widgets.items.index', ['menu' => $menuWidgetSlug]));
        }

        # Stage Two: Working On The Extracted Data and Dumping In DB...
        $error = false;
        if ($validator->passes()){
            try {
                db()->beginTransaction();
                # Delete All the Menu Items Related to $menuDetails->menuID
                $this->getWidgetData()->deleteWithCondition(
                    whereCondition: "fk_widget_id = ?", parameter: [$menuDetails['menuWidgetID']], table: $this->getWidgetData()->getWidgetItemsTable());
                # Reinsert it
                db()->insertBatch($this->getWidgetData()->getWidgetItemsTable(), $menuDetails['menuWidgetItems']);
                # Insert or Update The Location
                db()->insertOnDuplicate($this->getWidgetData()->getWidgetLocationTable(), $menuDetails['menuWidgetLocation'], ['fk_widget_id', 'wl_name']);
                db()->commit();
                $error = true;
            }catch (\Exception){

            }
        }

        if ($error === false) {
            $menuDetails = json_decode(input()->fromPost()->retrieve('menuDetails'), true);
            session()->flash($validator->getErrors(), $menuDetails ?? []);
            redirect(route('widgets.items.index', ['menu' => $menuWidgetSlug]));
        }
        session()->flash(['Menu Widget Successfully Saved'], $menuDetails ?? [], type: Session::SessionCategories_FlashMessageSuccess);
        helper()->clearAPCUCache();
        redirect(route('widgets.items.index', ['menu' => $menuWidgetSlug]));
    }

    /**
     * @return WidgetData
     */
    public function getWidgetData(): WidgetData
    {
        return $this->widgetData;
    }
}