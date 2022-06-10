<?php

namespace App\Modules\Widget\Data;

use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\Tables;
use App\Modules\Widget\Events\OnMenuWidgetMetaBox;

class WidgetData extends AbstractDataLayer
{
    use UniqueSlug;

    private const WidgetLocation_Sidebar = 'sidebar-section';

    public function getWidgetTable(): string
    {
        return Tables::getTable(Tables::WIDGETS);
    }

    public function getWidgetItemsTable(): string
    {
        return Tables::getTable(Tables::WIDGET_ITEMS);
    }

    public function getWidgetLocationTable(): string
    {
        return Tables::getTable(Tables::WIDGET_LOCATIONS);
    }

    public function getWidgetColumns(): array
    {
        return [ 'widget_id', 'widget_name', 'widget_slug', 'created_at', 'updated_at' ];
    }

    public function getWidgetLocationColumns(): array
    {
        return [ 'wl_id', 'wl_name', 'wl_slug', 'fk_widget_id' ];
    }

    public function getWidgetItemsColumns(): array
    {
        return [
            'id', 'fk_widget_id', 'wgt_id', 'wgt_name', 'wgt_options', 'created_at', 'updated_at'
        ];
    }

    /**
     * @throws \Exception
     */
    public function getWidgets(): mixed
    {
        $table = $this->getWidgetTable();
        return db()->run("SELECT * FROM $table");
    }

    /**
     * @throws \Exception
     */
    public function getWidgetLocationRows()
    {
        $widgetTable = $this->getWidgetLocationTable();
        return db()->run("SELECT * FROM $widgetTable;");
    }

    /**
     * @param string $slug
     * @return mixed
     * @throws \Exception
     */
    public function getWidgetID(string $slug): mixed
    {
        $table = $this->getWidgetTable();
        return db()->row("SELECT `widget_id` FROM $table WHERE `widget_slug` = ?", $slug)->widget_id ?? null;
    }

    /**
     * @throws \Exception
     */
    public function getWidgetItems(int $fkWidgetID): array
    {
        $table = $this->getWidgetItemsTable();
        $result = db()->run("SELECT * FROM $table WHERE `fk_widget_id` = ?", $fkWidgetID);
        return $this->decodeWidgetOptions($result);
    }

    /**
     * @param string $location
     * @param callable|null $onBeforeDecodingWidget
     * @return array
     * @throws \Exception
     */
    public function getWidgetLocationItems(string $location = self::WidgetLocation_Sidebar, callable $onBeforeDecodingWidget = null): array
    {
        $widgetLocationTable = $this->getWidgetLocationTable();
        $widgetItemTable = $this->getWidgetItemsTable();
        $widgetTable = $this->getWidgetTable();

        $result = db()->run(<<<SQL
            SELECT * FROM $widgetItemTable
            JOIN $widgetLocationTable ON $widgetLocationTable.fk_widget_id = $widgetItemTable.fk_widget_id
            JOIN $widgetTable ON $widgetTable.widget_id = $widgetLocationTable.fk_widget_id
WHERE wl_slug = ?
SQL, $location);
        if ($onBeforeDecodingWidget !== null){
            $onBeforeDecodingWidget($result);
        }
        return $this->decodeWidgetOptions($result);
    }

    /**
     * @param $widgetData
     * @return array
     */
    public function decodeWidgetOptions($widgetData): array
    {
        $widgetResult = [];
        if (!empty($widgetData) && is_array($widgetData)){
            $widgetResult = array_map(function ($value){
                if (is_string($value->wgt_options)){
                    $value->wgt_options = json_decode($value->wgt_options);
                }
                return $value;
            }, $widgetData);
        }

        return $widgetResult;
    }

    /**
     * @throws \Exception
     */
    public function adminWidgetListing($widgets): string
    {
        $csrfToken = session()->getCSRFToken();
        $htmlFrag = '';
        foreach ($widgets as $k => $widget) {
            $htmlFrag .= <<<HTML
    <li 
    data-list_id="$k" data-id="$widget->widget_id"  
    data-widget_id="$widget->widget_id" 
    data-widget_slug="$widget->widget_slug" 
    data-widget_name="$widget->widget_name"
    data-db_click_link="/admin/tools/widget/$widget->widget_slug/edit"
    tabindex="0" 
    class="admin-widget-item-for-listing d:flex flex-d:column align-items:center justify-content:center cursor:move no-text-highlight">
        <fieldset class="padding:default width:100% box-shadow-variant-1 d:flex justify-content:center">
            <legend class="bg:pure-black color:white padding:default">$widget->widget_name</legend>
            <div class="admin-widget-information owl width:100%">
            <div class="text-on-admin-util text-highlight">$widget->widget_name</div>
         
                <div class="form-group d:flex flex-gap:small">
                     <a href="/admin/tools/widget/$widget->widget_slug/edit" class="listing-button text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Edit</a>
                        
                         <a href="/admin/tools/widget/items/$widget->widget_slug/builder" class="listing-button text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Builder</a>
                   
                   <form method="post" class="d:contents" action="/admin/tools/widget/$widget->widget_slug/delete">
                    <input type="hidden" name="token" value="$csrfToken" >
                       <button data-click-onconfirmdelete="true" type="button" class="listing-button bg:pure-black color:white border:none border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Delete</button>
                    </form>
                </div>
                
            </div>
        </fieldset>
    </li>
HTML;
        }

        return $htmlFrag;
    }

