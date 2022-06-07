<?php

namespace App\Modules\Widget\Events;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnMenuWidgetMetaBox implements EventInterface
{
    private array $MenuWidgetBoxSettings = [];
    private $widgetSettings = null;

    /**
     * @throws \Exception
     */
    public function addMenuWidgetBox
    (
        string $name,
        string $description = '',
        callable $adminForm = null,
        callable $handleViewProcessing = null,
    )
    {
        $nameKey = helper()->slug($name);
        if(!key_exists($nameKey, $this->MenuWidgetBoxSettings)){
            $this->MenuWidgetBoxSettings[$nameKey] = (object)[
                'name' => $name,
                'description' => $description,
                'adminForm' => $adminForm ?? '',
                'handleViewProcessing' => $handleViewProcessing ?? '',
            ];
        }
    }

    public function generateMenuWidgetMetaBox(): string
    {
        $htmlFrag = ''; $checkBoxFrag = '';
        if (empty($this->MenuWidgetBoxSettings)){
            return $htmlFrag;
        }
        foreach ($this->MenuWidgetBoxSettings as $menuBoxName => $menuBox){
            $checkBoxFrag .= <<<HTML
<li class="menu-item">
    <input type="checkbox"
    data-action="getForm"
    data-name = "$menuBox->name"
    data-slug="$menuBoxName"
    id="$menuBoxName" name="menu-item" value="$menuBoxName">
    <label for="$menuBoxName">$menuBox->name</label>
</li>
HTML;
        }

        return <<<HTML
<li class="width:100% menu-item-parent-picker menu-box-li cursor:pointer">
    <fieldset class="padding:default d:flex">
        <legend class="bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
        Widgets
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
                </ul>
            </div>
            <button class="is-menu-checked listing-button border:none bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cursor:pointer act-like-button">Add To Widget
            </button>
        </div>
    </fieldset>
</li>
HTML;
    }

    /**
     * @param $widgetSlug
     * @param null $settings
     * @return string
     */
    public function getWidgetForm($widgetSlug, $settings = null): string
    {
        if(!key_exists($widgetSlug, $this->MenuWidgetBoxSettings)){
            return '';
        }

        $formCallback = $this->MenuWidgetBoxSettings[$widgetSlug]->adminForm;
        if (!is_callable($formCallback)){
            return '';
        }
        return $formCallback($settings);
    }

    /**
     * @param $widgetSlug
     * @param null $settings
     * @return string
     */
    public function getWidgetView($widgetSlug, $settings = null): string
    {
        if(!key_exists($widgetSlug, $this->MenuWidgetBoxSettings)){
            return '';
        }
        $formCallback = $this->MenuWidgetBoxSettings[$widgetSlug]->handleViewProcessing;
        return $formCallback($settings);
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    /**
     * @return null
     */
    public function getWidgetSettings()
    {
        return $this->widgetSettings;
    }

    /**
     * @param null $widgetSettings
     */
    public function setWidgetSettings($widgetSettings): OnMenuWidgetMetaBox
    {
        $this->widgetSettings = $widgetSettings;
        return $this;
    }
}