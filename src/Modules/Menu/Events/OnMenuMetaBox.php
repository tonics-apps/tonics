<?php

namespace App\Modules\Menu\Events;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnMenuMetaBox implements EventInterface
{

    private array $MenuBoxSettings = [];

    /**
     * @param $condition
     * @param callable $callback
     * @return OnMenuMetaBox
     */
    public function if($condition, callable $callback): static
    {
        if ($condition) {
            return $callback($this);
        }
        return $this;
    }

    /**
     * If the data items in the $paginationInfo are linkable,
     * be sure you add `_link` property to each of one the data items
     * @param string $name
     * @param string $svgIcon
     * @param \stdClass|null $paginationInfo
     * @param callable|null $moreMenuItems
     * @param callable|null $dataCondition
     * @return void
     * @throws \Exception
     */
    public function addMenuBox
    (
        string $name,
        string $svgIcon = '',
        \stdClass $paginationInfo = null,
        callable $moreMenuItems = null,
        callable $dataCondition = null,
    )
    {
        $nameKey = helper()->slug($name);
        if(!key_exists($nameKey, $this->MenuBoxSettings)){
            if($dataCondition === null){
                $dataCondition = function (){
                    return true;
                };
            }
            $this->MenuBoxSettings[$nameKey] = (object)[
                'name' => $name,
                'svgIcon' => $svgIcon,
                'paginationInfo' => $paginationInfo,
                'moreMenuItems' => $moreMenuItems,
                'condition' => $dataCondition
            ];
        }
    }

    public function generateMenuMetaBox(): string
    {
        $htmlFrag = '';
        if (empty($this->MenuBoxSettings)){
            return $htmlFrag;
        }

        foreach ($this->MenuBoxSettings as $menuBoxName => $menuBox){
            $checkBoxFrag = '';
            $htmlMoreFrag = '';

            # CHECKBOX
            if(isset($menuBox->paginationInfo->data) && is_array($menuBox->paginationInfo->data) && !empty($menuBox->paginationInfo->data)){
                $checkBoxFrag .=<<<HTML
<input style="margin-bottom: 1em;"
 data-action ="search" 
 data-query="{$menuBox->paginationInfo->path}&query="
 data-menuboxname = "$menuBoxName"
 data-searchvalue =""
 class="menu-box-item-search position:sticky top:0" type="search" required="" name="query" aria-label="Search and Hit Enter" placeholder="Search &amp; Hit Enter">
HTML;

                foreach ($menuBox->paginationInfo->data as $data){
                    $menuBoxCondition = $menuBox->condition;
                    $condition = $menuBoxCondition($data);
                    if($condition === true){
                        if(isset($data->_name)){
                            $link = (isset($data->_link)) ? $data->_link : '';
                            $id = $menuBoxName . '_' .$data->_name . '_' . $data->_id;
                            $checkBoxFrag .= <<<HTML
<li class="menu-item" data-parentid="null">
    <input type="checkbox" 
    data-url_slug="$link" 
    data-name = "$data->_name"
    id="$id" name="menu-item" value="$data->_name">
    <label for="$id">$data->_name</label>
</li>
HTML;
                        }
                    }
                }

                # MORE BUTTON
                if(isset($menuBox->paginationInfo->has_more) && $menuBox->paginationInfo->has_more){
                    $htmlMoreFrag = <<<HTML
 <button 
 data-morepageUrl="{$menuBox->paginationInfo->next_page_url}" 
 data-menuboxname = "$menuBoxName"
 data-nextpageid="{$menuBox->paginationInfo->next_page}"
 data-action = "more"
 class="border:none bg:white-one border-width:default border:black padding:gentle margin-top:0 cursor:pointer act-like-button more-button">More →</button>
HTML;
                }
            }

            # COMPLETE FRAGMENTS
            $htmlFrag .= <<<HTML
<li class="width:100% menu-item-parent-picker menu-box-li cursor:pointer">
    <fieldset class="padding:default d:flex">
        <legend class="tonics-legend bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
        $menuBox->name
            <button class="dropdown-toggle bg:transparent border:none cursor:pointer" aria-expanded="false" aria-label="Expand child menu">
                <svg class="icon:admin tonics-arrow-down color:white">
                    <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
                </svg>
            </button>
        </legend>
        <div class="d:none child-menu width:100% flex-d:column">
            <div class="menu-box-checkbox-items max-height:300px overflow:auto">
                <ul class="list:style:none">
                    $checkBoxFrag
                    $htmlMoreFrag
                </ul>
            </div>
            <button class="is-menu-checked listing-button border:none bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cursor:pointer act-like-button">Add To Menu
            </button>
        </div>
    </fieldset>
</li>
HTML;
        }

        return $htmlFrag;
    }

    /**
     * @param $menuBoxName
     * @param $menuBox
     * @return string
     * @throws \Exception
     */
    public function moreMenuItems($menuBoxName, $menuBox): string
    {
        $menuBoxName = helper()->slug($menuBoxName);
        $menuBoxSettings = $this->MenuBoxSettings[$menuBoxName];
        $checkBoxFrag = '';
        # CHECKBOX
        if(isset($menuBox->data) && is_array($menuBox->data) && !empty($menuBox->data)) {
            foreach ($menuBox->data as $k => $data) {
                if (isset($data->_name)) {
                    $link = (isset($data->_link)) ? $data->_link : '';
                    $id = $menuBoxName . '_' .$data->_name . '_' . $data->_id;

                    $menuBoxCondition = $menuBoxSettings->condition;
                    $condition = $menuBoxCondition($data);
                    if($condition === true){
                        $checkBoxFrag .= <<<HTML
<li class="menu-item" data-parentid="null">
    <input type="checkbox" 
    data-url_slug="$link" 
    data-name = "$data->_name"
    id="$id" name="menu-item" value="$data->_name">
    <label for="$id">$data->_name</label>
</li>
HTML;
                    }
                }
            }

            # MORE BUTTON
            if(isset($menuBox->has_more) && $menuBox->has_more){
                $htmlMoreFrag = <<<HTML
 <button 
 data-morepageUrl="{$menuBox->next_page_url}" 
 data-menuboxname = "$menuBoxName"
 data-nextpageid="{$menuBox->next_page}"
 data-action = "more"
 class="border:none bg:white-one border-width:default border:black padding:gentle margin-top:0 cursor:pointer act-like-button more-button">More →</button>
HTML;
                $checkBoxFrag .= $htmlMoreFrag;
            }
        }
        return $checkBoxFrag;
    }

    public function getMoreMenuBoxItems(string $menuBoxName): string
    {
        if(key_exists($menuBoxName, $this->MenuBoxSettings)){
            $menuBox = $this->MenuBoxSettings[$menuBoxName];
            $moreCallBack = $menuBox->moreMenuItems;
            if($moreCallBack instanceof \Closure){
                return $moreCallBack();
            }
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    /**
     * @return array
     */
    public function getMenuBoxSettings(): array
    {
        return $this->MenuBoxSettings;
    }
}