    /**
     * @throws \Exception
     */
    public function createWidget(array $ignore = []): array
    {
        $slug = $this->generateUniqueSlug($this->getWidgetTable(),
            'widget_slug', helper()->slug(input()->fromPost()->retrieve('widget_slug')));

        $menu = []; $postColumns = array_flip($this->getWidgetColumns());
        foreach (input()->fromPost()->all() as $inputKey => $inputValue){
            if (key_exists($inputKey, $postColumns) && input()->fromPost()->has($inputKey)){

                if($inputKey === 'created_at'){
                    $menu[$inputKey] = helper()->date(timestamp: $inputValue);
                    continue;
                }
                if ($inputKey === 'widget_slug'){
                    $menu[$inputKey] = $slug;
                    continue;
                }
                $menu[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $menu);
        if (!empty($ignores)){
            foreach ($ignores as $v){
                unset($menu[$v]);
            }
        }

        return $menu;
    }

    /**
     * @param mixed $getWidgetItems
     * @return string
     * @throws \Exception
     */
    public function getWidgetItemsListing(mixed $getWidgetItems): string
    {
        # re-dispatch so we can get the form values
        $onMenuWidgetMetaBox = new OnMenuWidgetMetaBox();
        $onMenuWidgetMetaBox = event()->dispatch($onMenuWidgetMetaBox);
        $frag = '';
        foreach ($getWidgetItems as $widgetItem){
            $slug = $widgetItem->wgt_options->widget_slug ?? null;
            $formData = $onMenuWidgetMetaBox->getWidgetForm($slug, $widgetItem->wgt_options?? null);
            $name = ucwords(str_replace('-', ' ', $slug));
            $frag .= <<<HTML
<li tabIndex="0"
               class="width:100% draggable menu-arranger-li cursor:move">
        <span class="width:100% height:100% z-index:hidden-over-draggable draggable-hidden-over"></span>
        <fieldset
            class="width:100% padding:default box-shadow-variant-1 d:flex justify-content:center pointer-events:none">
            <legend class="bg:pure-black color:white padding:default pointer-events:none d:flex flex-gap:small align-items:center">
                <span class="menu-arranger-text-head">$name</span>
                <button class="dropdown-toggle bg:transparent border:none pointer-events:all cursor:pointer"
                        aria-expanded="false" aria-label="Expand child menu">
                    <svg class="icon:admin tonics-arrow-down color:white">
                        <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
                    </svg>
                </button>
            </legend>
            <form class="widgetSettings d:none flex-d:column menu-widget-information pointer-events:all owl width:100%">
                <input type="hidden" name="widget_slug" value="$slug">
               $formData
                <div class="form-group">
                    <button name="delete" class="delete-menu-arrange-item listing-button border:none bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cursor:pointer act-like-button">
                        Delete Widget Item
                    </button>
                </div>
            </form>
        </fieldset>
    </li>
HTML;
        }
        return $frag;
    }

    /**
     * Widget For FrontPage
     * @param mixed $getWidgetItems
     * @return string
     * @throws \Exception
     */
    public function getWidgetViewListing(mixed $getWidgetItems, callable $fragWrapper = null): string
    {
        # re-dispatch so we can get the form values
        $onMenuWidgetMetaBox = new OnMenuWidgetMetaBox();
        $onMenuWidgetMetaBox = event()->dispatch($onMenuWidgetMetaBox);
        $frag = '';
        foreach ($getWidgetItems as $widgetItem) {
            $slug = $widgetItem->wgt_options->widget_slug ?? null;
            $name = (isset($widgetItem->wgt_options->widgetName)) ?
                ucwords(str_replace('-', ' ', $widgetItem->wgt_options->widgetName)) :
                ucwords(str_replace('-', ' ', $slug));
            $viewData = $onMenuWidgetMetaBox->getWidgetView($slug, $widgetItem->wgt_options ?? null);
            if ($fragWrapper !== null){
                $frag .=$fragWrapper($viewData);
            } else {
                $frag .= <<<HTML
<li style="margin-top: clamp(3rem, 2.5vw, 2rem);" tabIndex="0" class="owl width:100% padding:default menu-arranger-li color:black bg:white-one border-width:default border:black position:relative">
    <span class="widget-title bg:pure-black color:white padding:small">$name</span>
        $viewData
</li>
HTML;
            }
        }

        return $frag;
    }

    public function getWidgetLocationListing(mixed $widgetLocation, $widgetID): string
    {
        $frag = '';
        foreach ($widgetLocation as $location){
            if ($location->fk_widget_id === $widgetID){
                $checkExisting =<<<HTML
<input type="checkbox" 
data-wl-id="$location->fk_widget_id" 
data-wl-name="$location->wl_name"
data-wl-slug="$location->wl_slug" 
id="$location->wl_slug" name="menu-location" value="$location->wl_slug" checked="checked">
<label for="$location->wl_slug">$location->wl_name
</label>
HTML;
            }else{
                $checkExisting =<<<HTML
<input type="checkbox" 
data-wl-id="$location->fk_widget_id" 
data-wl-name="$location->wl_name"
data-wl-slug="$location->wl_slug" 
id="$location->wl_slug" name="menu-location" value="$location->wl_slug">
<label for="$location->wl_slug">$location->wl_name
</label>
HTML;
            }
            $frag .=<<<HTML
 <li class="menu-item">
 $checkExisting
</li>
HTML;

        }

        return $frag;
    }

}