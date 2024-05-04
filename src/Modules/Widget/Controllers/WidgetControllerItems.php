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
    public function index(string $slug): void
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
            'MetaBox' => $dispatched->generateMenuWidgetMetaBox(),
            'MenuWidgetItems' => $this->getWidgetData()->getWidgetItemsListing($this->getWidgetData()->getWidgetItems($widgetID)),
            'MenuWidgetBuilderName' => ucwords(str_replace('-', ' ', $slug)),
            'MenuWidgetSlug' => $slug,
            'MenuWidgetID' => $widgetID,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function store(): void
    {
        $menuWidgetSlug = input()->fromPost()->retrieve('menuWidgetSlug', '');

        # Stage One: Extract The menuWidgetDetails...
        try {
            $menuDetails = json_decode(input()->fromPost()->retrieve('menuWidgetDetails'), true);
            $validator = $this->getValidator()->make($menuDetails ?? [], $this->menuWidgetItemsStoreRule());
        }catch (\Exception){
            session()->flash(['An Error Occurred Extracting Menu Widget Data'], []);
            redirect(route('widgets.items.index', ['menu' => $menuWidgetSlug]));
        }

        # Stage Two: Working On The Extracted Data and Dumping In DB...
        $error = false;
        if ($validator->passes()){
            try {
                # Reinsert it
                db(onGetDB: function ($db) use ($menuDetails){
                    $db->beginTransaction();
                    # Delete All the Menu Items Related to $menuDetails->menuID
                    $db->FastDelete($this->getWidgetData()->getWidgetItemsTable(), db()->WhereEquals('fk_widget_id', $menuDetails['menuWidgetID']));
                    # Reinsert it
                    $db->Insert($this->getWidgetData()->getWidgetItemsTable(), $menuDetails['menuWidgetItems']);
                    $db->commit();
                });
                $error = true;
            }catch (\Exception){
                // Log..
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