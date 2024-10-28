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

namespace App\Modules\Field;

use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\FieldItemsExtensionConfig;
use App\Modules\Core\EventHandlers\Field\CacheFieldIDItems;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\EventHandlers\DefaultFieldHandlers\TonicsDefaultFieldsSelection;
use App\Modules\Field\EventHandlers\DefaultFieldHandlers\TonicsOEmbedFieldHandler;
use App\Modules\Field\EventHandlers\DefaultSanitization\DefaultSlugFieldSanitization;
use App\Modules\Field\EventHandlers\DefaultSanitization\PageSlugFieldSanitization;
use App\Modules\Field\EventHandlers\DefaultSanitization\PostContentEditorFieldSanitization;
use App\Modules\Field\EventHandlers\FieldMenus;
use App\Modules\Field\EventHandlers\Fields\Input\InputChoices;
use App\Modules\Field\EventHandlers\Fields\Input\InputColor;
use App\Modules\Field\EventHandlers\Fields\Input\InputColorPicker;
use App\Modules\Field\EventHandlers\Fields\Input\InputDate;
use App\Modules\Field\EventHandlers\Fields\Input\InputRange;
use App\Modules\Field\EventHandlers\Fields\Input\InputRichText;
use App\Modules\Field\EventHandlers\Fields\Input\InputSelect;
use App\Modules\Field\EventHandlers\Fields\Input\InputText;
use App\Modules\Field\EventHandlers\Fields\Interfaces\Table;
use App\Modules\Field\EventHandlers\Fields\Media\MediaAudio;
use App\Modules\Field\EventHandlers\Fields\Media\MediaFileManager;
use App\Modules\Field\EventHandlers\Fields\Media\MediaImage;
use App\Modules\Field\EventHandlers\Fields\Menu\Menu;
use App\Modules\Field\EventHandlers\Fields\Modular\FieldFileHandler;
use App\Modules\Field\EventHandlers\Fields\Modular\FieldSelection;
use App\Modules\Field\EventHandlers\Fields\Modular\FieldSelectionDropper;
use App\Modules\Field\EventHandlers\Fields\Modular\RowColumn;
use App\Modules\Field\EventHandlers\Fields\Modular\RowColumnRepeater;
use App\Modules\Field\EventHandlers\Fields\Modular\TemplateHooks;
use App\Modules\Field\EventHandlers\Fields\Post\PostAuthorSelect;
use App\Modules\Field\EventHandlers\Fields\Post\PostCategorySelect;
use App\Modules\Field\EventHandlers\Fields\Post\PostRecent;
use App\Modules\Field\EventHandlers\Fields\Tools\Currency;
use App\Modules\Field\EventHandlers\Fields\Track\TrackArtist;
use App\Modules\Field\EventHandlers\Fields\Track\TrackArtistSelect;
use App\Modules\Field\EventHandlers\Fields\Track\TrackCategorySelect;
use App\Modules\Field\EventHandlers\Fields\Track\TrackGenre;
use App\Modules\Field\EventHandlers\Fields\Track\TrackGenreSelect;
use App\Modules\Field\EventHandlers\Fields\Track\TrackLicenseSelect;
use App\Modules\Field\EventHandlers\FieldSelectionDropper\LayoutSelector;
use App\Modules\Field\Events\FieldSelectionDropper\OnAddFieldSelectionDropperEvent;
use App\Modules\Field\Events\FieldTemplateFile;
use App\Modules\Field\Events\OnAddFieldSanitization;
use App\Modules\Field\Events\OnAfterPreSavePostEditorFieldItems;
use App\Modules\Field\Events\OnComparedSortedFieldCategories;
use App\Modules\Field\Events\OnEditorFieldSelection;
use App\Modules\Field\Events\OnFieldCreate;
use App\Modules\Field\Events\OnFieldItemsSave;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Routes\Routes;
use Devsrealm\TonicsRouterSystem\Route;

class FieldActivator implements ExtensionConfig, FieldItemsExtensionConfig
{

    use Routes;

    private FieldData $fieldData;

    public function __construct ()
    {
        $this->fieldData = new FieldData();
    }

    /**
     * @inheritDoc
     */
    public function enabled (): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function events (): array
    {
        return [

            OnFieldItemsSave::class => [
                CacheFieldIDItems::class,
            ],

            OnFieldMetaBox::class => [
                # INPUT
                InputText::class,
                InputRange::class,
                InputChoices::class,
                InputDate::class,
                InputRichText::class,
                InputSelect::class,
                InputColor::class,
                InputColorPicker::class,

                # POSTS
                PostCategorySelect::class,
                PostAuthorSelect::class,
                PostRecent::class,

                # TRACKS
                TrackArtist::class,
                TrackGenre::class,
                TrackLicenseSelect::class,
                TrackGenreSelect::class,
                TrackArtistSelect::class,
                TrackCategorySelect::class,

                # Media
                MediaFileManager::class,
                MediaImage::class,
                MediaAudio::class,

                # Modular
                RowColumn::class,
                RowColumnRepeater::class,
                FieldSelection::class,
                FieldSelectionDropper::class,
                FieldFileHandler::class,
                TemplateHooks::class,

                # Menu
                Menu::class,

                # Tools
                Currency::class,

                # Interfaces
                Table::class,
            ],

            OnEditorFieldSelection::class => [
                TonicsDefaultFieldsSelection::class,
            ],

            FieldTemplateFile::class => [
                TonicsOEmbedFieldHandler::class,
            ],

            OnFieldCreate::class => [
            ],

            OnAdminMenu::class => [
                FieldMenus::class,
            ],

            OnAfterPreSavePostEditorFieldItems::class => [

            ],

            OnAddFieldSanitization::class => [
                PageSlugFieldSanitization::class,
                DefaultSlugFieldSanitization::class,
                PostContentEditorFieldSanitization::class,
            ],

            OnAddFieldSelectionDropperEvent::class => [
                LayoutSelector::class,
                TonicsOEmbedFieldHandler::class,
            ],

            OnComparedSortedFieldCategories::class => [
                LayoutSelector::class,
            ],
        ];
    }

    /**
     * @param Route $routes
     *
     * @return Route
     * @throws \ReflectionException
     */
    public function route (Route $routes): Route
    {
        return $this->routeWeb($routes);
    }

    /**
     * @return array
     */
    public function tables (): array
    {
        return
            [
                Tables::getTable(Tables::FIELD)       => Tables::$TABLES[Tables::FIELD],
                Tables::getTable(Tables::FIELD_ITEMS) => Tables::$TABLES[Tables::FIELD_ITEMS],
            ];
    }

    public function onInstall (): void
    {
        // TODO: Implement onInstall() method.
    }

    public function onUninstall (): void
    {
        // TODO: Implement onUninstall() method.
    }

    public function info (): array
    {
        return [
            "name"                 => "Field",
            "type"                 => "Module",
            "slug_id"              => "73df171d-2740-11ef-9736-124c30cfdb6b",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version"              => '1-O-Ola.1718718690',
            "stable"               => 0,
            "description"          => "The Field Module",
            "info_url"             => '',
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-field-module/releases/latest",
            "authors"              => [
                "name"  => "The Devsrealm Guy",
                "email" => "faruq@devsrealm.com",
                "role"  => "Developer",
            ],
            "credits"              => [],
        ];
    }

    /**
     * @throws \Exception
     */
    public function onUpdate (): void
    {
        $this->fieldData->importFieldItems($this->fieldItems());
    }

    public function onDelete (): void
    {
        // TODO: Implement onDelete() method.
    }

