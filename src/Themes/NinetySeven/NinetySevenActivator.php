<?php

namespace App\Themes\NinetySeven;

use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Modules\Core\Events\OnSelectTonicsTemplateHooks;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Themes\NinetySeven\EventHandler\TemplateEngines\ThemeTemplateHooks;
use App\Themes\NinetySeven\Route\Routes;
use Devsrealm\TonicsRouterSystem\Route;

class NinetySevenActivator implements ModuleConfig, PluginConfig
{
    use Routes;

    private FieldData $fieldData;

    public function __construct(){
        $this->fieldData = new FieldData();
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function events(): array
    {
        return [
            OnSelectTonicsTemplateHooks::class => [
                ThemeTemplateHooks::class
            ]
        ];
    }

    /**
     * @inheritDoc
     * @throws \ReflectionException
     */
    public function route(Route $routes): Route
    {
        return $this->routeWeb($routes);
    }

    /**
     * @inheritDoc
     */
    public function tables(): array
    {
        return [];
    }

    /**
     * @throws \Exception
     */
    public function onInstall(): void
    {
        $this->fieldData->importFieldItems($this->getFieldItemsToImport());
    }

    /**
     * @throws \Exception
     */
    public function onUninstall(): void
    {
        $toDelete = "'site-header', 'site-footer', 'post-home-page', 'post-category', 'user-post-page', 'track-section-field', 'site-header-tracks', 'single-post'";
        db()->run("DELETE FROM {$this->fieldData->getFieldTable()} WHERE `field_slug` IN ($toDelete)");
    }

    public function info(): array
    {
        return [
            "name" => "NinetySeven",
            "type" => "Theme",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-Ola.1656111209',
            "description" => "NinetySeven Theme, The First Tonic Theme",
            "info_url" => '',
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/theme-ninetyseven/releases/latest",
            "authors" => [
                "name" => "The Devsrealm Guy",
                "email" => "faruq@devsrealm.com",
                "role" => "Developer"
            ],
            "credits" => []
        ];
    }

    public function onUpdate(): void
    {
        // TODO: Implement onUpdate() method.
    }

    public function getFieldItemsToImport(): array
    {
        $json =<<<'JSON'
[
  {
    "fk_field_id": "Site Header",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"7g0gl9zbq500000000000\",\"fieldName\":\"header\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"header\",\"attributes\":\"class=\\\"margin-top:0\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Site Header",
    "field_name": "modular_rowcolumn",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"7g0gl9zbq500000000000\",\"fieldName\":\"section\",\"inputName\":\"\",\"row\":\"2\",\"column\":\"1\",\"elementName\":\"section\",\"attributes\":\"class=\\\"site-header:admin d:flex flex-wrap:wrap flex-gap padding:default justify-content:space-around\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Site Header",
    "field_name": "modular_rowcolumn",
    "field_id": 3,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"7g0gl9zbq500000000000\",\"fieldName\":\"site logo\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"div\",\"attributes\":\"class=\\\"site-logo\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Site Header",
    "field_name": "modular_rowcolumn",
    "field_id": 4,
    "field_parent_id": 3,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"7g0gl9zbq500000000000\",\"fieldName\":\"a element\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"a\",\"attributes\":\"href=\\\"/\\\" rel=\\\"home\\\" itemprop=\\\"url\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Site Header",
    "field_name": "media_media-image",
    "field_id": 5,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"7g0gl9zbq500000000000\",\"fieldName\":\"Image\",\"inputName\":\"\",\"imageLink\":\"/logo/o-ola-micky-logo.svg\",\"featured_image\":\"\",\"defaultImage\":\"\",\"attributes\":\"class=\\\"image:avatar\\\" alt=\\\"Tonics Official Logo\\\" title=\\\"O-Ọlá Official Logo\\\"\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Site Header",
    "field_name": "modular_rowcolumn",
    "field_id": 6,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"2\",\"field_slug_unique_hash\":\"7g0gl9zbq500000000000\",\"fieldName\":\"navigation\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"nav\",\"attributes\":\"id=\\\"site-navigation\\\" class=\\\"site-nav d:flex align-items:center\\\" role=\\\"navigation\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Site Header",
    "field_name": "modular_rowcolumn",
    "field_id": 7,
    "field_parent_id": 6,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"7g0gl9zbq500000000000\",\"fieldName\":\"nav menu wrapper\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"ul\",\"attributes\":\"class=\\\"site-navigation-ul d:flex flex-gap list:style:none\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Site Header",
    "field_name": "menu_menus",
    "field_id": 8,
    "field_parent_id": 7,
    "field_options": "{\"field_slug\":\"menu_menus\",\"menu_menus_cell\":\"1\",\"field_slug_unique_hash\":\"7g0gl9zbq500000000000\",\"fieldName\":\"Menu\",\"inputName\":\"\",\"menuSlug\":\"header-menu:1\",\"displayName\":\"1\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Site Footer",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4yznv9bdlzs0000000000\",\"fieldName\":\"footer\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"footer\",\"attributes\":\"class=\\\"footer-area d:flex flex-wrap:wrap flex-gap padding:default justify-content:center\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Site Footer",
    "field_name": "modular_rowcolumn",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"4yznv9bdlzs0000000000\",\"fieldName\":\"footer section wrapper\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"nav\",\"attributes\":\"class=\\\"footer-section d:flex flex-d:column justify-content:center align-items:center flex-gap:small\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Site Footer",
    "field_name": "modular_rowcolumn",
    "field_id": 3,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"4yznv9bdlzs0000000000\",\"fieldName\":\"footer menu wrapper\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"ul\",\"attributes\":\"class=\\\"site-navigation-ul d:flex flex-gap list:style:none footer-with-social-icon\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Site Footer",
    "field_name": "menu_menus",
    "field_id": 4,
    "field_parent_id": 3,
    "field_options": "{\"field_slug\":\"menu_menus\",\"menu_menus_cell\":\"1\",\"field_slug_unique_hash\":\"4yznv9bdlzs0000000000\",\"fieldName\":\"Footer Menu\",\"inputName\":\"\",\"menuSlug\":\"footer-menu:2\",\"displayName\":\"0\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Site Footer",
    "field_name": "modular_tonicstemplatesystem",
    "field_id": 5,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"modular_tonicstemplatesystem\",\"modular_tonicstemplatesystem_cell\":\"1\",\"field_slug_unique_hash\":\"4yznv9bdlzs0000000000\",\"fieldName\":\"TonicsTemplateSystem\",\"tonicsTemplateFrag\":\"<div class=\\\"site-info\\\">\\n    \\n    <span class=\\\"site-footer-info\\\"> © 2022 Devsrealm | \\n        <a href=\\\"https://devsrealm.com/\\\">Powered by Tonics</a> | Theme: <a href=\\\"#\\\">NinetySeven by DevsRealmGuy</a>\\n    </span>\\n\\n</div>\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Post Home Page",
    "field_name": "modular_fieldselection",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_fieldselection\",\"field_slug_unique_hash\":\"1x8nhmkviv34000000000\",\"fieldName\":\"Site Header\",\"inputName\":\"site_header\",\"fieldSlug\":\"site-header:6\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Post Home Page",
    "field_name": "modular_rowcolumn",
    "field_id": 2,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"2ocmhmz2xhy0000000000\",\"fieldName\":\"Post Main Element\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"main\",\"attributes\":\"id=\\\"main\\\" class=\\\"flex:one bg:gray-one\\\" tabindex=\\\"-1\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Home Page",
    "field_name": "modular_rowcolumn",
    "field_id": 3,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"2ocmhmz2xhy0000000000\",\"fieldName\":\"Main Section\",\"inputName\":\"main_section\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"section\",\"attributes\":\"class=\\\"section color:black text-align:center padding:default\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Home Page",
    "field_name": "modular_rowcolumn",
    "field_id": 4,
    "field_parent_id": 3,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"2ocmhmz2xhy0000000000\",\"fieldName\":\"Header Wrapper\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"div\",\"attributes\":\"class=\\\"owl header-wrapper\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Home Page",
    "field_name": "modular_tonicstemplatesystem",
    "field_id": 5,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"modular_tonicstemplatesystem\",\"modular_tonicstemplatesystem_cell\":\"1\",\"field_slug_unique_hash\":\"2ocmhmz2xhy0000000000\",\"fieldName\":\"Inline Header Wrapper Styles\",\"tonicsTemplateFrag\":\"[[- I am lazy lol -]]\\n<style>\\n.header-wrapper > .header-title {\\n    font-size: clamp(2.05rem,2.04rem + 5.07vw,4.65rem);\\n    max-width: 20ch;\\n    margin-inline: auto;\\n}\\n\\n.header-wrapper > .header-description {\\n    font-size: clamp(1.56rem,1.39rem + 0.85vw,1.7rem);\\n    font-family: var(--font-serif);\\n    max-width: 30ch;\\n    margin-inline: auto;\\n    font-style: italic;\\n}\\n</style>\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Post Home Page",
    "field_name": "modular_rowcolumn",
    "field_id": 6,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"2ocmhmz2xhy0000000000\",\"fieldName\":\"Header Title\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"h1\",\"attributes\":\"class=\\\"header-title margin-top:0\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Home Page",
    "field_name": "input_text",
    "field_id": 7,
    "field_parent_id": 6,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2ocmhmz2xhy0000000000\",\"fieldName\":\"Text\",\"inputName\":\"header_title_text\",\"textType\":\"text\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"defaultValue\":\"Welcome To The World of Tonics Apps\",\"elementWrapper\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Post Home Page",
    "field_name": "modular_rowcolumn",
    "field_id": 8,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"2ocmhmz2xhy0000000000\",\"fieldName\":\"Header Description\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"p\",\"attributes\":\"class=\\\"header-description\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Home Page",
    "field_name": "input_text",
    "field_id": 9,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2ocmhmz2xhy0000000000\",\"fieldName\":\"Text\",\"inputName\":\"header_description_text\",\"textType\":\"text\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"defaultValue\":\"A niche by niche web application soothed for your goals\",\"elementWrapper\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Post Home Page",
    "field_name": "modular_rowcolumn",
    "field_id": 10,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"2ocmhmz2xhy0000000000\",\"fieldName\":\"owl marker\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"div\",\"attributes\":\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Home Page",
    "field_name": "modular_fieldselection",
    "field_id": 11,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"2ocmhmz2xhy0000000000\",\"fieldName\":\"Field\",\"inputName\":\"\",\"fieldSlug\":\"post-page:1\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Post Home Page",
    "field_name": "modular_rowcolumn",
    "field_id": 12,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"2ocmhmz2xhy0000000000\",\"fieldName\":\"Post Container\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"ul\",\"attributes\":\"id =\\\"postloop\\\" class=\\\"admin-widget list:style:none d:flex flex-wrap:wrap flex-gap padding:default\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Home Page",
    "field_name": "post_posts",
    "field_id": 13,
    "field_parent_id": 12,
    "field_options": "{\"field_slug\":\"post_posts\",\"post_posts_cell\":\"1\",\"field_slug_unique_hash\":\"2ocmhmz2xhy0000000000\",\"fieldName\":\"Posts Settings\",\"showPostImage\":\"0\",\"noOfPostPerPage\":\"6\",\"postDescriptionLength\":\"200\",\"postInCategories\":\"\",\"readMoreLabel\":\"Read More\",\"elementWrapper\":\"li\",\"attributes\":\"tabindex=\\\"0\\\" class=\\\"admin-widget-item-for-listing d:flex flex-d:column align-items:center justify-content:center cursor:pointer position:relative justify-content:flex-start\\\" data-selected=\\\"false\\\"\"}"
  },
  {
    "fk_field_id": "Post Home Page",
    "field_name": "modular_tonicstemplatesystem",
    "field_id": 14,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"modular_tonicstemplatesystem\",\"modular_tonicstemplatesystem_cell\":\"1\",\"field_slug_unique_hash\":\"2ocmhmz2xhy0000000000\",\"fieldName\":\"Post Prev and Next Button\",\"tonicsTemplateFrag\":\"[[import(\\\"Modules::Core/Views/Blocks/Default\\\")]]\\n\\n<div class=\\\"d:flex flex-gap:small justify-content:space-evenly padding:default\\\">\\n\\n    [[if(\\\"v[PostLoopData.prev_page_url]\\\")\\n<a type=\\\"submit\\\" class=\\\"border:none color:black border-width:default border:black padding:default\\n                        margin-top:0 cart-width cursor:pointer max-width:200 text-align:center text-underline\\\"\\n   title=\\\"Prev\\\" href=\\\"[[_v('PostLoopData.prev_page_url')]]#postloop\\\">\\n    Prev\\n</a>\\n    ]]\\n\\n    [[if(\\\"v[PostLoopData.next_page_url]\\\")\\n<a type=\\\"submit\\\" class=\\\"border:none color:black border-width:default border:black padding:default\\n                        margin-top:0 cart-width cursor:pointer max-width:200 text-align:center text-underline\\\"\\n   title=\\\"Next\\\" href=\\\"[[_v('PostLoopData.next_page_url')]]#postloop\\\">\\n    Next\\n</a>\\n    ]]\\n\\n</div>\\n\\n<div></div>\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Post Home Page",
    "field_name": "modular_fieldselection",
    "field_id": 15,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_fieldselection\",\"field_slug_unique_hash\":\"2clrm905c11c000000000\",\"fieldName\":\"Site Footer\",\"inputName\":\"site_footer\",\"fieldSlug\":\"site-footer:7\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Post Category",
    "field_name": "modular_tonicstemplatesystem",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_tonicstemplatesystem\",\"field_slug_unique_hash\":\"3ta3unnraqu0000000000\",\"fieldName\":\"post category header open\",\"tonicsTemplateFrag\":\"<header class=\\\"margin-top:0\\\">\\n<section class=\\\"d:flex flex-wrap:wrap flex-gap padding:default justify-content:space-around bg:gray-one\\\">\\n<nav id=\\\"site-navigation\\\" class=\\\"site-nav d:flex align-items:center\\\" role=\\\"navigation\\\">\\n<ul class=\\\"site-navigation-ul d:flex flex-gap list:style:none\\\">\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Post Category",
    "field_name": "menu_menus",
    "field_id": 2,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"menu_menus\",\"field_slug_unique_hash\":\"4srcdjdiudg0000000000\",\"fieldName\":\"post menu category\",\"inputName\":\"\",\"menuSlug\":\"post-categories:3\",\"displayName\":\"1\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Post Category",
    "field_name": "modular_tonicstemplatesystem",
    "field_id": 3,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_tonicstemplatesystem\",\"field_slug_unique_hash\":\"ijst8ivi38g000000000\",\"fieldName\":\"post category header close\",\"tonicsTemplateFrag\":\"</nav>\\n</ul>\\n</section>\\n</header>\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Track Section Field",
    "field_name": "modular_fieldselection",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_fieldselection\",\"field_slug_unique_hash\":\"6hzc62xuccw0000000000\",\"fieldName\":\"Track Header\",\"inputName\":\"\",\"fieldSlug\":\"site-header-tracks:18\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Track Section Field",
    "field_name": "modular_rowcolumn",
    "field_id": 2,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"1gr7i12disg0000000000\",\"fieldName\":\"Track Main Element\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"main\",\"attributes\":\"id=\\\"main\\\" class=\\\"flex:one bg:gray-one\\\" tabindex=\\\"-1\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Section Field",
    "field_name": "modular_tonicstemplatesystem",
    "field_id": 3,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"modular_tonicstemplatesystem\",\"modular_tonicstemplatesystem_cell\":\"1\",\"field_slug_unique_hash\":\"1gr7i12disg0000000000\",\"fieldName\":\"Header Inline Styles\",\"tonicsTemplateFrag\":\"<style>\\n\\nsection.section.color\\\\:black.text-align\\\\:center.padding\\\\:default {\\n    box-shadow: 10px 7px 0px #000000, 17px 11px 0px 0px #ffffff, inset 0px 0px 0px #89464833;\\n}\\n\\n.header-wrapper-written {\\n    display: flex;\\n    align-items: center;\\n    flex-direction: column;\\n    justify-content: center;\\n    gap: 1em;\\n}\\n\\n.header_section_title {\\n    font-size: clamp(2.05rem,1.04rem + 2.07vw,4.65rem);\\n    max-width: 20ch;\\n    margin-inline: auto;\\n    line-height: 1.1;\\n}\\n\\n.header_section_description {\\n    font-size: clamp(1.1rem,1rem + 0.85vw,1.2rem);\\n    max-width: 50ch;\\n    margin-inline: auto;\\n    color: #8b8b8b;\\n}\\n\\n\\n</style>\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Track Section Field",
    "field_name": "modular_rowcolumn",
    "field_id": 4,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"1gr7i12disg0000000000\",\"fieldName\":\"Header Section\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"section\",\"attributes\":\"class=\\\"section color:black text-align:center padding:default\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Section Field",
    "field_name": "input_text",
    "field_id": 5,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1gr7i12disg0000000000\",\"fieldName\":\"Header Section Title\",\"inputName\":\"header_section_title\",\"textType\":\"text\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"defaultValue\":\"\",\"elementWrapper\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"0\"}"
  },
  {
    "fk_field_id": "Track Section Field",
    "field_name": "input_text",
    "field_id": 6,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1gr7i12disg0000000000\",\"fieldName\":\"Header Section Description\",\"inputName\":\"header_section_description\",\"textType\":\"text\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"defaultValue\":\"\",\"elementWrapper\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"0\"}"
  },
  {
    "fk_field_id": "Track Section Field",
    "field_name": "media_media-image",
    "field_id": 7,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"1gr7i12disg0000000000\",\"fieldName\":\"Header Section Image\",\"inputName\":\"header_section_image\",\"imageLink\":\"https://cdn.pixabay.com/photo/2019/09/01/10/13/man-4444765_960_720.jpg\",\"featured_image\":\"\",\"defaultImage\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"0\"}"
  },
  {
    "fk_field_id": "Track Section Field",
    "field_name": "modular_tonicstemplatesystem",
    "field_id": 8,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"modular_tonicstemplatesystem\",\"modular_tonicstemplatesystem_cell\":\"1\",\"field_slug_unique_hash\":\"1gr7i12disg0000000000\",\"fieldName\":\"TonicsTemplateSystem\",\"tonicsTemplateFrag\":\"<div class=\\\"header-wrapper\\\">\\n    \\n    <div class=\\\"header-wrapper-written\\\">\\n        <div class=\\\"header_section_title\\\">\\n            [[v(\\\"Data.header_section_title\\\")]]\\n        </div>\\n\\n        <div class=\\\"header_section_description\\\">\\n            [[v(\\\"Data.header_section_description\\\")]]\\n        </div>\\n    </div>\\n\\n    <div class=\\\"header-wrapper-image\\\">\\n        <img loading=\\\"lazy\\\" decoding=\\\"async\\\"\\n             src='[[v(\\\"Data.header_section_image\\\")]]' class=\\\"\\\"\\n             alt=\\\"\\\">\\n    </div>\\n\\n</div>\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Track Section Field",
    "field_name": "modular_fieldselection",
    "field_id": 9,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"1gr7i12disg0000000000\",\"fieldName\":\"Field\",\"inputName\":\"\",\"fieldSlug\":\"post-category:16\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Track Section Field",
    "field_name": "track_tracks",
    "field_id": 10,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"track_tracks\",\"track_tracks_cell\":\"1\",\"field_slug_unique_hash\":\"1gr7i12disg0000000000\",\"fieldName\":\"Tracks Settings\",\"noOfTrackPerPage\":\"6\",\"showTrackImage\":\"1\"}"
  },
  {
    "fk_field_id": "Track Section Field",
    "field_name": "modular_tonicstemplatesystem",
    "field_id": 11,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"modular_tonicstemplatesystem\",\"modular_tonicstemplatesystem_cell\":\"1\",\"field_slug_unique_hash\":\"1gr7i12disg0000000000\",\"fieldName\":\"TonicsTemplateSystem\",\"tonicsTemplateFrag\":\"[[import(\\\"Modules::Core/Views/Blocks/Assets\\\")]]\\n[[import(\\\"Modules::Core/Views/Blocks/AudioPlayer\\\")]]\\n\\n\\n<section style=\\\"padding-left: 4em; padding-right: 4em;\\\" class=\\\"track-loop-section\\\">\\n    <ul class=\\\"track-loop-container admin-widget list:style:none d:flex flex-wrap:wrap flex-gap padding:default d:flex justify-content:center\\\">\\n        [[each(\\\"track in TrackData\\\")\\n        <li tabindex=\\\"0\\\" class=\\\"admin-widget-item-for-listing d:flex flex-d:column align-items:center justify-content:center cursor:pointer position:relative justify-content:flex-start\\\">\\n            <div class=\\\"owl width:100% padding:default border-width:default border:black color:black height:100%\\\">\\n                <div class=\\\"thumbnail\\\">\\n\\n                </div>\\n                <div class=\\\"text-on-admin-util owl cursor:text\\\">\\n                    <h4><a href=\\\"[[v('track.track_link')]]\\\" class=\\\"track-title color:black\\\" title=\\\"[[v('track.track_title')]]\\\">[[v(\\\"track.track_title\\\")]]</a></h4>\\n                    <a style=\\\"font-size: 90%;\\\" href=\\\"[[v('track.track_genre_link')]]\\\" class=\\\"track-genre color:black\\\">[[v('track.track_genre')]]</a>\\n                </div>\\n                <div class=\\\"cart-section owl\\\">\\n                    <div class=\\\"license-selector\\\">\\n                        <label for=\\\"track_[[v('track.track_slug')]]\\\">Choose License</label>\\n                        <select id=\\\"track_[[v('track.track_slug')]]\\\" class=\\\"selector mg-b-plus-1\\\" name=\\\"track_license\\\">\\n                            [[each(\\\"license in track.track_licenses\\\")\\n                                <option data-license_id=\\\"[[v('track.license_id')]]\\\" data-license_price=\\\"[[v('license.price')]]\\\" value=\\\"[[v('license.unique_id')]]\\\">[[v('license.name')]] → ($[[v('license.price')]])</option>\\n                            ]]\\n                        </select>\\n                    </div>\\n                    <div class=\\\"buttons-functional\\\">\\n                        <button type=\\\"button\\\" title=\\\"Play\\\"\\n                                data-tonics-audioplayer-track=\\\"\\\"\\n                                data-audioplayer_songurl=\\\"[[v('track.track_audio')]]\\\"\\n                                data-audioplayer_title=\\\"[[v('track.track_title')]]\\\"\\n                                data-audioplayer_image=\\\"[[v('track.track_image')]]\\\"\\n                                data-audioplayer_format=\\\"mp3\\\"\\n                                data-audioplayer_play=\\\"false\\\"\\n                                data-trackloaded=\\\"true\\\" class=\\\"audioplayer-track border:none act-like-button icon:audio bg:transparent cursor:pointer color:black\\\">\\n                            <svg class=\\\"audio-play icon:audio tonics-widget pointer-events:none\\\">\\n                                <use class=\\\"svgUse\\\" xlink:href=\\\"#tonics-audio-play\\\"></use>\\n                            </svg>\\n                            <svg class=\\\"audio-pause icon:audio tonics-widget pointer-events:none\\\">\\n                                <use class=\\\"svgUse\\\" xlink:href=\\\"#tonics-audio-pause\\\"></use>\\n                            </svg>\\n                        </button>\\n                    </div>\\n                </div>\\n                <div></div>\\n            </div>\\n        </li>\\n        ]]\\n    </ul>\\n</section>\\n\\n\\n[[- BeatPlayer Bottom Section -]]\\n<section class=\\\"d:none audio-player position:sticky bottom:0 left:0 z-index:audio-sticky-footer\\\">\\n    <div data-audioplayer_groupid=\\\"GLOBAL_GROUP\\\"\\n         class=\\\"audio-player-global-container bg:pure-black d:flex flex-d:column flex-gap:small position:relative\\\">\\n        <div class=\\\"audio-player-queue color:black bg:white-one border-width:default border:black d:none flex-gap flex-d:column height:600px width:320px bg:pure-white color:black padding:default position:absolute right:0 bottom:80px overflow:auto\\\">\\n            <div class=\\\"more-audio-player-control cursor:pointer\\\">\\n                [[mFunc('AudioPlayer::IconGenerate')\\n                [[arg('class' 'icon:audio color:black')]]\\n                [[arg('title' 'Repeat')]]\\n                [[arg('attr' 'data-audioplayer_repeat=\\\"false\\\"')]]\\n                [[bArg('more' 'AudioPlayer::Icon::Repeat')]]\\n                ]]\\n                [[mFunc('AudioPlayer::IconGenerate')\\n                [[arg('class' 'icon:audio color:black')]]\\n                [[arg('title' 'Shuffle')]]\\n                [[arg('attr' 'data-audioplayer_shuffle=\\\"false\\\"')]]\\n                [[bArg('more' 'AudioPlayer::Icon::Shuffle')]]\\n                ]]\\n            </div>\\n            <ol class=\\\"audio-player-queue-list d:flex flex-d:column flex-gap:small\\\">\\n            </ol>\\n        </div>\\n\\n        <div class=\\\"time-progress\\\">\\n            <div class=\\\"progress-container\\\">\\n                <input type=\\\"range\\\" step=\\\"1\\\" value=\\\"0\\\" class=\\\"song-slider\\\" min=\\\"0\\\" max=\\\"100\\\">\\n            </div>\\n        </div>\\n        <div class=\\\"others-audio-player-info d:flex flex-d:row align-items:center padding:0-1rwm\\\">\\n            <div id=\\\"meta-container\\\" class=\\\"beatplayer-item flex:one\\\">\\n                <div class=\\\"padding:0-1rwm\\\">\\n                    <img src=\\\"\\\" data-audioplayer_globalart class=\\\"main-album-art width:100px\\\" alt=\\\"\\\"/>\\n                </div>\\n                <div data-audioplayer_globaltitle\\n                     class=\\\"color:white width:200px text:no-wrap text-overflow:ellipsis\\\"></div>\\n            </div>\\n            <div class=\\\"cursor:pointer d:flex flex:one\\\">\\n                [[mFunc('AudioPlayer::IconGenerate')\\n                [[arg('class' 'icon:audio color:white')]]\\n                [[arg('title' 'Previous')]]\\n                [[arg('attr' 'data-audioplayer_prev=\\\"true\\\"')]]\\n                [[bArg('more' 'AudioPlayer::Icon::Prev')]]\\n                ]]\\n\\n                [[mFunc('AudioPlayer::IconGenerate')\\n                [[arg('class' 'global-play icon:audio-x-2 color:white')]]\\n                [[arg('title' 'Play')]]\\n                [[arg('attr' 'data-audioplayer_play=\\\"false\\\"')]]\\n                [[bArg('more' 'AudioPlayer::Icon::Play-Pause')]]\\n                ]]\\n\\n                [[mFunc('AudioPlayer::IconGenerate')\\n                [[arg('class' 'icon:audio color:white')]]\\n                [[arg('title' 'Next')]]\\n                [[arg('attr' 'data-audioplayer_next=\\\"true\\\"')]]\\n                [[bArg('more' 'AudioPlayer::Icon::Next')]]\\n                ]]\\n            </div>\\n            <div class=\\\"volume-slider-and-more d:flex align-items:center\\\">\\n                [[mFunc('AudioPlayer::IconGenerate')\\n                [[arg('class' 'dropdown-toggle color:white')]]\\n                [[arg('title' 'Queue')]]\\n                [[arg('attr' 'aria-expanded=\\\"false\\\" aria-label=\\\"Expand child menu\\\"')]]\\n                [[bArg('more' 'AudioPlayer::Icon::ArrowUp')]]\\n                ]]\\n                <label for=\\\"volume-slider\\\" class=\\\"screen-reader-text\\\"> Volume </label>\\n                <input id=\\\"volume-slider\\\" title=\\\"Volume\\\" type=\\\"range\\\" class=\\\"volume-slider\\\" min=\\\"0\\\" max=\\\"1\\\" step=\\\"0.1\\\">\\n            </div>\\n        </div>\\n    </div>\\n</section>\\n\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Track Section Field",
    "field_name": "modular_fieldselection",
    "field_id": 12,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_fieldselection\",\"field_slug_unique_hash\":\"6cup8k99zww0000000000\",\"fieldName\":\"Track Footer\",\"inputName\":\"\",\"fieldSlug\":\"site-footer:14\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Site Header (Tracks)",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"31g8sztvam3jnj7sbnthc\",\"fieldName\":\"header\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"header\",\"attributes\":\"class=\\\"margin-top:0\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Site Header (Tracks)",
    "field_name": "modular_rowcolumn",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"31g8sztvam3jnj7sbnthc\",\"fieldName\":\"section\",\"inputName\":\"\",\"row\":\"3\",\"column\":\"1\",\"elementName\":\"section\",\"attributes\":\"class=\\\"site-header:admin d:flex flex-wrap:wrap flex-gap padding:default align-items:center justify-content:space-around\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Site Header (Tracks)",
    "field_name": "media_media-image",
    "field_id": 3,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"31g8sztvam3jnj7sbnthc\",\"fieldName\":\"site logo\",\"inputName\":\"site_logo\",\"imageLink\":\"/logo/o-ola-micky-logo.svg\",\"featured_image\":\"\",\"defaultImage\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"0\"}"
  },
  {
    "fk_field_id": "Site Header (Tracks)",
    "field_name": "modular_tonicstemplatesystem",
    "field_id": 4,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"modular_tonicstemplatesystem\",\"modular_tonicstemplatesystem_cell\":\"1\",\"field_slug_unique_hash\":\"31g8sztvam3jnj7sbnthc\",\"fieldName\":\"site_logo_template\",\"tonicsTemplateFrag\":\"<div class=\\\"site-logo\\\">\\n<a href=\\\"/\\\" rel=\\\"home\\\" itemprop=\\\"url\\\">\\n<img src=\\\"[[v('site_logo.imageLink')]]\\\" class=\\\"image:avatar\\\" alt=\\\"[[v('site_logo.fieldName')]]\\\" title=\\\"[[v('site_logo.fieldName')]]\\\">\\n</a>\\n</div>\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Site Header (Tracks)",
    "field_name": "modular_rowcolumn",
    "field_id": 5,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"2\",\"field_slug_unique_hash\":\"31g8sztvam3jnj7sbnthc\",\"fieldName\":\"navigation\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"nav\",\"attributes\":\"id=\\\"site-navigation\\\" class=\\\"site-nav d:flex align-items:center\\\" role=\\\"navigation\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Site Header (Tracks)",
    "field_name": "modular_rowcolumn",
    "field_id": 6,
    "field_parent_id": 5,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"31g8sztvam3jnj7sbnthc\",\"fieldName\":\"nav menu wrapper\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"ul\",\"attributes\":\"class=\\\"site-navigation-ul d:flex flex-gap list:style:none\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Site Header (Tracks)",
    "field_name": "menu_menus",
    "field_id": 7,
    "field_parent_id": 6,
    "field_options": "{\"field_slug\":\"menu_menus\",\"menu_menus_cell\":\"1\",\"field_slug_unique_hash\":\"31g8sztvam3jnj7sbnthc\",\"fieldName\":\"Menu\",\"inputName\":\"\",\"menuSlug\":\"header-menu:1\",\"displayName\":\"1\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Site Header (Tracks)",
    "field_name": "modular_tonicstemplatesystem",
    "field_id": 8,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"modular_tonicstemplatesystem\",\"modular_tonicstemplatesystem_cell\":\"3\",\"field_slug_unique_hash\":\"31g8sztvam3jnj7sbnthc\",\"fieldName\":\"cart section\",\"tonicsTemplateFrag\":\"<div class=\\\"nav-right\\\">\\n    <div class=\\\"cart-button\\\">\\n        <span class=\\\"cb-counter\\\"><span class=\\\"cb-counter-label\\\">0</span></span>\\n        <button class=\\\"toggle bg:transparent border:none cursor:pointer\\\" aria-expanded=\\\"false\\\" aria-label=\\\"Expand child menu\\\" data-menutoggle_click_outside=\\\"true\\\">\\n            <svg class=\\\"icon color:black tonics-cart\\\">\\n                <use xlink:href=\\\"#tonics-cart\\\"></use>\\n            </svg>\\n        </button>\\n        <div class=\\\"child-menu cart flex-wrap:wrap flex-d:column align-items:center bg:gray-two z-index:cart position:absolute padding:default border-width:default border:black button:box-shadow-variant-2 swing-out-top-fwd d:none\\\">\\n            <div class=\\\"text-align:center text cart-header text-responsive color:black border-width:default border:black\\\">CART</div>\\n\\n            <div class=\\\"cart-item d:flex flex-wrap:wrap padding:2rem-1rem align-items:center flex-gap\\\">\\n                <img src=\\\"https://cdn.devsrealm.com/wp-content/uploads/2020/01/Devsreal-Author-Logo.svg\\\" class=\\\"image:avatar\\\" alt=\\\"Exclusivemusicplus Official Logo\\\">\\n                <div class=\\\"cart-detail\\\">\\n                    <span class=\\\"text cart-title\\\">(Free) Afrobeat Instrumental - Ferola - Easy</span>\\n                    <span class=\\\"text cart-license-price\\\">Mp3 License\\n                <span> → ($67.00)</span>\\n            </span>\\n                    <button class=\\\"bg:transparent  border:none color:black bg:white-one border-width:default border:black padding:small cursor:pointer button:box-shadow-variant-1\\\">\\n                        <span class=\\\"text text:no-wrap\\\">Remove</span>\\n                    </button>\\n                </div>\\n            </div>\\n\\n            <button class=\\\"width:100% bg:transparent  border:none color:black bg:white-one border-width:default border:black padding:default margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2\\\">\\n                <span class=\\\"text text:no-wrap\\\">CHECKOUT →</span>\\n            </button>\\n\\n        </div>\\n    </div>\\n</div>\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Single Post",
    "field_name": "modular_fieldselection",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_fieldselection\",\"field_slug_unique_hash\":\"1jw2hf0xy6ao000000000\",\"fieldName\":\"post header\",\"inputName\":\"\",\"fieldSlug\":\"site-header:6\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Single Post",
    "field_name": "modular_rowcolumn",
    "field_id": 2,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"1wrolfr640ewjs34sc4xs\",\"fieldName\":\"main\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"main\",\"attributes\":\"id=\\\"main\\\" class=\\\"flex:one bg:pure-white\\\" tabindex=\\\"-1\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Single Post",
    "field_name": "widget_widgets",
    "field_id": 3,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"widget_widgets\",\"widget_widgets_cell\":\"1\",\"field_slug_unique_hash\":\"1wrolfr640ewjs34sc4xs\",\"fieldName\":\"Sidebar Widget\",\"inputName\":\"sidebar_widget\",\"widgetSlug\":\"sidebar-widget:1\",\"elementName\":\"li\",\"attributes\":\"style=\\\"margin-top: clamp(3rem, 2.5vw, 2rem);\\\" tabindex=\\\"0\\\" class=\\\"owl width:100% padding:default menu-arranger-li color:black bg:white-one border-width:default border:black position:relative\\\"\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Single Post",
    "field_name": "modular_tonicstemplatesystem",
    "field_id": 4,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"modular_tonicstemplatesystem\",\"modular_tonicstemplatesystem_cell\":\"1\",\"field_slug_unique_hash\":\"1wrolfr640ewjs34sc4xs\",\"fieldName\":\"post template\",\"tonicsTemplateFrag\":\"[[import(\\\"Modules::Core/Views/Blocks/Default\\\")]]\\n[[import(\\\"Themes::NinetySeven/Views/Blocks/Post\\\")]]\\n\\n<article class=\\\"owl\\\" id=\\\"post_slug_id_[[v('Data.slug_id')]]\\\">\\n\\n       [[_use(\\\"Post::Single::Header\\\")]]\\n\\n        <section class=\\\"content-body bg:pure-white d:flex justify-content:space-evenly flex-wrap:wrap\\\">\\n            <article class=\\\"owl entry-content\\\">\\n                [[_v('Data.post_content')]]\\n            </article>\\n            [[if(\\\"v[sidebar_widget.data] !== block[empty]\\\")\\n                <aside>\\n                    <div class=\\\"owl sidebar-widget position:sticky top:0\\\">\\n                        <h4>[[_v('sidebar_widget.name')]]</h4>\\n                        <ul class=\\\"owl list:style:none\\\">\\n                            [[_v('sidebar_widget.data')]]\\n                        </ul>\\n                    </div>\\n                </aside>\\n            ]]\\n        </section>\\n\\n</article>\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "Single Post",
    "field_name": "modular_fieldselection",
    "field_id": 5,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_fieldselection\",\"field_slug_unique_hash\":\"1qw05nz6yt0g000000000\",\"fieldName\":\"post footer\",\"inputName\":\"\",\"fieldSlug\":\"site-footer:7\",\"handleViewProcessing\":\"1\"}"
  }
]
JSON;
        return json_decode($json);
    }

    public function getFieldTableItems(): string
    {
        return Tables::getTable(Tables::FIELD_ITEMS);
    }

    /**
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->fieldData;
    }

    /**
     * @param FieldData $fieldData
     */
    public function setFieldData(FieldData $fieldData): void
    {
        $this->fieldData = $fieldData;
    }
}