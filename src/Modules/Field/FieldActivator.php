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
use App\Modules\Core\EventHandlers\Field\CacheFieldIDItems;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\EventHandlers\DefaultFieldHandlers\TonicsDefaultFieldsSelection;
use App\Modules\Field\EventHandlers\DefaultFieldHandlers\TonicsOEmbedFieldHandler;
use App\Modules\Field\EventHandlers\DefaultSanitization\DefaultSlugFieldSanitization;
use App\Modules\Field\EventHandlers\DefaultSanitization\PageSlugFieldSanitization;
use App\Modules\Field\EventHandlers\DefaultSanitization\PostContentEditorFieldSanitization;
use App\Modules\Field\EventHandlers\FieldMenus;
use App\Modules\Field\EventHandlers\Fields\Input\InputChoices;
use App\Modules\Field\EventHandlers\Fields\Input\InputColor;
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
use App\Modules\Field\EventHandlers\Fields\Widget;
use App\Modules\Field\Events\FieldTemplateFile;
use App\Modules\Field\Events\OnAddFieldSanitization;
use App\Modules\Field\Events\OnAfterPreSavePostEditorFieldItems;
use App\Modules\Field\Events\OnEditorFieldSelection;
use App\Modules\Field\Events\OnFieldCreate;
use App\Modules\Field\Events\OnFieldItemsSave;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Routes\Routes;
use Devsrealm\TonicsRouterSystem\Route;

class FieldActivator implements ExtensionConfig
{

    use Routes;

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

                # Menu
                Menu::class,

                # Widget
                Widget::class,

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

    public function onUpdate (): void
    {
        // TODO: Implement onUpdate() method.
    }

    public function onDelete (): void
    {
        // TODO: Implement onDelete() method.
    }
}