    function fieldItems (): array
    {
        $json = <<<'JSON'
[
	{
		"field_field_name": "Layout 2 by 2",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "layout-2-by-2",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"7cu8kckliug0000000000\",\"field_input_name\":\"layout2by2\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Four-Panel Layout (2x2 Grid)\",\"inputName\":\"layout2by2\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout 2 by 2",
		"field_name": "modular_rowcolumn",
		"field_id": 2,
		"field_slug": "layout-2-by-2",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"6pgsiybzgew0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"\",\"row\":\"2\",\"column\":\"2\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout 2 by 2",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 3,
		"field_slug": "layout-2-by-2",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"2mr4ta4nc5o0000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 2 by 2",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 4,
		"field_slug": "layout-2-by-2",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"2\",\"field_slug_unique_hash\":\"76ma1xgi72g0000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 2 by 2",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 5,
		"field_slug": "layout-2-by-2",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"3\",\"field_slug_unique_hash\":\"4kden1gp9gk0000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 2 by 2",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 6,
		"field_slug": "layout-2-by-2",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"4\",\"field_slug_unique_hash\":\"17fiy1l9dtts000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 2 by 2",
		"field_name": "modular_fieldselection",
		"field_id": 7,
		"field_slug": "layout-2-by-2",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"25t7rrpvshc0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"\",\"fieldSlug\":\"layout-settings\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Layout 2 by 2",
		"field_name": "input_text",
		"field_id": 8,
		"field_slug": "layout-2-by-2",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1zndadvre5ds000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"ℹ️ Note\",\"inputName\":\"\",\"textType\":\"hidden\",\"defaultValue\":\"\",\"info\":\"This layout support variants (you can add a class with any of the variants):\\n<br><br>\\nlayout-2-by-2-variant-2fr-1fr <br>\\nlayout-2-by-2-variant-1fr-2fr\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Layout 1 by 1 (Single-Column)",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "layout-1-by-1",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"82l2qqmg0w4000000000\",\"field_input_name\":\"layout1by1\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Single-Column Layout (1x1 Grid)\",\"inputName\":\"layout1by1\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout 1 by 1 (Single-Column)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 2,
		"field_slug": "layout-1-by-1",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"4dl17sc1hgy0000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 1 by 1 (Single-Column)",
		"field_name": "modular_fieldselection",
		"field_id": 3,
		"field_slug": "layout-1-by-1",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"43ranlaf0gc0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"\",\"fieldSlug\":\"layout-settings\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Layout 1 by 2 (Two-Column)",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "layout-1-by-2",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"54j5a23d7800000000000\",\"field_input_name\":\"layout1by2\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Two-Column Layout (1x2 Grid)\",\"inputName\":\"layout1by2\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout 1 by 2 (Two-Column)",
		"field_name": "modular_rowcolumn",
		"field_id": 2,
		"field_slug": "layout-1-by-2",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"5fwp82yr91o0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"2\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout 1 by 2 (Two-Column)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 3,
		"field_slug": "layout-1-by-2",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"hrt0lbbhlew000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 1 by 2 (Two-Column)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 4,
		"field_slug": "layout-1-by-2",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"2\",\"field_slug_unique_hash\":\"kk7am747zf4000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 1 by 2 (Two-Column)",
		"field_name": "modular_fieldselection",
		"field_id": 5,
		"field_slug": "layout-1-by-2",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"6tsj6hpinro0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"\",\"fieldSlug\":\"layout-settings\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Layout 1 by 2 (Two-Column)",
		"field_name": "input_text",
		"field_id": 6,
		"field_slug": "layout-1-by-2",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5ndury8popc0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"ℹ️ Note\",\"inputName\":\"\",\"textType\":\"hidden\",\"defaultValue\":\"\",\"info\":\"This layout support variants (you can add a class with any of the variants):\\n<br><br>\\nlayout-1-by-2-variant-2fr-1fr <br>\\nlayout-1-by-2-variant-1fr-2fr\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"1\",\"required\":\"0\",\"styles\":\"height:200px\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Layout Magazine (Header, Two Columns, Footer)",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "layout-magazine-header-two-columns-footer",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4zixhj3u6uo0000000000\",\"field_input_name\":\"layoutMagazine\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Layout Magazine \",\"inputName\":\"layoutMagazine\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout Magazine (Header, Two Columns, Footer)",
		"field_name": "modular_rowcolumn",
		"field_id": 2,
		"field_slug": "layout-magazine-header-two-columns-footer",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"5um0cfwdr8s0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout Magazine (Header, Two Columns, Footer)",
		"field_name": "modular_rowcolumn",
		"field_id": 3,
		"field_slug": "layout-magazine-header-two-columns-footer",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"pm6r2agabhc000000000\",\"field_input_name\":\"layoutMagazineHeader\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Header\",\"inputName\":\"layoutMagazineHeader\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout Magazine (Header, Two Columns, Footer)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 4,
		"field_slug": "layout-magazine-header-two-columns-footer",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"1gsr73jz0sgw000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout Magazine (Header, Two Columns, Footer)",
		"field_name": "modular_rowcolumn",
		"field_id": 5,
		"field_slug": "layout-magazine-header-two-columns-footer",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"3ktlkg5f9jc0000000000\",\"field_input_name\":\"layoutMagazineTwoCols\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Two Columns\",\"inputName\":\"layoutMagazineTwoCols\",\"row\":\"1\",\"column\":\"2\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout Magazine (Header, Two Columns, Footer)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 6,
		"field_slug": "layout-magazine-header-two-columns-footer",
		"field_parent_id": 5,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"6fycsstvk4w0000000000\",\"field_input_name\":\"layoutMagazineColLeft\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"layoutMagazineColLeft\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout Magazine (Header, Two Columns, Footer)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 7,
		"field_slug": "layout-magazine-header-two-columns-footer",
		"field_parent_id": 5,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"2\",\"field_slug_unique_hash\":\"62hafihw7io0000000000\",\"field_input_name\":\"layoutMagazineColRight\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"layoutMagazineColRight\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout Magazine (Header, Two Columns, Footer)",
		"field_name": "modular_rowcolumn",
		"field_id": 8,
		"field_slug": "layout-magazine-header-two-columns-footer",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"3fqszzqrkfq0000000000\",\"field_input_name\":\"layoutMagazineFooter\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Footer\",\"inputName\":\"layoutMagazineFooter\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout Magazine (Header, Two Columns, Footer)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 9,
		"field_slug": "layout-magazine-header-two-columns-footer",
		"field_parent_id": 8,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"a40ydwx4qmg000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout Magazine (Header, Two Columns, Footer)",
		"field_name": "modular_fieldselection",
		"field_id": 10,
		"field_slug": "layout-magazine-header-two-columns-footer",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"4qh09bdbyk60000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"\",\"fieldSlug\":\"layout-settings\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Layout 2 by 3 Grid (Two Rows, Three Columns)",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "layout-2-by-3",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"45cfg0nyzt00000000000\",\"field_input_name\":\"layout2by3\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Layout 2 by 3 (Two Rows, Three Columns)\",\"inputName\":\"layout2by3\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout 2 by 3 Grid (Two Rows, Three Columns)",
		"field_name": "modular_rowcolumn",
		"field_id": 2,
		"field_slug": "layout-2-by-3",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"3ds4280fh5g0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"\",\"row\":\"2\",\"column\":\"3\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout 2 by 3 Grid (Two Rows, Three Columns)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 3,
		"field_slug": "layout-2-by-3",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"1a5pvb2xohk0000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 2 by 3 Grid (Two Rows, Three Columns)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 4,
		"field_slug": "layout-2-by-3",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"2\",\"field_slug_unique_hash\":\"577apdquaqk0000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 2 by 3 Grid (Two Rows, Three Columns)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 5,
		"field_slug": "layout-2-by-3",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"3\",\"field_slug_unique_hash\":\"35h8gbnmsdi0000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 2 by 3 Grid (Two Rows, Three Columns)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 6,
		"field_slug": "layout-2-by-3",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"4\",\"field_slug_unique_hash\":\"1fbd3ivqy274000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 2 by 3 Grid (Two Rows, Three Columns)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 7,
		"field_slug": "layout-2-by-3",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"5\",\"field_slug_unique_hash\":\"4q96rcoz22y0000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 2 by 3 Grid (Two Rows, Three Columns)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 8,
		"field_slug": "layout-2-by-3",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"6\",\"field_slug_unique_hash\":\"70np92z32hc0000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 2 by 3 Grid (Two Rows, Three Columns)",
		"field_name": "modular_fieldselection",
		"field_id": 9,
		"field_slug": "layout-2-by-3",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"1e51hdudgkbk000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"\",\"fieldSlug\":\"layout-settings\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Layout 2 by 3 Grid (Two Rows, Three Columns)",
		"field_name": "input_text",
		"field_id": 10,
		"field_slug": "layout-2-by-3",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5vln1jdv86w0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"ℹ️ Note\",\"inputName\":\"\",\"textType\":\"hidden\",\"defaultValue\":\"\",\"info\":\"This layout support variants (you can add a class with any of the variants):\\n<br><br>\\nlayout-2-by-3-variant-1fr-2fr-1fr <br>\\nlayout-2-by-3-variant-1fr-2fr-2fr <br>\\nlayout-2-by-3-variant-2fr-1fr-2fr <br>\\nlayout-2-by-3-variant-2fr-1fr-1fr <br>\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Layout 1 by 3 (Three-Column)",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "layout-1-by-3",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"7267xlc7izw0000000000\",\"field_input_name\":\"layout1by3\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Layout Three-Column\",\"inputName\":\"layout1by3\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout 1 by 3 (Three-Column)",
		"field_name": "modular_rowcolumn",
		"field_id": 2,
		"field_slug": "layout-1-by-3",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"45cczimicu00000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"3\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout 1 by 3 (Three-Column)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 3,
		"field_slug": "layout-1-by-3",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"jhd240l3bco000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 1 by 3 (Three-Column)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 4,
		"field_slug": "layout-1-by-3",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"2\",\"field_slug_unique_hash\":\"sexhnagapw0000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 1 by 3 (Three-Column)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 5,
		"field_slug": "layout-1-by-3",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"3\",\"field_slug_unique_hash\":\"m0tei0vx018000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 1 by 3 (Three-Column)",
		"field_name": "modular_fieldselection",
		"field_id": 6,
		"field_slug": "layout-1-by-3",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"6f99x4rpjsw0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"\",\"fieldSlug\":\"layout-settings\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Layout 1 by 3 (Three-Column)",
		"field_name": "input_text",
		"field_id": 7,
		"field_slug": "layout-1-by-3",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"68yhdqnpad0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"ℹ️ Note\",\"inputName\":\"\",\"textType\":\"hidden\",\"defaultValue\":\"\",\"info\":\"This layout support variants (you can add a class with any of the variants):\\n<br><br>\\nlayout-1-by-3-variant-1fr-2fr-1fr <br>\\nlayout-1-by-3-variant-1fr-2fr-2fr <br>\\nlayout-1-by-3-variant-2fr-1fr-2fr <br>\\nlayout-1-by-3-variant-2fr-1fr-1fr <br>\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Text Element",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "text-element",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"5ohsvjmr6bg0000000000\",\"field_input_name\":\"textElement\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Text\",\"inputName\":\"textElement\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Text Element",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "text-element",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2em4vf91i4cg000000000\",\"field_input_name\":\"rawTextValue\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Text\",\"inputName\":\"rawTextValue\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Text\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Text Element",
		"field_name": "modular_rowcolumn",
		"field_id": 3,
		"field_slug": "text-element",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"5rqame5pwwc0000000000\",\"field_input_name\":\"tonicsPageBuilderSettings\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"tonicsPageBuilderSettings\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Text Element",
		"field_name": "input_text",
		"field_id": 4,
		"field_slug": "text-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2kj5ud3qyvc0000000000\",\"field_input_name\":\"htmlTagCustomTag\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Custom Tag\",\"inputName\":\"htmlTagCustomTag\",\"textType\":\"text\",\"defaultValue\":\"p\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Custom Tag\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Text Element",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 5,
		"field_slug": "text-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"3nrv3psr2zw0000000000\",\"field_input_name\":\"propertyRepeater\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"propertyRepeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"1\",\"repeat_button_text\":\"New Property\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Text Element",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 6,
		"field_slug": "text-element",
		"field_parent_id": 5,
		"field_options": "{\"toggle_state\":true,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"6gfrcf3erew0000000000\",\"field_input_name\":\"property\",\"hook_name\":\"tonicsPageBuilderTextProperty\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"property\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderTextProperty\"}"
	},
	{
		"field_field_name": "Text Element",
		"field_name": "modular_fieldselection",
		"field_id": 7,
		"field_slug": "text-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"2yso4hffabq000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Attributes\",\"inputName\":\"\",\"fieldSlug\":\"html-tag-attributes\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Button Element",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "button-element",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"1w9c67f4oteo000000000\",\"field_input_name\":\"buttonElement\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Button\",\"inputName\":\"buttonElement\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Button Element",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "button-element",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"11d3a7dae82fe7a473c7\",\"field_input_name\":\"rawTextValue\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Button\",\"inputName\":\"rawTextValue\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Button Name\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Button Element",
		"field_name": "modular_rowcolumn",
		"field_id": 3,
		"field_slug": "button-element",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"20v7m9emgops000000000\",\"field_input_name\":\"tonicsPageBuilderSettings\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"tonicsPageBuilderSettings\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Button Element",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 4,
		"field_slug": "button-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"1crz29rgxj4w000000000\",\"field_input_name\":\"propertyRepeater\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"propertyRepeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"0\",\"repeat_button_text\":\"New Property\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Button Element",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 5,
		"field_slug": "button-element",
		"field_parent_id": 4,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"1owh37pv82qo000000000\",\"field_input_name\":\"property\",\"hook_name\":\"tonicsPageBuilderButtonProperty\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"property\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderButtonProperty\"}"
	},
	{
		"field_field_name": "Button Element",
		"field_name": "modular_fieldselection",
		"field_id": 6,
		"field_slug": "button-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"2laqdxf4hvc0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Attributes\",\"inputName\":\"\",\"fieldSlug\":\"html-tag-attributes\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Image Element",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "image-element",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"qd67d68giqo000000000\",\"field_input_name\":\"imageElement\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Image\",\"inputName\":\"imageElement\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Image Element",
		"field_name": "media_media-image",
		"field_id": 2,
		"field_slug": "image-element",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"3dahvet6hx20000000000\",\"field_input_name\":\"tonicsBuilderImage\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Image\",\"inputName\":\"tonicsBuilderImage\",\"imageLink\":\"\",\"featured_image\":\"\",\"defaultImage\":\"\"}"
	},
	{
		"field_field_name": "Image Element",
		"field_name": "modular_rowcolumn",
		"field_id": 3,
		"field_slug": "image-element",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"3y89gzou4k20000000000\",\"field_input_name\":\"tonicsPageBuilderSettings\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"tonicsPageBuilderSettings\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Image Element",
		"field_name": "input_text",
		"field_id": 4,
		"field_slug": "image-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"63fwwqvq8kk0000000000\",\"field_input_name\":\"tonicsBuilderImageExternalURL\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"External URL\",\"inputName\":\"tonicsBuilderImageExternalURL\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Image External URL\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Image Element",
		"field_name": "modular_fieldselection",
		"field_id": 5,
		"field_slug": "image-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"38ol05v0aa20000000000\",\"field_input_name\":\"sources\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Sources\",\"inputName\":\"sources\",\"fieldSlug\":\"image-sources\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Image Element",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 6,
		"field_slug": "image-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"wtkm4kbiksg000000000\",\"field_input_name\":\"propertyRepeater\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"propertyRepeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"0\",\"repeat_button_text\":\"New Property\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Image Element",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 7,
		"field_slug": "image-element",
		"field_parent_id": 6,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"74cbn7pffo40000000000\",\"field_input_name\":\"property\",\"hook_name\":\"tonicsPageBuilderImageProperty\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"property\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderImageProperty\"}"
	},
	{
		"field_field_name": "Image Element",
		"field_name": "modular_fieldselection",
		"field_id": 8,
		"field_slug": "image-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"5lc0ld7demk0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Attributes\",\"inputName\":\"\",\"fieldSlug\":\"html-tag-attributes\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Layout Selector",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "layout-selector",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"28one631mqvw000000000\",\"field_input_name\":\"layout-selector-modular\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Layouts\",\"inputName\":\"layout-selector-modular\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout Selector",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 2,
		"field_slug": "layout-selector",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"6vzftm0ubv00000000000\",\"field_input_name\":\"layout-selector-modular-repeater\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Layout\",\"inputName\":\"layout-selector-modular-repeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"1\",\"toggleable\":\"1\",\"repeat_button_text\":\"Repeat Layout\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout Selector",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 3,
		"field_slug": "layout-selector",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"1lrscuvem128000000000\",\"field_input_name\":\"tonicsPageBuilderLayoutSelector\",\"hook_name\":\"tonicsPageBuilderLayoutSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderLayoutSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderLayoutSelector\"}"
	},
	{
		"field_field_name": "Layout Selector",
		"field_name": "modular_rowcolumn",
		"field_id": 4,
		"field_slug": "layout-selector",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"6s4vddi4uus0000000000\",\"field_input_name\":\"tonicsPageBuilderSettings\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"tonicsPageBuilderSettings\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"1\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout Selector",
		"field_name": "modular_templatehooks",
		"field_id": 5,
		"field_slug": "layout-selector",
		"field_parent_id": 4,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_templatehooks\",\"modular_templatehooks_cell\":\"1\",\"field_slug_unique_hash\":\"4yxxd5135g00000000000\",\"field_input_name\":\"templateHook\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Hook Into\",\"inputName\":\"templateHook\",\"info\":\"<pre style=\\\"all:revert;\\\">\\nNote: This would not work if you are inheriting the page. \\nYou can use page import if you want hook_into to work.\\n</pre>\\n\\n\",\"hideInUserEditForm\":\"0\",\"templateName\":\"Modules::Core/Views/Templates/theme\",\"defaultValue\":\"in_main_content\"}"
	},
	{
		"field_field_name": "Layout Selector",
		"field_name": "input_text",
		"field_id": 6,
		"field_slug": "layout-selector",
		"field_parent_id": 4,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3nyocdalvg40000000000\",\"field_input_name\":\"htmlTagCustomTag\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Custom Tag\",\"inputName\":\"htmlTagCustomTag\",\"textType\":\"text\",\"defaultValue\":\"div\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Custom Tag\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Layout Selector",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 7,
		"field_slug": "layout-selector",
		"field_parent_id": 4,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"3nrv3psr2zw0000000000\",\"field_input_name\":\"propertyRepeater\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"propertyRepeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"1\",\"repeat_button_text\":\"New Property\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout Selector",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 8,
		"field_slug": "layout-selector",
		"field_parent_id": 7,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"6gfrcf3erew0000000000\",\"field_input_name\":\"property\",\"hook_name\":\"tonicsPageBuilderLayoutProperty\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"property\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderLayoutProperty\"}"
	},
	{
		"field_field_name": "Layout Selector",
		"field_name": "modular_fieldselection",
		"field_id": 9,
		"field_slug": "layout-selector",
		"field_parent_id": 4,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"5b4f1t2e6to0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Attributes\",\"inputName\":\"\",\"fieldSlug\":\"html-tag-attributes\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Layout Selector",
		"field_name": "modular_rowcolumn",
		"field_id": 10,
		"field_slug": "layout-selector",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"150ma1kkne0w000000000\",\"field_input_name\":\"tonics-preview-layout-container\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Preview\",\"inputName\":\"tonics-preview-layout-container\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout Selector",
		"field_name": "modular_rowcolumn",
		"field_id": 11,
		"field_slug": "layout-selector",
		"field_parent_id": 10,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"1ikfnkn4tvuo000000000\",\"field_input_name\":\"tonics-preview-layout\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Preview\",\"inputName\":\"tonics-preview-layout\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout Selector",
		"field_name": "input_select",
		"field_id": 12,
		"field_slug": "layout-selector",
		"field_parent_id": 10,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"1qogm46jmfb4000000000\",\"field_input_name\":\"tonics-preview-break-point\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Break Point\",\"inputName\":\"tonics-preview-break-point\",\"selectData\":\"100%:Default (100%),480px:Mobile (480px),768px:Tablet (768px),1024px:Small Desktop (1024px),1400px:Large Desktop (1400px)\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\",\"hookName\":\"\"}"
	},
	{
		"field_field_name": "Layout 3 by 1 (Three-Rows)",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "layout-3-by-1",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"a1r25ceeokg000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Layout Three-Rows\",\"inputName\":\"layout3by1\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout 3 by 1 (Three-Rows)",
		"field_name": "modular_rowcolumn",
		"field_id": 2,
		"field_slug": "layout-3-by-1",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"1gv4mdtre01s000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"\",\"row\":\"3\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout 3 by 1 (Three-Rows)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 3,
		"field_slug": "layout-3-by-1",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"6mnu3cb07uo0000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 3 by 1 (Three-Rows)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 4,
		"field_slug": "layout-3-by-1",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"2\",\"field_slug_unique_hash\":\"1k9vq7ynyzk0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"\"}"
	},
	{
		"field_field_name": "Layout 3 by 1 (Three-Rows)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 5,
		"field_slug": "layout-3-by-1",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"3\",\"field_slug_unique_hash\":\"9mbzcvw6tuc000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 3 by 1 (Three-Rows)",
		"field_name": "modular_fieldselection",
		"field_id": 6,
		"field_slug": "layout-3-by-1",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"1898knki6d28000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"\",\"fieldSlug\":\"layout-settings\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Repeater Element",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 1,
		"field_slug": "repeater-element",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumnrepeater\",\"field_slug_unique_hash\":\"6h3ng2v1w8o0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Repeater\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"1\",\"repeat_button_text\":\"Repeat\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Repeater Element",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 2,
		"field_slug": "repeater-element",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"1nbaf77vt9nk000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Rich Text Element",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "rich-text-element",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"6txychy66rw0000000000\",\"field_input_name\":\"richTextElement\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Rich Text\",\"inputName\":\"richTextElement\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Rich Text Element",
		"field_name": "input_rich-text",
		"field_id": 2,
		"field_slug": "rich-text-element",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_rich-text\",\"input_rich-text_cell\":\"1\",\"field_slug_unique_hash\":\"57ny58tjvr40000000000\",\"field_input_name\":\"richTextValue\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Rich Text\",\"inputName\":\"richTextValue\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"toggleable\":\"0\"}"
	},
	{
		"field_field_name": "Rich Text Element",
		"field_name": "modular_rowcolumn",
		"field_id": 3,
		"field_slug": "rich-text-element",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"5rqame5pwwc0000000000\",\"field_input_name\":\"tonicsPageBuilderSettings\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"tonicsPageBuilderSettings\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Rich Text Element",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 4,
		"field_slug": "rich-text-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"6p5ghdwizs80000000000\",\"field_input_name\":\"propertyRepeater\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"propertyRepeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"0\",\"repeat_button_text\":\"New Property\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Rich Text Element",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 5,
		"field_slug": "rich-text-element",
		"field_parent_id": 4,
		"field_options": "{\"toggle_state\":true,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"3tfebvp5xo8000000000\",\"field_input_name\":\"property\",\"hook_name\":\"tonicsPageBuilderTextProperty\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"property\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderTextProperty\"}"
	},
	{
		"field_field_name": "Rich Text Element",
		"field_name": "modular_fieldselection",
		"field_id": 6,
		"field_slug": "rich-text-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"2dx3g8vh1r8k000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Attributes\",\"inputName\":\"\",\"fieldSlug\":\"html-tag-attributes\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Layout 2 by 1 (Two Rows)",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "layout-2-by-1",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"3ezdn7y9qwa0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Layout Two-Rows\",\"inputName\":\"layout2by1\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout 2 by 1 (Two Rows)",
		"field_name": "modular_rowcolumn",
		"field_id": 2,
		"field_slug": "layout-2-by-1",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"6jk7b09m0o00000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"\",\"row\":\"2\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout 2 by 1 (Two Rows)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 3,
		"field_slug": "layout-2-by-1",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"4hh8fxw7mae0000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 2 by 1 (Two Rows)",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 4,
		"field_slug": "layout-2-by-1",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"2\",\"field_slug_unique_hash\":\"37lgdracqm00000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Layout 2 by 1 (Two Rows)",
		"field_name": "modular_fieldselection",
		"field_id": 5,
		"field_slug": "layout-2-by-1",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"21wnlvvifhc0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"\",\"fieldSlug\":\"layout-settings\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Input Element",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "input-element",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"6zifocdzgbo0000000000\",\"field_input_name\":\"inputElement\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Input\",\"inputName\":\"inputElement\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Input Element",
		"field_name": "input_select",
		"field_id": 2,
		"field_slug": "input-element",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"s63pkjrrz0w000000000\",\"field_input_name\":\"tonicsBuilderInputType\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Input\",\"inputName\":\"tonicsBuilderInputType\",\"selectData\":\"button,checkbox,color,date,datetime-local,email,file,hidden,image,month,number,password,radio,range,reset,search,submit,tel,text,text-area,time,url,week\",\"defaultValue\":\"text\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Input Element",
		"field_name": "modular_rowcolumn",
		"field_id": 3,
		"field_slug": "input-element",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"4h9yzmijasy0000000000\",\"field_input_name\":\"tonicsPageBuilderSettings\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"tonicsPageBuilderSettings\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"height:400px;\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Input Element",
		"field_name": "input_text",
		"field_id": 4,
		"field_slug": "input-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3m0c26q8hlk0000000000\",\"field_input_name\":\"tonicsBuilderDefaultValue\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Default Value\",\"inputName\":\"tonicsBuilderDefaultValue\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Default Value\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Input Element",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 5,
		"field_slug": "input-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"3nrv3psr2zw0000000000\",\"field_input_name\":\"propertyRepeater\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"propertyRepeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"1\",\"repeat_button_text\":\"New Property\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Input Element",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 6,
		"field_slug": "input-element",
		"field_parent_id": 5,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"6gfrcf3erew0000000000\",\"field_input_name\":\"property\",\"hook_name\":\"tonicsPageBuilderTextProperty\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"property\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderTextProperty\"}"
	},
	{
		"field_field_name": "Input Element",
		"field_name": "modular_fieldselection",
		"field_id": 7,
		"field_slug": "input-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"1r9gdnryfozk000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Attributes\",\"inputName\":\"\",\"fieldSlug\":\"html-tag-attributes\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Page Inheritance",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "page-inheritance",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"t3vd198z0sg000000000\",\"field_input_name\":\"pageInheritance\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Page Inheritance\",\"inputName\":\"pageInheritance\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Page Inheritance",
		"field_name": "interface_table",
		"field_id": 2,
		"field_slug": "page-inheritance",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"interface_table\",\"interface_table_cell\":\"1\",\"field_slug_unique_hash\":\"4mdaylhi5ks0000000000\",\"field_input_name\":\"pages\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Inherit From\",\"inputName\":\"pages-inheritance-input\",\"tableName\":\"pages\",\"orderBy\":\"page_title\",\"colNameDisplay\":\"page_title\",\"colNameValue\":\"page_slug\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"1\"}"
	},
	{
		"field_field_name": "Style Color",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "style-color",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"3ya5pdj6zwu0000000000\",\"field_input_name\":\"colorStylesContainer\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Color Styles\",\"inputName\":\"colorStylesContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"1\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Style Color",
		"field_name": "input_select",
		"field_id": 2,
		"field_slug": "style-color",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"4wwk6okj1pk0000000000\",\"field_input_name\":\"tonicsBuilderStyleColorType\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Color Type 🔧\",\"inputName\":\"tonicsBuilderStyleColorType\",\"selectData\":\"Color,Background,Link,Link Hover,Heading\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Style Color",
		"field_name": "input_choices",
		"field_id": 3,
		"field_slug": "style-color",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"input_choices\",\"input_choices_cell\":\"1\",\"field_slug_unique_hash\":\"2e4j3m3xnbbw000000000\",\"field_input_name\":\"tonicsBuilderStyleColor\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Pure\",\"inputName\":\"tonicsBuilderStyleColor\",\"choiceType\":\"color\",\"choices\":\"#ffffff:color-light,\\n#000000:color-dark\",\"defaultValue\":\"\"}"
	},
	{
		"field_field_name": "Style Color",
		"field_name": "input_choices",
		"field_id": 4,
		"field_slug": "style-color",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"input_choices\",\"input_choices_cell\":\"1\",\"field_slug_unique_hash\":\"18ux1ysgbszk000000000\",\"field_input_name\":\"tonicsBuilderStyleColor\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Red\",\"inputName\":\"tonicsBuilderStyleColor\",\"choiceType\":\"color\",\"choices\":\"#1c0d06:color-red-950,\\n#30130a:color-red-900,\\n#45150c:color-red-850,\\n#5c160d:color-red-800,\\n#72170f:color-red-750,\\n#861d13:color-red-700,\\n#9b2318:color-red-650,\\n\\n#af291d:color-red-600,\\n#c52f21:color-red-550,\\n#d93526:color-red-500,\\n#ee402e:color-red-450,\\n#f06048:color-red-400,\\n\\n#f17961:color-red-350,\\n#f38f79:color-red-300,\\n#f5a390:color-red-250,\\n#f5b7a8:color-red-200,\\n#f6cabf:color-red-150,\\n#f8dcd6:color-red-100,\\n#faeeeb:color-red-50,\\n#c52f21:color-red\",\"defaultValue\":\"\"}"
	},
	{
		"field_field_name": "Style Color",
		"field_name": "input_choices",
		"field_id": 5,
		"field_slug": "style-color",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"input_choices\",\"input_choices_cell\":\"1\",\"field_slug_unique_hash\":\"6dodcyujvsk0000000000\",\"field_input_name\":\"tonicsBuilderStyleColor\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Blue\",\"inputName\":\"tonicsBuilderStyleColor\",\"choiceType\":\"color\",\"choices\":\"#080f2d:color-blue-950,\\n#0c1a41:color-blue-900,\\n#0e2358:color-blue-850,\\n#0f2d70:color-blue-800,\\n#0f3888:color-blue-750,\\n#1343a0:color-blue-700,\\n#184eb8:color-blue-650,\\n#1d59d0:color-blue-600,\\n#2060df:color-blue-550,\\n#3c71f7:color-blue-500,\\n#5c7ef8:color-blue-450,\\n#748bf8:color-blue-400,\\n#8999f9:color-blue-350,\\n#9ca7fa:color-blue-300,\\n#aeb5fb:color-blue-250,\\n#bfc3fa:color-blue-200,\\n#d0d2fa:color-blue-150,\\n#e0e1fa:color-blue-100,\\n#f0f0fb:color-blue-50,\\n#2060df:color-blue\",\"defaultValue\":\"\"}"
	},
	{
		"field_field_name": "Style Color",
		"field_name": "input_choices",
		"field_id": 6,
		"field_slug": "style-color",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"input_choices\",\"input_choices_cell\":\"1\",\"field_slug_unique_hash\":\"5v23ek26xgw0000000000\",\"field_input_name\":\"tonicsBuilderStyleColor\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Green\",\"inputName\":\"tonicsBuilderStyleColor\",\"choiceType\":\"color\",\"choices\":\"#0b1305:color-green-950,\\n#131f07:color-green-900,\\n#152b07:color-green-850,\\n#173806:color-green-800,\\n#1a4405:color-green-750,\\n#205107:color-green-700,\\n#265e09:color-green-650,\\n#2c6c0c:color-green-600,\\n#33790f:color-green-550,\\n#398712:color-green-500,\\n#409614:color-green-450,\\n#47a417:color-green-400,\\n#4eb31b:color-green-350,\\n#55c21e:color-green-300,\\n#5dd121:color-green-250,\\n#62d926:color-green-200,\\n#77ef3d:color-green-150,\\n#95fb62:color-green-100,\\n#d7fbc1:color-green-50,\\n#398712:color-green\",\"defaultValue\":\"\"}"
	},
	{
		"field_field_name": "Style Color",
		"field_name": "input_choices",
		"field_id": 7,
		"field_slug": "style-color",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"input_choices\",\"input_choices_cell\":\"1\",\"field_slug_unique_hash\":\"3gbf774f9sm0000000000\",\"field_input_name\":\"tonicsBuilderStyleColor\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Lime\",\"inputName\":\"tonicsBuilderStyleColor\",\"choiceType\":\"color\",\"choices\":\"#101203:color-lime-950,\\n#191d03:color-lime-900,\\n#202902:color-lime-850,\\n#273500:color-lime-800,\\n#304100:color-lime-750,\\n#394d00:color-lime-700,\\n#435a00:color-lime-650,\\n#4d6600:color-lime-600,\\n#577400:color-lime-550,\\n#628100:color-lime-500,\\n#6c8f00:color-lime-450,\\n#779c00:color-lime-400,\\n#82ab00:color-lime-350,\\n#8eb901:color-lime-300,\\n#99c801:color-lime-250,\\n#a5d601:color-lime-200,\\n#b2e51a:color-lime-150,\\n#c1f335:color-lime-100,\\n#defc85:color-lime-50,\\n#a5d601:color-lime\",\"defaultValue\":\"\"}"
	},
	{
		"field_field_name": "Style Color",
		"field_name": "input_choices",
		"field_id": 8,
		"field_slug": "style-color",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"input_choices\",\"input_choices_cell\":\"1\",\"field_slug_unique_hash\":\"2lzkh21x3aw0000000000\",\"field_input_name\":\"tonicsBuilderStyleColor\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Yellow\",\"inputName\":\"tonicsBuilderStyleColor\",\"choiceType\":\"color\",\"choices\":\"#141103:color-yellow-950,\\n#1f1c02:color-yellow-900,\\n#2b2600:color-yellow-850,\\n#363100:color-yellow-800,\\n#423c00:color-yellow-750,\\n#4e4700:color-yellow-700,\\n#5b5300:color-yellow-650,\\n#685f00:color-yellow-600,\\n#756b00:color-yellow-550,\\n#827800:color-yellow-500,\\n#908501:color-yellow-450,\\n#9e9200:color-yellow-400,\\n#ad9f00:color-yellow-350,\\n#bbac00:color-yellow-300,\\n#caba01:color-yellow-250,\\n#d9c800:color-yellow-200,\\n#e8d600:color-yellow-150,\\n#f2df0d:color-yellow-100,\\n#fdf1b4:color-yellow-50,\\n#f2df0d:color-yellow\",\"defaultValue\":\"\"}"
	},
	{
		"field_field_name": "Style Color",
		"field_name": "input_choices",
		"field_id": 9,
		"field_slug": "style-color",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"input_choices\",\"input_choices_cell\":\"1\",\"field_slug_unique_hash\":\"6lvw6xclevw0000000000\",\"field_input_name\":\"tonicsBuilderStyleColor\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Amber\",\"inputName\":\"tonicsBuilderStyleColor\",\"choiceType\":\"color\",\"choices\":\"#161003:color-amber-950,\\n#231a03:color-amber-900,\\n#312302:color-amber-850,\\n#3f2d00:color-amber-800,\\n#4d3700:color-amber-750,\\n#5b4200:color-amber-700,\\n#694d00:color-amber-650,\\n#785800:color-amber-600,\\n#876400:color-amber-550,\\n#977000:color-amber-500,\\n#a77c00:color-amber-450,\\n#b78800:color-amber-400,\\n#c79400:color-amber-350,\\n#d8a100:color-amber-300,\\n#e8ae01:color-amber-250,\\n#ffbf00:color-amber-200,\\n#fecc63:color-amber-150,\\n#fddea6:color-amber-100,\\n#fcefd9:color-amber-50,\\n#ffbf00:color-amber\",\"defaultValue\":\"\"}"
	},
	{
		"field_field_name": "Style Color",
		"field_name": "input_choices",
		"field_id": 10,
		"field_slug": "style-color",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"input_choices\",\"input_choices_cell\":\"1\",\"field_slug_unique_hash\":\"4kts3ppodi80000000000\",\"field_input_name\":\"tonicsBuilderStyleColor\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Pumpkin\",\"inputName\":\"tonicsBuilderStyleColor\",\"choiceType\":\"color\",\"choices\":\"#180f04:color-pumpkin-950,\\n#271805:color-pumpkin-900,\\n#372004:color-pumpkin-850,\\n#482802:color-pumpkin-800,\\n#593100:color-pumpkin-750,\\n#693a00:color-pumpkin-700,\\n#7a4400:color-pumpkin-650,\\n#8b4f00:color-pumpkin-600,\\n#9c5900:color-pumpkin-550,\\n#ad6400:color-pumpkin-500,\\n#bf6e00:color-pumpkin-450,\\n#d27a01:color-pumpkin-400,\\n#e48500:color-pumpkin-350,\\n#ff9500:color-pumpkin-300,\\n#ffa23a:color-pumpkin-250,\\n#feb670:color-pumpkin-200,\\n#fcca9b:color-pumpkin-150,\\n#fcdcc1:color-pumpkin-100,\\n#fceee3:color-pumpkin-50,\\n#ff9500:color-pumpkin\",\"defaultValue\":\"\"}"
	},
	{
		"field_field_name": "Style Color",
		"field_name": "input_choices",
		"field_id": 11,
		"field_slug": "style-color",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"input_choices\",\"input_choices_cell\":\"1\",\"field_slug_unique_hash\":\"32wm87erw300000000000\",\"field_input_name\":\"tonicsBuilderStyleColor\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Orange\",\"inputName\":\"tonicsBuilderStyleColor\",\"choiceType\":\"color\",\"choices\":\"#1b0d06:color-orange-950,\\n#2d1509:color-orange-900,\\n#411a0a:color-orange-850,\\n#561e0a:color-orange-800,\\n#6b220a:color-orange-750,\\n#7f270b:color-orange-700,\\n#942d0d:color-orange-650,\\n#a83410:color-orange-600,\\n#bd3c13:color-orange-550,\\n#d24317:color-orange-500,\\n#e74b1a:color-orange-450,\\n#f45d2c:color-orange-400,\\n#f56b3d:color-orange-350,\\n#f68e68:color-orange-300,\\n#f8a283:color-orange-250,\\n#f8b79f:color-orange-200,\\n#f8cab9:color-orange-150,\\n#f9dcd2:color-orange-100,\\n#faeeea:color-orange-50,\\n#d24317:color-orange\",\"defaultValue\":\"\"}"
	},
	{
		"field_field_name": "Style Color",
		"field_name": "input_choices",
		"field_id": 12,
		"field_slug": "style-color",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"input_choices\",\"input_choices_cell\":\"1\",\"field_slug_unique_hash\":\"2xhwd9l0x2i0000000000\",\"field_input_name\":\"tonicsBuilderStyleColor\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Grey\",\"inputName\":\"tonicsBuilderStyleColor\",\"choiceType\":\"color\",\"choices\":\"#111111:color-grey-950,\\n#1b1b1b:color-grey-900,\\n#262626:color-grey-850,\\n#303030:color-grey-800,\\n#3b3b3b:color-grey-750,\\n#474747:color-grey-700,\\n#525252:color-grey-650,\\n#5e5e5e:color-grey-600,\\n#6a6a6a:color-grey-550,\\n#777777:color-grey-500,\\n#808080:color-grey-450,\\n#919191:color-grey-400,\\n#9e9e9e:color-grey-350,\\n#ababab:color-grey-300,\\n#b9b9b9:color-grey-250,\\n#c6c6c6:color-grey-200,\\n#d4d4d4:color-grey-150,\\n#e2e2e2:color-grey-100,\\n#f1f1f1:color-grey-50,\\n#ababab:color-grey\",\"defaultValue\":\"\"}"
	},
	{
		"field_field_name": "Style Color",
		"field_name": "input_choices",
		"field_id": 13,
		"field_slug": "style-color",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"input_choices\",\"input_choices_cell\":\"1\",\"field_slug_unique_hash\":\"52mqbud4ljg0000000000\",\"field_input_name\":\"tonicsBuilderStyleColor\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Zinc\",\"inputName\":\"tonicsBuilderStyleColor\",\"choiceType\":\"color\",\"choices\":\"#0f1114:color-zinc-950,\\n#191c20:color-zinc-900,\\n#23262c:color-zinc-850,\\n#2d3138:color-zinc-800,\\n#373c44:color-zinc-750,\\n#424751:color-zinc-700,\\n#4d535e:color-zinc-650,\\n#5c6370:color-zinc-600,\\n#646b79:color-zinc-550,\\n#6f7887:color-zinc-500,\\n#7b8495:color-zinc-450,\\n#8891a4:color-zinc-400,\\n#969eaf:color-zinc-350,\\n#a4acba:color-zinc-300,\\n#b3b9c5:color-zinc-250,\\n#c2c7d0:color-zinc-200,\\n#d1d5db:color-zinc-150,\\n#e0e3e7:color-zinc-100,\\n#f0f1f3:color-zinc-50,\\n#646b79:color-zinc\",\"defaultValue\":\"\"}"
	},
	{
		"field_field_name": "Style Spacing",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "style-spacing",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"45fdlbcx9tw0000000000\",\"field_input_name\":\"spacingContainer\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Spacing\",\"inputName\":\"spacingContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Style Spacing",
		"field_name": "input_select",
		"field_id": 2,
		"field_slug": "style-spacing",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"6z31jazh4og0000000000\",\"field_input_name\":\"padding\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Padding\",\"inputName\":\"tonicsBuilderStylePadding\",\"selectData\":\"p-xs:Extra Small Padding, p-sm:Small Padding, p-md:Medium Padding, p-lg:Large Padding, p-xl:Extra Large Padding, p-xxl:Extra Extra Large Padding,\\n\\n :,\\n\\npt-xs: Extra Small Padding Top, pb-xs: Extra Small Padding Bottom, pl-xs: Extra Small Padding Left, pr-xs: Extra Small Padding Right,\\n\\n  : ,\\n\\npt-sm: Small Padding Top, pb-sm: Small Padding Bottom, pl-sm: Small Padding Left, pr-sm: Small Padding Right,\\n\\n   : ,\\n\\npt-md: Medium Padding Top, pb-md: Medium Padding Bottom, pl-md: Medium Padding Left, pr-md: Medium Padding Right,\\n\\n    : ,\\n\\npt-lg: Large Padding Top, pb-lg: Large Padding Bottom, pl-lg: Large Padding Left, pr-lg: Large Padding Right,\\n\\n       : ,\\n\\npt-xl: Extra Large Padding Top, pb-xl: Extra Large Padding Bottom, pl-xl: Extra Large Padding Left, pr-xl: Extra Large Padding Right,\\n\\n         : ,\\n\\npt-xxl: Extra Extra Large Padding Top, pb-xxl: Extra Extra Large Padding Bottom, pl-xxl: Extra Extra Large Padding Left, pr-xxl: Extra Extra Large Padding Right\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"1\"}"
	},
	{
		"field_field_name": "Style Spacing",
		"field_name": "input_select",
		"field_id": 3,
		"field_slug": "style-spacing",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"5vdsttbkfyk0000000000\",\"field_input_name\":\"margin\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Margin\",\"inputName\":\"tonicsBuilderStyleMargin\",\"selectData\":\"m-xs: Extra Small Margin, m-sm: Small Margin, m-md: Medium Margin, m-lg: Large Margin, m-xl: Extra Large Margin, m-xxl: Extra Extra Large Margin,\\n\\n:,\\n\\nmt-xs: Extra Small Margin Top, mb-xs: Extra Small Margin Bottom, ml-xs: Extra Small Margin Left, mr-xs: Extra Small Margin Right,\\n\\n :,\\n\\nmt-sm: Small Margin Top, mb-sm: Small Margin Bottom, ml-sm: Small Margin Left, mr-sm: Small Margin Right,\\n\\n  :,\\n\\nmt-md: Medium Margin Top, mb-md: Medium Margin Bottom, ml-md: \\nMedium Margin Left, mr-md: Medium Margin Right,\\n\\n   :,\\n\\nmt-lg: Large Margin Top, mb-lg: Large Margin Bottom, ml-lg: Large Margin Left, mr-lg: Large Margin Right,\\n\\n    :,\\n\\nmt-xl: Extra Large Margin Top, mb-xl: Extra Large Margin Bottom, ml-xl: Extra Large Margin Left, mr-xl: Extra Large Margin Right,\\n\\n     :,\\n\\nmt-xxl: Extra Extra Large Margin Top, mb-xxl: Extra Extra Large Margin Bottom, ml-xxl: Extra Extra Large Margin Left, mr-xxl: Extra Extra Large Margin Right\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"1\"}"
	},
	{
		"field_field_name": "Display Flex",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "display-flex",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4nn35yg22m0000000000\",\"field_input_name\":\"display-flex\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Flex Options\",\"inputName\":\"display-flex\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Display Flex",
		"field_name": "input_select",
		"field_id": 2,
		"field_slug": "display-flex",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"47zd3ogd9460000000000\",\"field_input_name\":\"display-flex\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Display\",\"inputName\":\"display-flex\",\"selectData\":\":,flex,inline-flex\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Display Flex",
		"field_name": "input_select",
		"field_id": 3,
		"field_slug": "display-flex",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"6a2kxi13oj80000000000\",\"field_input_name\":\"flex-wrap\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Flex Wrap\",\"inputName\":\"flex-wrap\",\"selectData\":\":,nowrap,wrap,wrap-reverse\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Display Flex",
		"field_name": "input_select",
		"field_id": 4,
		"field_slug": "display-flex",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"5cyrpautmhg0000000000\",\"field_input_name\":\"flex-direction\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Flex Direction\",\"inputName\":\"flex-direction\",\"selectData\":\":,row,row-reverse,column,column-reverse\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Display Flex",
		"field_name": "input_select",
		"field_id": 5,
		"field_slug": "display-flex",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"6svewqyxn680000000000\",\"field_input_name\":\"justify-content\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Justify Content\",\"inputName\":\"justify-content\",\"selectData\":\":,flex-start,flex-end,center,space-between,space-around,space-evenly\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Display Flex",
		"field_name": "input_select",
		"field_id": 6,
		"field_slug": "display-flex",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"5o7i12phlmg0000000000\",\"field_input_name\":\"align-items\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Align Items\",\"inputName\":\"align-items\",\"selectData\":\":,stretch,flex-start,flex-end,center,baseline\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Display Flex",
		"field_name": "input_select",
		"field_id": 7,
		"field_slug": "display-flex",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"j90yr5ek99s000000000\",\"field_input_name\":\"align-content\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Align Content\",\"inputName\":\"align-content\",\"selectData\":\":,stretch,flex-start,flex-end,center,space-between,space-around\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Display Flex",
		"field_name": "input_select",
		"field_id": 8,
		"field_slug": "display-flex",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"2u23jloeg2w0000000000\",\"field_input_name\":\"align-self\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Align Self\",\"inputName\":\"align-self\",\"selectData\":\":,auto,flex-start,flex-end,center,baseline,stretch\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Display Flex",
		"field_name": "input_text",
		"field_id": 9,
		"field_slug": "display-flex",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5ciy4sh841g0000000000\",\"field_input_name\":\"gap\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Gap\",\"inputName\":\"gap\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Flex Gap\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Display Flex",
		"field_name": "input_text",
		"field_id": 10,
		"field_slug": "display-flex",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3nc81fo24nm0000000000\",\"field_input_name\":\"order\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Order\",\"inputName\":\"order\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Order of the flex item\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Display Flex",
		"field_name": "input_text",
		"field_id": 11,
		"field_slug": "display-flex",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5oe8k21jzq80000000000\",\"field_input_name\":\"flex\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Flex\",\"inputName\":\"flex\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"e.g: 1 1 auto\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "HTML Tag",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "html-tag",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"xfwbil862k0000000000\",\"field_input_name\":\"htmlTagContainer\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"HTML Tag\",\"inputName\":\"htmlTagContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "HTML Tag",
		"field_name": "input_select",
		"field_id": 2,
		"field_slug": "html-tag",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"2bs0w23qyhs0000000000\",\"field_input_name\":\"htmlTagType\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Tag Type\",\"inputName\":\"htmlTagType\",\"selectData\":\":,a,address,article,aside,div,form,figure,footer,form,header,ol,ul,li,nav,section\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\",\"hookName\":\"\"}"
	},
	{
		"field_field_name": "HTML Tag",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "html-tag",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"7em249j4zko0000000000\",\"field_input_name\":\"htmlTagCustomTag\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Custom Tag\",\"inputName\":\"htmlTagCustomTag\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Custom Tag if Tag Type is not enough\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "HTML Tag",
		"field_name": "input_text",
		"field_id": 4,
		"field_slug": "html-tag",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2wjttdag1rk0000000000\",\"field_input_name\":\"htnlInlineClass\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Inline Class\",\"inputName\":\"htmlInlineClass\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"By default, a class is generated for each element, override it with yours here, e.g, class-1, class-2\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"class-1\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "HTML Tag",
		"field_name": "modular_fieldselection",
		"field_id": 5,
		"field_slug": "html-tag",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"17mo0ijx9thc000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Attributes\",\"inputName\":\"\",\"fieldSlug\":\"html-tag-attributes\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Display Grid",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "display-grid",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"3upn0akexpu0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Grid Options\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Display Grid",
		"field_name": "input_select",
		"field_id": 2,
		"field_slug": "display-grid",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"4nyp5w5nzdo0000000000\",\"field_input_name\":\"display-grid\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Display\",\"inputName\":\"display-grid\",\"selectData\":\":,grid,inline-grid\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Display Grid",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "display-grid",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"43sqf5x9jdk0000000000\",\"field_input_name\":\"grid-template-areas\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Template Areas\",\"inputName\":\"grid-template-areas\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"info\":\"Defines grid areas by assigning names to them e.g: \\n\\n\\\"header header header\\\" \\n\\\"main main sidebar\\\" \\n\\\"footer footer footer\\\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Template Area\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Display Grid",
		"field_name": "input_select",
		"field_id": 4,
		"field_slug": "display-grid",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"4j07xfuo3ds0000000000\",\"field_input_name\":\"grid-auto-flow\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Auto Flow\",\"inputName\":\"grid-auto-flow\",\"selectData\":\":,row,column,dense\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Display Grid",
		"field_name": "input_text",
		"field_id": 5,
		"field_slug": "display-grid",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5d3zbngnjrs0000000000\",\"field_input_name\":\"gap\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Gap\",\"inputName\":\"gap\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Example: 10px 15px\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Display Grid",
		"field_name": "modular_rowcolumn",
		"field_id": 6,
		"field_slug": "display-grid",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"5tgx93ybx500000000000\",\"field_input_name\":\"alignmentContainer\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Alignment\",\"inputName\":\"alignmentContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Display Grid",
		"field_name": "input_select",
		"field_id": 7,
		"field_slug": "display-grid",
		"field_parent_id": 6,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"58arnhkyzok0000000000\",\"field_input_name\":\"justify-items\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Justify Items\",\"inputName\":\"justify-items\",\"selectData\":\":,start,end,center,stretch\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Display Grid",
		"field_name": "input_select",
		"field_id": 8,
		"field_slug": "display-grid",
		"field_parent_id": 6,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"2r25akhlw8a0000000000\",\"field_input_name\":\"align-items\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Align Items\",\"inputName\":\"align-items\",\"selectData\":\":,start,end,center,stretch\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Display Grid",
		"field_name": "input_select",
		"field_id": 9,
		"field_slug": "display-grid",
		"field_parent_id": 6,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"5btkjib85e8000000000\",\"field_input_name\":\"justify-content\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Justify Content\",\"inputName\":\"justify-content\",\"selectData\":\":,start,end,center,stretch,space-around,space-between,space-evenly\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Display Grid",
		"field_name": "input_select",
		"field_id": 10,
		"field_slug": "display-grid",
		"field_parent_id": 6,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"14s65sc1zc2o000000000\",\"field_input_name\":\"align-content\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Align Content\",\"inputName\":\"align-content\",\"selectData\":\":,start,end,center,stretch,space-around,space-between,space-evenly\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Display Grid",
		"field_name": "modular_rowcolumn",
		"field_id": 11,
		"field_slug": "display-grid",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"25wc2pe2xko0000000000\",\"field_input_name\":\"gridItemContainer\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Item\",\"inputName\":\"gridItemContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Display Grid",
		"field_name": "input_text",
		"field_id": 12,
		"field_slug": "display-grid",
		"field_parent_id": 11,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"58p1h5jkpa80000000000\",\"field_input_name\":\"grid-area\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Grid Area\",\"inputName\":\"grid-area\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Grid Area Name, e.g header\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Display Grid",
		"field_name": "input_text",
		"field_id": 13,
		"field_slug": "display-grid",
		"field_parent_id": 11,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"hpr0ce6232o000000000\",\"field_input_name\":\"grid-column\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Grid Column\",\"inputName\":\"grid-column\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"Specifies a grid item’s start/end position within the grid column\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"e.g: 1 / span 2\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Display Grid",
		"field_name": "input_text",
		"field_id": 14,
		"field_slug": "display-grid",
		"field_parent_id": 11,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6sxepopffaw0000000000\",\"field_input_name\":\"grid-row\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Grid Row\",\"inputName\":\"grid-row\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"Specifies a grid item’s start/end position within the grid row\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"1 / span 2\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Display Grid",
		"field_name": "input_text",
		"field_id": 15,
		"field_slug": "display-grid",
		"field_parent_id": 11,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"17cup0j56w2k000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Grid Template Rows\",\"inputName\":\"grid-template-rows\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"Explicitly create a grid rows\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"1fr 1fr\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Display Grid",
		"field_name": "input_text",
		"field_id": 16,
		"field_slug": "display-grid",
		"field_parent_id": 11,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"21rpn3ykkzhc000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Grid Template Columns\",\"inputName\":\"grid-template-columns\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"Explicitly create a grid columns\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"1fr 1fr\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Typography",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "typography",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"5e2yk8xrgqw0000000000\",\"field_input_name\":\"typographyOptions\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"TypoGraphy Options\",\"inputName\":\"typographyOptions\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Typography",
		"field_name": "modular_rowcolumn",
		"field_id": 2,
		"field_slug": "typography",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"55k5usx6qqs0000000000\",\"field_input_name\":\"textContainer\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Text\",\"inputName\":\"textContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Typography",
		"field_name": "input_select",
		"field_id": 3,
		"field_slug": "typography",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"2lpps16612s0000000000\",\"field_input_name\":\"text-transform\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Text Transform\",\"inputName\":\"text-transform\",\"selectData\":\":,capitalize,lowercase,uppercase\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Typography",
		"field_name": "input_select",
		"field_id": 4,
		"field_slug": "typography",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"3w3embs881a0000000000\",\"field_input_name\":\"text-align\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Text Align\",\"inputName\":\"text-align\",\"selectData\":\":,Center,End,Justify,Left,Right,Start\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Typography",
		"field_name": "input_select",
		"field_id": 5,
		"field_slug": "typography",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"6uc3lipbkmk0000000000\",\"field_input_name\":\"text-decoration\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Text Decoration\",\"inputName\":\"text-decoration\",\"selectData\":\":,underline,strikethrough,overline,none\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Typography",
		"field_name": "modular_rowcolumn",
		"field_id": 6,
		"field_slug": "typography",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"6cgt0oxehqo0000000000\",\"field_input_name\":\"fontContainer\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Font\",\"inputName\":\"fontContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Typography",
		"field_name": "input_select",
		"field_id": 7,
		"field_slug": "typography",
		"field_parent_id": 6,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"23v3e36rj82o000000000\",\"field_input_name\":\"font-style\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Font Style\",\"inputName\":\"font-style\",\"selectData\":\":,italic,normal,oblique\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Typography",
		"field_name": "input_select",
		"field_id": 8,
		"field_slug": "typography",
		"field_parent_id": 6,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"6ja91jtg7ug0000000000\",\"field_input_name\":\"font-weight\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Font Weight\",\"inputName\":\"font-weight\",\"selectData\":\":,100:Thin,200:Extra-Light,300:Light,400:Normal,500:Medium,600:Semi-Bold,700:Bold,800:Extra-Bold,900:Ultra-Bold\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Typography",
		"field_name": "input_text",
		"field_id": 9,
		"field_slug": "typography",
		"field_parent_id": 6,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"48twzy5jtts0000000000\",\"field_input_name\":\"font-size\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Font Size\",\"inputName\":\"font-size\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"e.g: 20px\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Typography",
		"field_name": "modular_rowcolumn",
		"field_id": 10,
		"field_slug": "typography",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"2fbd6bw0dx0k000000000\",\"field_input_name\":\"othersContainer\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Others\",\"inputName\":\"othersContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Typography",
		"field_name": "input_text",
		"field_id": 11,
		"field_slug": "typography",
		"field_parent_id": 10,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5wyzctuoeqk0000000000\",\"field_input_name\":\"line-height\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Line Height\",\"inputName\":\"line-height\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"e.g 15\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Container Queries",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "container-queries",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"2ya9i013bby0000000000\",\"field_input_name\":\"queryOption\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Query Option\",\"inputName\":\"queryOption\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Container Queries",
		"field_name": "input_select",
		"field_id": 2,
		"field_slug": "container-queries",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"ccbs1myukzk000000000\",\"field_input_name\":\"containerSize\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Container Size\",\"inputName\":\"containerSize\",\"selectData\":\":,420px:Mobile (420px),720px:Tablet (720px),1024px:Small Desktop (1024px),1200px:Medium Desktop (1200px),1440px:Large Desktop (1400px),1920px:Extra Large Desktop (1920px)\",\"defaultValue\":\"\",\"info\":\"For container query to work, please, ensure the element you are using the container on has the container-type property\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\",\"hookName\":\"\"}"
	},
	{
		"field_field_name": "Container Queries",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "container-queries",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1kq78b1w9lcw000000000\",\"field_input_name\":\"container-name\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Container Name\",\"inputName\":\"container-name\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Container Name, can be empty\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Container Queries",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 4,
		"field_slug": "container-queries",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"3nrv3psr2zw0000000000\",\"field_input_name\":\"propertyRepeater\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"propertyRepeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"1\",\"toggleable\":\"0\",\"repeat_button_text\":\"New Property\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Container Queries",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 5,
		"field_slug": "container-queries",
		"field_parent_id": 4,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"6gfrcf3erew0000000000\",\"field_input_name\":\"property\",\"hook_name\":\"tonicsPageBuilderLayoutProperty\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"property\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderLayoutProperty\"}"
	},
	{
		"field_field_name": "Container Queries",
		"field_name": "input_text",
		"field_id": 6,
		"field_slug": "container-queries",
		"field_parent_id": 4,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4zcqv5nml5s0000000000\",\"field_input_name\":\"classTarget\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Class Target\",\"inputName\":\"classTarget\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"This should be a query target, e.g: .class-name, #id-name, [data-attributes],etc.\\nIf no class target is set, it uses the element generated class\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Class Target, e.g: .className\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"1\"}"
	},
	{
		"field_field_name": "Image Sources",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "image-sources",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4epc5kkagzs0000000000\",\"field_input_name\":\"imageOptions\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Image Option\",\"inputName\":\"imageOptions\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Image Sources",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "image-sources",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5lo4omfc0e40000000000\",\"field_input_name\":\"alt\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Alt\",\"inputName\":\"alt\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Image Alt Text\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Image Sources",
		"field_name": "input_select",
		"field_id": 3,
		"field_slug": "image-sources",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"1kpvhleruh0g000000000\",\"field_input_name\":\"object-fit\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Object Fit\",\"inputName\":\"object-fit\",\"selectData\":\":,contain,cover,fill,scale-down\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Image Sources",
		"field_name": "input_select",
		"field_id": 4,
		"field_slug": "image-sources",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"3jmnbspf29s0000000000\",\"field_input_name\":\"loading\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Loading\",\"inputName\":\"loading\",\"selectData\":\":,lazy,eager\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Image Sources",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 5,
		"field_slug": "image-sources",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"3nzkz6u6p080000000000\",\"field_input_name\":\"sourcesContainer\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Sources\",\"inputName\":\"sourcesContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"1\",\"toggleable\":\"0\",\"repeat_button_text\":\"Repeat Source\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Image Sources",
		"field_name": "media_media-image",
		"field_id": 6,
		"field_slug": "image-sources",
		"field_parent_id": 5,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"3e9hbmi5n200000000000\",\"field_input_name\":\"tonicsBuilderImageSourcesImage\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Image\",\"inputName\":\"tonicsBuilderImageSourcesImage\",\"imageLink\":\"\",\"featured_image\":\"\",\"defaultImage\":\"\"}"
	},
	{
		"field_field_name": "Image Sources",
		"field_name": "input_text",
		"field_id": 7,
		"field_slug": "image-sources",
		"field_parent_id": 5,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"7e2vwt6xkjk0000000000\",\"field_input_name\":\"tonicsBuilderImageSourcesExternalURL\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"External URL\",\"inputName\":\"tonicsBuilderImageSourcesExternalURL\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Image External URL\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Image Sources",
		"field_name": "input_select",
		"field_id": 8,
		"field_slug": "image-sources",
		"field_parent_id": 5,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"5v1pe0af5lc0000000000\",\"field_input_name\":\"tonicsBuilderImageSourcesBreakPoint\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Break Point\",\"inputName\":\"tonicsBuilderImageSourcesBreakPoint\",\"selectData\":\":,480px:Mobile (480px),768px:Tablet (768px),1024px:Small Desktop (1024px),1400px:Large Desktop (1400px)\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Image Sources",
		"field_name": "input_text",
		"field_id": 9,
		"field_slug": "image-sources",
		"field_parent_id": 5,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3pbwk0x73xg0000000000\",\"field_input_name\":\"tonicsBuilderImageSourcesCustomBreakPoint\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Custom Break Point\",\"inputName\":\"tonicsBuilderImageSourcesCustomBreakPoint\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"enter custom break point here\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Section Element",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 1,
		"field_slug": "section-element",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"field_slug_unique_hash\":\"1uirdpjt8b0g000000000\",\"field_input_name\":\"section-element-repeater\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Section\",\"inputName\":\"section-element-repeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"1\",\"toggleable\":\"0\",\"repeat_button_text\":\"Repeat Section\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Section Element",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 2,
		"field_slug": "section-element",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"2382o72h9c3k000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Section Element",
		"field_name": "modular_rowcolumn",
		"field_id": 3,
		"field_slug": "section-element",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"6upbrshziio0000000000\",\"field_input_name\":\"tonicsPageBuilderSettings\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"tonicsPageBuilderSettings\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Section Element",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 4,
		"field_slug": "section-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"4q02upgrtpk0000000000\",\"field_input_name\":\"propertyRepeater\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"propertyRepeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"0\",\"repeat_button_text\":\"Repeat Property\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Section Element",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 5,
		"field_slug": "section-element",
		"field_parent_id": 4,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"3sb2ilcqxy40000000000\",\"field_input_name\":\"property\",\"hook_name\":\"tonicsPageBuilderLayoutProperty\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"property\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderLayoutProperty\"}"
	},
	{
		"field_field_name": "Section Element",
		"field_name": "modular_fieldselection",
		"field_id": 6,
		"field_slug": "section-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"6fzd2968u4c0000000000\",\"field_input_name\":\"htmlTag\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"HTML Tag\",\"inputName\":\"htmlTag\",\"fieldSlug\":\"html-tag\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"1\"}"
	},
	{
		"field_field_name": "Section Element",
		"field_name": "input_text",
		"field_id": 7,
		"field_slug": "section-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3at3mycfn1g0000000000\",\"field_input_name\":\"registerHookName\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Register Hookname\",\"inputName\":\"registerHookName\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Register a section hook-name\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Loop",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "loop",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"3pta9902tyy0000000000\",\"field_input_name\":\"loop\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Loop\",\"inputName\":\"loop\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Loop",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 2,
		"field_slug": "loop",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"4qcuxkqf9e00000000000\",\"field_input_name\":\"loop-data\",\"hook_name\":\"tonicsPageBuilderLoopData\",\"tabbed_key\":\"\",\"fieldName\":\"Data Field\",\"inputName\":\"loop-data\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderLoopData\"}"
	},
	{
		"field_field_name": "Loop",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 3,
		"field_slug": "loop",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"x64254ar5nk000000000\",\"field_input_name\":\"loop-children\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"loop-children\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Loop",
		"field_name": "modular_rowcolumn",
		"field_id": 4,
		"field_slug": "loop",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"7egjd6lw15g0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Loop",
		"field_name": "input_text",
		"field_id": 5,
		"field_slug": "loop",
		"field_parent_id": 4,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"skyxsrtszc0000000000\",\"field_input_name\":\"LoopCacheKey\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Cache Key\",\"inputName\":\"LoopCacheKey\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"Name of the cache-key to use\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Name of the cache-key to use\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Loop",
		"field_name": "input_text",
		"field_id": 6,
		"field_slug": "loop",
		"field_parent_id": 4,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6vskqyqpams0000000000\",\"field_input_name\":\"LoopCachePull\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Pull From Cache\",\"inputName\":\"LoopCachePull\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"Name of the Cache Key storing the data, note that if you are pulling from cache, you don't need to add anything in the Data Field, it would use the pulled data\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Name of the Cache Key storing the data\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Loop",
		"field_name": "input_select",
		"field_id": 7,
		"field_slug": "loop",
		"field_parent_id": 4,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"2p7fy9526640000000000\",\"field_input_name\":\"LoopCacheData\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Loop Cache Data\",\"inputName\":\"LoopCacheData\",\"selectData\":\"0:False,1:True\",\"defaultValue\":\"\",\"info\":\"If the data is pulled from the cache, select True to loop the pulled cache data, False, otherwise\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Json Data",
		"field_name": "input_text",
		"field_id": 1,
		"field_slug": "json-data",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"field_slug_unique_hash\":\"3h5ro88zfp00000000000\",\"field_input_name\":\"json-data\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"JSON\",\"inputName\":\"json-data\",\"textType\":\"textarea\",\"defaultValue\":\"[\\n  {\\n    \\\"name\\\": \\\"Example One\\\",\\n    \\\"age\\\": 28\\n  },\\n  {\\n    \\\"name\\\": \\\"Example Two\\\",\\n    \\\"age\\\": 34\\n  }\\n]\\n\",\"info\":\"Access the value of the json by referencing its key in the format: <code>[[key-name]]</code>\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter JSON Data\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:200px;\",\"toggleable\":\"0\"}"
	},
	{
		"field_field_name": "Tonics Template System",
		"field_name": "input_text",
		"field_id": 1,
		"field_slug": "tonics-template-system",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"field_slug_unique_hash\":\"ravpzszpvhs000000000\",\"field_input_name\":\"TonicsTemplateSystem\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"TonicsTemplateSystem\",\"inputName\":\"TonicsTemplateSystem\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Write Template Logic\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:250px;\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Video Element",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 1,
		"field_slug": "video-element",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_slug\":\"modular_fieldselectiondropper\",\"field_slug_unique_hash\":\"2gc9llylnhes00000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Video Type\",\"inputName\":\"videoElementType\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderVideoElementType\"}"
	},
	{
		"field_field_name": "YouTube Video Type",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "youtube-video-type",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"33wjimy15t80000000000\",\"field_input_name\":\"options\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Options\",\"inputName\":\"options\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "YouTube Video Type",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "youtube-video-type",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"83idk0l8mcg000000000\",\"field_input_name\":\"id\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"ID\",\"inputName\":\"id\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Youtube Embed ID\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "YouTube Video Type",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "youtube-video-type",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6eoj5yz4uts0000000000\",\"field_input_name\":\"title\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Title\",\"inputName\":\"title\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Custom Video Title\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "YouTube Video Type",
		"field_name": "modular_rowcolumn",
		"field_id": 4,
		"field_slug": "youtube-video-type",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"2d4ykwx0bqas000000000\",\"field_input_name\":\"posterImage\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Poster Image\",\"inputName\":\"posterImage\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "YouTube Video Type",
		"field_name": "media_media-image",
		"field_id": 5,
		"field_slug": "youtube-video-type",
		"field_parent_id": 4,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"5c6vkwnkws00000000000\",\"field_input_name\":\"image\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Image\",\"inputName\":\"image\",\"imageLink\":\"\",\"featured_image\":\"\",\"defaultImage\":\"\"}"
	},
	{
		"field_field_name": "YouTube Video Type",
		"field_name": "input_text",
		"field_id": 6,
		"field_slug": "youtube-video-type",
		"field_parent_id": 4,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6kwffcfjsmk0000000000\",\"field_input_name\":\"externalURL\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"External URL\",\"inputName\":\"externalURL\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"Use this for custom poster image\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter External URL\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "YouTube Video Type",
		"field_name": "input_select",
		"field_id": 7,
		"field_slug": "youtube-video-type",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"gu04v38feg0000000000\",\"field_input_name\":\"controls\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Controls\",\"inputName\":\"controls\",\"selectData\":\"1:True,0:False\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "YouTube Video Type",
		"field_name": "input_select",
		"field_id": 8,
		"field_slug": "youtube-video-type",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"2apy4elb084k000000000\",\"field_input_name\":\"autoplay\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Autoplay\",\"inputName\":\"autoplay\",\"selectData\":\"0:False,1:True\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "YouTube Video Type",
		"field_name": "input_select",
		"field_id": 9,
		"field_slug": "youtube-video-type",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"4g6mgzz258y0000000000\",\"field_input_name\":\"loop\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Loop\",\"inputName\":\"loop\",\"selectData\":\"0:False,1:True\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "YouTube Video Type",
		"field_name": "modular_fieldselection",
		"field_id": 10,
		"field_slug": "youtube-video-type",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"316wqyoka6e0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Attributes\",\"inputName\":\"\",\"fieldSlug\":\"html-tag-attributes\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Media Video Type",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "media-video-type",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"3oiuud726ew0000000000\",\"field_input_name\":\"options\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Options\",\"inputName\":\"options\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Media Video Type",
		"field_name": "media_media-manager",
		"field_id": 2,
		"field_slug": "media-video-type",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"media_media-manager\",\"media_media-manager_cell\":\"1\",\"field_slug_unique_hash\":\"6go1oeau8i80000000000\",\"field_input_name\":\"src\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Video\",\"inputName\":\"src\",\"featured_link\":\"\",\"file_url\":\"\"}"
	},
	{
		"field_field_name": "Media Video Type",
		"field_name": "modular_rowcolumn",
		"field_id": 3,
		"field_slug": "media-video-type",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"2d4ykwx0bqas000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Poster Image\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Media Video Type",
		"field_name": "media_media-image",
		"field_id": 4,
		"field_slug": "media-video-type",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"5c6vkwnkws00000000000\",\"field_input_name\":\"posterImage\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Image\",\"inputName\":\"posterImage\",\"imageLink\":\"\",\"featured_image\":\"\",\"defaultImage\":\"\"}"
	},
	{
		"field_field_name": "Media Video Type",
		"field_name": "input_text",
		"field_id": 5,
		"field_slug": "media-video-type",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6kwffcfjsmk0000000000\",\"field_input_name\":\"posterImageExternalURL\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"External URL\",\"inputName\":\"posterImageExternalURL\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"Use this for custom poster image\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter External URL\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Media Video Type",
		"field_name": "input_select",
		"field_id": 6,
		"field_slug": "media-video-type",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"zbypwraqyf4000000000\",\"field_input_name\":\"controls\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Controls\",\"inputName\":\"controls\",\"selectData\":\"1:True,0:False\",\"defaultValue\":\"1\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\",\"hookName\":\"\"}"
	},
	{
		"field_field_name": "Media Video Type",
		"field_name": "input_select",
		"field_id": 7,
		"field_slug": "media-video-type",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"26hi1t54ls8w000000000\",\"field_input_name\":\"autoplay\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Autoplay\",\"inputName\":\"autoplay\",\"selectData\":\"0:False,1:True\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\",\"hookName\":\"\"}"
	},
	{
		"field_field_name": "Media Video Type",
		"field_name": "input_select",
		"field_id": 8,
		"field_slug": "media-video-type",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"266r6c56gjxc000000000\",\"field_input_name\":\"loop\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Loop\",\"inputName\":\"loop\",\"selectData\":\"0:False,1:True\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\",\"hookName\":\"\"}"
	},
	{
		"field_field_name": "Media Video Type",
		"field_name": "input_select",
		"field_id": 9,
		"field_slug": "media-video-type",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"1zmv572nhj7k000000000\",\"field_input_name\":\"preload\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Preload\",\"inputName\":\"preload\",\"selectData\":\":,auto,metadata\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\",\"hookName\":\"\"}"
	},
	{
		"field_field_name": "Media Video Type",
		"field_name": "input_select",
		"field_id": 10,
		"field_slug": "media-video-type",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"2riyheplsta0000000000\",\"field_input_name\":\"muted\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Mute\",\"inputName\":\"muted\",\"selectData\":\"1:True,0:False\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\",\"hookName\":\"\"}"
	},
	{
		"field_field_name": "Media Video Type",
		"field_name": "modular_fieldselection",
		"field_id": 11,
		"field_slug": "media-video-type",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"5tb0trikfro000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Attributes\",\"inputName\":\"\",\"fieldSlug\":\"html-tag-attributes\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Menu Element",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "menu-element",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"kfr3v7lvzlc000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Menu\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Menu Element",
		"field_name": "menu_menus",
		"field_id": 2,
		"field_slug": "menu-element",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"menu_menus\",\"menu_menus_cell\":\"1\",\"field_slug_unique_hash\":\"6t6hx4yczl00000000000\",\"field_input_name\":\"menu\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Menu\",\"inputName\":\"menu\"}"
	},
	{
		"field_field_name": "Menu Element",
		"field_name": "modular_rowcolumn",
		"field_id": 3,
		"field_slug": "menu-element",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"6s5v5a0tbic0000000000\",\"field_input_name\":\"tonicsPageBuilderSettings\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"tonicsPageBuilderSettings\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Menu Element",
		"field_name": "input_text",
		"field_id": 4,
		"field_slug": "menu-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2kj5ud3qyvc0000000000\",\"field_input_name\":\"htmlTagCustomTag\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Custom Tag\",\"inputName\":\"htmlTagCustomTag\",\"textType\":\"text\",\"defaultValue\":\"ul\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Custom Tag\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Menu Element",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 5,
		"field_slug": "menu-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"3nrv3psr2zw0000000000\",\"field_input_name\":\"propertyRepeater\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"propertyRepeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"1\",\"repeat_button_text\":\"New Property\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Menu Element",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 6,
		"field_slug": "menu-element",
		"field_parent_id": 5,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"6gfrcf3erew0000000000\",\"field_input_name\":\"property\",\"hook_name\":\"tonicsPageBuilderTextProperty\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"property\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderTextProperty\"}"
	},
	{
		"field_field_name": "Menu Element",
		"field_name": "modular_fieldselection",
		"field_id": 7,
		"field_slug": "menu-element",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"2yso4hffabq000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Attributes\",\"inputName\":\"\",\"fieldSlug\":\"html-tag-attributes\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "HTML Tag Attributes",
		"field_name": "input_text",
		"field_id": 1,
		"field_slug": "html-tag-attributes",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"field_slug_unique_hash\":\"2g6w1sl5nk4k00000000\",\"field_input_name\":\"htmlTagAttribute\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Attribute\",\"inputName\":\"htmlTagAttribute\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"info\":\"Attribute should start with the attribute key and the value should be single or double quoted, e.g:\\n\\nclass=\\\"class-one class-two\\\" placeholder='Password',etc\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"id='name' class='class-name'\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Layout Settings",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "layout-settings",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"1txyqtnoxxq8000000000\",\"field_input_name\":\"tonicsPageBuilderSettings\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"tonicsPageBuilderSettings\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout Settings",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "layout-settings",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2kj5ud3qyvc0000000000\",\"field_input_name\":\"htmlTagCustomTag\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Custom Tag\",\"inputName\":\"htmlTagCustomTag\",\"textType\":\"text\",\"defaultValue\":\"div\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Custom Tag\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Layout Settings",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 3,
		"field_slug": "layout-settings",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"3nrv3psr2zw0000000000\",\"field_input_name\":\"propertyRepeater\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"propertyRepeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"1\",\"repeat_button_text\":\"New Property\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Layout Settings",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 4,
		"field_slug": "layout-settings",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"6gfrcf3erew0000000000\",\"field_input_name\":\"property\",\"hook_name\":\"tonicsPageBuilderTextProperty\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"property\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderTextProperty\"}"
	},
	{
		"field_field_name": "Layout Settings",
		"field_name": "modular_fieldselection",
		"field_id": 5,
		"field_slug": "layout-settings",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"2yso4hffabq000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Attributes\",\"inputName\":\"\",\"fieldSlug\":\"html-tag-attributes\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "HTML Class",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "html-class",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"5c7etx54yus0000000000\",\"field_input_name\":\"classElement\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Class\",\"inputName\":\"classElement\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "HTML Class",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "html-class",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5q0pbxz67io0000000000\",\"field_input_name\":\"tonicsBuilderClassName\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Class Name\",\"inputName\":\"tonicsBuilderClassName\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\" Class Name To Create e.g .class-one\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "HTML Class",
		"field_name": "modular_rowcolumn",
		"field_id": 3,
		"field_slug": "html-class",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"5xcr0d48cj40000000000\",\"field_input_name\":\"tonicsPageBuilderSettings\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"tonicsPageBuilderSettings\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "HTML Class",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 4,
		"field_slug": "html-class",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"2ls09hogahq0000000000\",\"field_input_name\":\"propertyRepeater\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"propertyRepeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"1\",\"repeat_button_text\":\"New Property\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "HTML Class",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 5,
		"field_slug": "html-class",
		"field_parent_id": 4,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"4ysl4h8bkks0000000000\",\"field_input_name\":\"property\",\"hook_name\":\"tonicsPageBuilderTextProperty\",\"tabbed_key\":\"\",\"fieldName\":\"Property\",\"inputName\":\"property\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderContainerProperty\"}"
	},
	{
		"field_field_name": "Post Query",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "post-query",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"5hal4sg95480000000000\",\"field_input_name\":\"post-query\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Query Option\",\"inputName\":\"post-query\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"Access the value of the each post data by referencing its key in the format: [[post_title]], [[post_content]], etc\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"styles\":\"\",\"toggleable\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Post Query",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 2,
		"field_slug": "post-query",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"16hj2ekz5u68000000000\",\"field_input_name\":\"simpleParameterContainer\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Simple Parameters\",\"inputName\":\"simpleParameterContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"The value should be in the format: <code>id=\\\"1,2,3\\\"</code> or <code>slug=\\\"slug-1,slug-2\\\"</code>, if you don't follow the format, it would have to guess\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"1\",\"repeat_button_text\":\"Repeat Parameter\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Post Query",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 3,
		"field_slug": "post-query",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"3h05fgh72xa0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Parameter Type\",\"inputName\":\"\",\"fieldSlug\":[\"post-query-parameter-type\",\"category-query-parameter-type\"],\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"\"}"
	},
	{
		"field_field_name": "Post Query",
		"field_name": "input_text",
		"field_id": 4,
		"field_slug": "post-query",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3a5myzqet740000000000\",\"field_input_name\":\"simpleParameterParameterTypeValue\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Value\",\"inputName\":\"simpleParameterParameterTypeValue\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Value\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Post Query",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 5,
		"field_slug": "post-query",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"5d3ahybnlsc0000000000\",\"field_input_name\":\"DateParameterContainer\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Date Parameters\",\"inputName\":\"DateParameterContainer\",\"row\":\"1\",\"column\":\"2\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"1\",\"repeat_button_text\":\"Repeat Date\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Post Query",
		"field_name": "input_select",
		"field_id": 6,
		"field_slug": "post-query",
		"field_parent_id": 5,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"3a7d9utk7780000000000\",\"field_input_name\":\"DateParametersDateType\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Date Type\",\"inputName\":\"DateParametersDateType\",\"selectData\":\":,CreatedTime,AfterCreatedTime,BeforeCreatedTime,UpdatedTime,AfterUpdatedTime,BeforeUpdatedTime\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\",\"hookName\":\"\"}"
	},
	{
		"field_field_name": "Post Query",
		"field_name": "input_date",
		"field_id": 7,
		"field_slug": "post-query",
		"field_parent_id": 5,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_date\",\"input_date_cell\":\"2\",\"field_slug_unique_hash\":\"2xejrddthyk0000000000\",\"field_input_name\":\"DateParametersDate\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Date\",\"inputName\":\"DateParametersDate\",\"dateType\":\"datetime-local\",\"min\":\"\",\"max\":\"\",\"readonly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\"}"
	},
	{
		"field_field_name": "Post Query",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 8,
		"field_slug": "post-query",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"21fwprlmnops000000000\",\"field_input_name\":\"OrderParameterContainer\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Order Parameters\",\"inputName\":\"OrderParameterContainer\",\"row\":\"1\",\"column\":\"2\",\"grid_template_col\":\"\",\"info\":\"Ensure you choose the right order by, e.g, for Post Parameter Type, choose post_*, and for Category Parameter, choose cat_*\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"1\",\"repeat_button_text\":\"Repeat Order\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Post Query",
		"field_name": "input_select",
		"field_id": 9,
		"field_slug": "post-query",
		"field_parent_id": 8,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"1xt41rz70irk000000000\",\"field_input_name\":\"OrderParametersDirection\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Direction\",\"inputName\":\"OrderParametersDirection\",\"selectData\":\"ASC,DESC\",\"defaultValue\":\"DESC\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\",\"hookName\":\"\"}"
	},
	{
		"field_field_name": "Post Query",
		"field_name": "input_select",
		"field_id": 10,
		"field_slug": "post-query",
		"field_parent_id": 8,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"2\",\"field_slug_unique_hash\":\"6ct7tuwy2xw0000000000\",\"field_input_name\":\"OrderParametersBy\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"By\",\"inputName\":\"OrderParametersBy\",\"selectData\":\"post_id,post_title,post_slug,post_status,cat_id,cat_name,cat_slug,cat_status,created_at,updated_at\",\"defaultValue\":\"created_at\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\",\"hookName\":\"\"}"
	},
	{
		"field_field_name": "Post Query",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 11,
		"field_slug": "post-query",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"14l1wem8hghs000000000\",\"field_input_name\":\"StatusParameterContainer\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Status Parameters\",\"inputName\":\"StatusParameterContainer\",\"row\":\"1\",\"column\":\"2\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"1\",\"repeat_button_text\":\"Repeat Status\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Post Query",
		"field_name": "input_select",
		"field_id": 12,
		"field_slug": "post-query",
		"field_parent_id": 11,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"2fght17sr4bo000000000\",\"field_input_name\":\"StatusParametersType\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Type\",\"inputName\":\"StatusParametersType\",\"selectData\":\"Status,StatusNotIn,OrStatus,OrStatusNotIn\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\",\"hookName\":\"\"}"
	},
	{
		"field_field_name": "Post Query",
		"field_name": "input_select",
		"field_id": 13,
		"field_slug": "post-query",
		"field_parent_id": 11,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"2\",\"field_slug_unique_hash\":\"5ewfn4nt6680000000000\",\"field_input_name\":\"StatusParametersStatus\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Status\",\"inputName\":\"StatusParametersStatus\",\"selectData\":\":,1:Publish,0:Draft,-1:Trash\",\"defaultValue\":\"1\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\",\"hookName\":\"\"}"
	},
	{
		"field_field_name": "Post Query",
		"field_name": "modular_rowcolumn",
		"field_id": 14,
		"field_slug": "post-query",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"6mepv3je2700000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Others\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Post Query",
		"field_name": "input_text",
		"field_id": 15,
		"field_slug": "post-query",
		"field_parent_id": 14,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"c456eh84tcg000000000\",\"field_input_name\":\"PaginationParametersPerPage\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Posts Per Page\",\"inputName\":\"PaginationParametersPerPage\",\"textType\":\"number\",\"defaultValue\":\"20\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"number of post to show per page\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Post Query",
		"field_name": "input_select",
		"field_id": 16,
		"field_slug": "post-query",
		"field_parent_id": 14,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"d1qi52505i0000000000\",\"field_input_name\":\"ChildrenNested\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Include Children\",\"inputName\":\"ChildrenNested\",\"selectData\":\"0:False,1:True\",\"defaultValue\":\"\",\"info\":\"If set to true, it would recursively get the children of the query loop\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\",\"hookName\":\"\"}"
	},
	{
		"field_field_name": "Post Query",
		"field_name": "input_text",
		"field_id": 17,
		"field_slug": "post-query",
		"field_parent_id": 14,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"kl4a3jkhocw000000000\",\"field_input_name\":\"searchParameter\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Search Parameter\",\"inputName\":\"searchParameter\",\"textType\":\"text\",\"defaultValue\":\"query\",\"info\":\"The name of the url param where the search keyword is located\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Post Query Parameter Type",
		"field_name": "input_select",
		"field_id": 1,
		"field_slug": "post-query-parameter-type",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"field_slug_unique_hash\":\"xk5rizofk9s000000000\",\"field_input_name\":\"postSimpleParameterParameterType\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Post Parameter Type\",\"inputName\":\"postSimpleParameterParameterType\",\"selectData\":\":,Author,AuthorNotIn,OrAuthor,OrAuthorNotIn,Category,CategoryNotIn,OrCategory,OrCategoryNotIn,Post,OrPost\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Category Query Parameter Type",
		"field_name": "input_select",
		"field_id": 1,
		"field_slug": "category-query-parameter-type",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"field_slug_unique_hash\":\"6brvo8u881o0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Category Parameter Type\",\"inputName\":\"categorySimpleParameterParameterType\",\"selectData\":\":,Category,CategoryNotIn,OrCategory,OrCategoryNotIn\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Hook",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 1,
		"field_slug": "hook",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"field_slug_unique_hash\":\"747or0fk0f00000000000\",\"field_input_name\":\"hook-repeater\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Hook\",\"inputName\":\"hook-repeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"1\",\"toggleable\":\"0\",\"repeat_button_text\":\"Repeat Hook\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Hook",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 2,
		"field_slug": "hook",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"19hqlrgu1ytc000000000\",\"field_input_name\":\"tonicsPageBuilderFieldSelector\",\"hook_name\":\"tonicsPageBuilderFieldSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Field\",\"inputName\":\"tonicsPageBuilderFieldSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderFieldSelector\"}"
	},
	{
		"field_field_name": "Hook",
		"field_name": "modular_rowcolumn",
		"field_id": 3,
		"field_slug": "hook",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"44vj7fx28vs0000000000\",\"field_input_name\":\"tonicsPageBuilderSettings\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"tonicsPageBuilderSettings\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Hook",
		"field_name": "input_select",
		"field_id": 4,
		"field_slug": "hook",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"5izi8ppmtvc0000000000\",\"field_input_name\":\"hookname\",\"hook_name\":\"tonicsLayoutHookSelector\",\"tabbed_key\":\"\",\"fieldName\":\"Hook\",\"inputName\":\"hookname\",\"selectData\":\":\",\"defaultValue\":\"\",\"info\":\"Do ensure the hook data field, comes before wherever the hook is registered which is typically the section element.\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\",\"hookName\":\"tonicsLayoutHookSelector\"}"
	},
	{
		"field_field_name": "Hook",
		"field_name": "input_select",
		"field_id": 5,
		"field_slug": "hook",
		"field_parent_id": 3,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"2lirvg12fd20000000000\",\"field_input_name\":\"hooktype\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"HookType\",\"inputName\":\"hooktype\",\"selectData\":\"Replace,Append,Clear,Rehook\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\",\"hookName\":\"\"}"
	},
	{
		"field_field_name": "Style Background",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "style-background",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"7bnbnie1qgo0000000000\",\"field_input_name\":\"styleBackgroundContainer\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Background\",\"inputName\":\"styleBackgroundContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Style Background",
		"field_name": "media_media-image",
		"field_id": 2,
		"field_slug": "style-background",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"74czd83joxg0000000000\",\"field_input_name\":\"style-bg-image\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Image\",\"inputName\":\"style-bg-image\",\"imageLink\":\"\",\"featured_image\":\"\",\"defaultImage\":\"\"}"
	},
	{
		"field_field_name": "Style Background",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "style-background",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"15o0on0uznmk000000000\",\"field_input_name\":\"style-bg-image-custom\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"External URL\",\"inputName\":\"style-bg-image-custom\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter External Image URL\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Style Background",
		"field_name": "modular_rowcolumn",
		"field_id": 4,
		"field_slug": "style-background",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"85xkl8lt89c000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Size\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Style Background",
		"field_name": "input_select",
		"field_id": 5,
		"field_slug": "style-background",
		"field_parent_id": 4,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"3aq13klrt380000000000\",\"field_input_name\":\"background-size\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Size\",\"inputName\":\"background-size\",\"selectData\":\":,auto,cover,contain\",\"defaultValue\":\"cover\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Style Background",
		"field_name": "input_text",
		"field_id": 6,
		"field_slug": "style-background",
		"field_parent_id": 4,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4kva1jvqfns0000000000\",\"field_input_name\":\"background-size-custom\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Custom Size\",\"inputName\":\"background-size-custom\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"Enter Custom Size, e.g: 30% or 100px\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Custom Size, e.g: 30% or 100px\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Style Background",
		"field_name": "input_select",
		"field_id": 7,
		"field_slug": "style-background",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"706hqy1ptf8000000000\",\"field_input_name\":\"background-attachment\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Attachment\",\"inputName\":\"background-attachment\",\"selectData\":\":,scroll,fixed\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Style Background",
		"field_name": "input_select",
		"field_id": 8,
		"field_slug": "style-background",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"7fswwk0m1mo0000000000\",\"field_input_name\":\"background-repeat\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Repeat\",\"inputName\":\"background-repeat\",\"selectData\":\":,repeat,repeat-x,repeat-y,no-repeat,space,round\",\"defaultValue\":\"repeat\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Style Background",
		"field_name": "input_select",
		"field_id": 9,
		"field_slug": "style-background",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"1bdushdhlibk000000000\",\"field_input_name\":\"background-clip\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Clip\",\"inputName\":\"background-clip\",\"selectData\":\":,border-box,padding-box,content-box,text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Style Background",
		"field_name": "input_select",
		"field_id": 10,
		"field_slug": "style-background",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"5p939ujs8ik0000000000\",\"field_input_name\":\"background-origin\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Origin\",\"inputName\":\"background-origin\",\"selectData\":\":,border-box,padding-box,content-box\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Style Background",
		"field_name": "input_select",
		"field_id": 11,
		"field_slug": "style-background",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"10j7aumyp09c000000000\",\"field_input_name\":\"background-blend-mode\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Blend Mode\",\"inputName\":\"background-blend-mode\",\"selectData\":\":,normal,multiply,screen,overlay,darken,lighten,color-dodge,color-burn,hard-light,soft-light,difference,exclusion,hue,saturation,color,luminosity\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Style Background",
		"field_name": "modular_rowcolumn",
		"field_id": 12,
		"field_slug": "style-background",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"6x85fzsxez80000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Position\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Style Background",
		"field_name": "input_select",
		"field_id": 13,
		"field_slug": "style-background",
		"field_parent_id": 12,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"6z61f763i14000000000\",\"field_input_name\":\"background-position\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Position\",\"inputName\":\"background-position\",\"selectData\":\":,left,center,right,top,bottom\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Style Background",
		"field_name": "input_text",
		"field_id": 14,
		"field_slug": "style-background",
		"field_parent_id": 12,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2kp8ehosv7c0000000000\",\"field_input_name\":\"background-position-custom\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Custom Postion\",\"inputName\":\"background-position-custom\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"Enter custom position in this format: \\n\\nX=\\\"200px\\\" Y=\\\"211px\\\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"X='200px' Y='211px'\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Style Animation",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "style-animation",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"6naxl1m51po0000000000\",\"field_input_name\":\"styleAnimationContainer\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Animation\",\"inputName\":\"styleAnimationContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Style Animation",
		"field_name": "input_select",
		"field_id": 2,
		"field_slug": "style-animation",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"2h76x4987ua0000000000\",\"field_input_name\":\"style-hover-animation\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Hover\",\"inputName\":\"style-hover-animation\",\"selectData\":\":,float-hover:float,trans-left-hover:trans-left,trans-right-hover:trans-right,trans-up-hover:trans-up,trans-down-hover:trans-down,shake-hover:shake,shake-fix-hover:shake-fix,jello-hover:jello,swing-hover:swing\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Style Animation",
		"field_name": "input_select",
		"field_id": 3,
		"field_slug": "style-animation",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"4l3f513hkgs0000000000\",\"field_input_name\":\"style-scale-animation\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Scale\",\"inputName\":\"style-scale-animation\",\"selectData\":\":,scale-up-center,scale-up-top,scale-up-tr,scale-up-right,scale-up-br,scale-up-bottom,scale-up-bl,scale-up-left,scale-up-tl,scale-up-hor-center,scale-up-hor-left,scale-up-hor-right,scale-up-ver-center,scale-up-ver-top,scale-up-ver-bottom,scale-in-center,scale-in-top,scale-in-tr,scale-in-right,scale-in-br,scale-in-bottom,scale-in-bl,scale-in-left,scale-in-tl,scale-in-hor-center,scale-in-hor-left,scale-in-hor-right,scale-in-ver-center,scale-in-ver-top,scale-in-ver-bottom\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Container Query Type",
		"field_name": "input_select",
		"field_id": 1,
		"field_slug": "container-query-type",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"field_slug_unique_hash\":\"2hb5pk6kqru0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Type\",\"inputName\":\"container-type\",\"selectData\":\"inline-size,block-size,size,none\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Style Color Picker",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "style-color-picker",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"232gsrlo1vy8000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Color Styles\",\"inputName\":\"colorStylesContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"1\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Style Color Picker",
		"field_name": "input_select",
		"field_id": 2,
		"field_slug": "style-color-picker",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"7go6u17mnfs0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Color Type 🔧\",\"inputName\":\"tonicsBuilderStyleColorType\",\"selectData\":\"Color,Background,Link,Link Hover,Heading\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Style Color Picker",
		"field_name": "input_colorpicker",
		"field_id": 3,
		"field_slug": "style-color-picker",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"input_colorpicker\",\"input_colorpicker_cell\":\"1\",\"field_slug_unique_hash\":\"5n0k3d0isv80000000000\",\"field_input_name\":\"tonicsBuilderStyleColor\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Color Picker\",\"inputName\":\"tonicsBuilderStyleColor\",\"defaultValue\":\"#000000\"}"
	},
	{
		"field_field_name": "Style Attribute",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 1,
		"field_slug": "style-attribute",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"field_slug_unique_hash\":\"4gu8hjakwf40000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Attribute\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"2\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"1\",\"repeat_button_text\":\"Repeat Attribute\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Style Attribute",
		"field_name": "input_select",
		"field_id": 2,
		"field_slug": "style-attribute",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"d4jvz876js0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Attribute\",\"inputName\":\"style-attribute\",\"selectData\":\"align-content, align-items, align-self, all, animation, animation-delay, animation-direction, animation-duration, animation-fill-mode, animation-iteration-count, animation-name, animation-play-state, animation-timing-function, aspect-ratio, backface-visibility, background, background-attachment, background-blend-mode, background-clip, background-color, background-image, background-origin, background-position, background-repeat, background-size, border, border-bottom, border-bottom-color, border-bottom-left-radius, border-bottom-right-radius, border-bottom-style, border-bottom-width, border-collapse, border-color, border-image, border-image-outset, border-image-repeat, border-image-slice, border-image-source, border-image-width, border-left, border-left-color, border-left-style, border-left-width, border-radius, border-right, border-right-color, border-right-style, border-right-width, border-spacing, border-style, border-top, border-top-color, border-top-left-radius, border-top-right-radius, border-top-style, border-top-width, border-width, bottom, box-decoration-break, box-shadow, box-sizing, break-after, break-before, break-inside, caption-side, caret-color, @charset, clear, clip, clip-path, color, column-count, column-fill, column-gap, column-rule, column-rule-color, column-rule-style, column-rule-width, column-span, column-width, columns, content, counter-increment, counter-reset, cursor, direction, display, empty-cells, filter, flex, flex-basis, flex-direction, flex-flow, flex-grow, flex-shrink, flex-wrap, float, font, font-family, font-feature-settings, font-kerning, font-language-override, font-optical-sizing, font-size, font-size-adjust, font-stretch, font-style, font-synthesis, font-variant, font-variant-alternates, font-variant-caps, font-variant-east-asian, font-variant-ligatures, font-variant-numeric, font-variant-position, font-weight, gap, grid, grid-area, grid-auto-columns, grid-auto-flow, grid-auto-rows, grid-column, grid-column-end, grid-column-gap, grid-column-start, grid-gap, grid-row, grid-row-end, grid-row-gap, grid-row-start, grid-template, grid-template-areas, grid-template-columns, grid-template-rows, hanging-punctuation, height, hyphens, image-rendering, @import, isolation, justify-content, @keyframes, left, letter-spacing, line-break, line-height, list-style, list-style-image, list-style-position, list-style-type, margin, margin-block, margin-block-end, margin-block-start, margin-bottom, margin-inline, margin-inline-end, margin-inline-start, margin-left, margin-right, margin-top, mask, mask-border, mask-border-mode, mask-border-outset, mask-border-repeat, mask-border-slice, mask-border-source, mask-border-width, mask-clip, mask-composite, mask-image, mask-mode, mask-origin, mask-position, mask-repeat, mask-size, mask-type, max-height, max-width, @media, min-height, min-width, mix-blend-mode, object-fit, object-position, offset, offset-anchor, offset-distance, offset-path, offset-rotate, opacity, order, orphans, outline, outline-color, outline-offset, outline-style, outline-width, overflow, overflow-anchor, overflow-wrap, overflow-x, overflow-y, overscroll-behavior, overscroll-behavior-x, overscroll-behavior-y, padding, padding-block, padding-block-end, padding-block-start, padding-bottom, padding-inline, padding-inline-end, padding-inline-start, padding-left, padding-right, padding-top, page-break-after, page-break-before, page-break-inside, perspective, perspective-origin, place-content, place-items, place-self, pointer-events, position, quotes, resize, right, rotate, row-gap, ruby-align, ruby-position, scale, scroll-behavior, scroll-margin, scroll-margin-block, scroll-margin-block-end, scroll-margin-block-start, scroll-margin-bottom, scroll-margin-inline, scroll-margin-inline-end, scroll-margin-inline-start, scroll-margin-left, scroll-margin-right, scroll-margin-top, scroll-padding, scroll-padding-block, scroll-padding-block-end, scroll-padding-block-start, scroll-padding-bottom, scroll-padding-inline, scroll-padding-inline-end, scroll-padding-inline-start, scroll-padding-left, scroll-padding-right, scroll-padding-top, scroll-snap-align, scroll-snap-stop, scroll-snap-type, scrollbar-color, scrollbar-gutter, scrollbar-width, shape-image-threshold, shape-margin, shape-outside, tab-size, table-layout, text-align, text-align-last, text-combine-upright, text-decoration, text-decoration-color, text-decoration-line, text-decoration-skip-ink, text-decoration-style, text-emphasis, text-emphasis-color, text-emphasis-position, text-emphasis-style, text-indent, text-justify, text-orientation, text-overflow, text-rendering, text-shadow, text-transform, text-underline-position, top, touch-action, transform, transform-box, transform-origin, transform-style, transition, transition-delay, transition-duration, transition-property, transition-timing-function, translate, unicode-bidi, user-select, vertical-align, visibility, white-space, widows, width, will-change, word-break, word-spacing, word-wrap, writing-mode, z-index\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Style Attribute",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "style-attribute",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"1ftodcecdnq8000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Value\",\"inputName\":\"style-attribute-value\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Attribute value: e.g 1em, 10px\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Style Class Utilities",
		"field_name": "input_select",
		"field_id": 1,
		"field_slug": "style-class-utilities",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"field_slug_unique_hash\":\"4dd0c1o712c0000000000\",\"field_input_name\":\"class-utilities\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Class Utilities\",\"inputName\":\"class-utilities\",\"selectData\":\"container-max-width:Default Max Width\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"1\"}"
	},
	{
		"field_field_name": "Page Import",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 1,
		"field_slug": "page-import",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_slug\":\"modular_rowcolumnrepeater\",\"field_slug_unique_hash\":\"2t3s8nq4x5w0000000000\",\"field_input_name\":\"pageImport\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Page Import\",\"inputName\":\"pageImport\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"1\",\"toggleable\":\"1\",\"repeat_button_text\":\"Repeat Import\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Page Import",
		"field_name": "interface_table",
		"field_id": 2,
		"field_slug": "page-import",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"interface_table\",\"interface_table_cell\":\"1\",\"field_slug_unique_hash\":\"571svsfj4yc0000000000\",\"field_input_name\":\"pages-import-input\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Import From\",\"inputName\":\"pages-import-input\",\"tableName\":\"pages\",\"orderBy\":\"page_title\",\"colNameDisplay\":\"page_title\",\"colNameValue\":\"page_slug\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "Page Import",
		"field_name": "modular_fieldselection",
		"field_id": 3,
		"field_slug": "page-import",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"6nb63gzjvrk0000000000\",\"field_input_name\":\"hook\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Hook\",\"inputName\":\"hookPageImport\",\"fieldSlug\":\"hook\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"group\":\"1\",\"toggleable\":\"1\"}"
	},
	{
		"field_field_name": "HTML Fragment",
		"field_name": "input_text",
		"field_id": 1,
		"field_slug": "html-fragment",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"field_slug_unique_hash\":\"58zyxreusn40000000000\",\"field_input_name\":\"htmlTemplateFragment\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"HTMLTemplateFragment\",\"inputName\":\"htmlTemplateFragment\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Raw HTML Fragment...\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:250px;\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "Module Import",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 1,
		"field_slug": "module-import",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_slug\":\"modular_rowcolumnrepeater\",\"field_slug_unique_hash\":\"5ba9416o19g0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Module Import\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"1\",\"repeat_button_text\":\"Repeat Module Import\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "Module Import",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 2,
		"field_slug": "module-import",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"1qgt8a3vvtc0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Add a Module\",\"inputName\":\"tonicsPageBuilderModuleSelector\",\"defaultFieldSlug\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\",\"group\":\"1\",\"toggleable\":\"\",\"hookName\":\"tonicsPageBuilderModuleSelector\"}"
	},
	{
		"field_field_name": "Post FieldSettings Applier",
		"field_name": "input_text",
		"field_id": 1,
		"field_slug": "post-fieldsettings-applier",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"field_slug_unique_hash\":\"39a4zym30z80000000000\",\"field_input_name\":\"fieldsettings_location\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Post FieldSettings Location\",\"inputName\":\"post_fieldsettings_location\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"This will locate the field_settings and apply its logic to this layout\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"[[field_settings]]\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	}
]
JSON;
        return json_decode($json);
    }
}