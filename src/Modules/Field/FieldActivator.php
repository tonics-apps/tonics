<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Field;

use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Modules\Core\EventHandlers\Field\CacheFieldIDItems;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Controllers\OnFieldItemsSave;
use App\Modules\Field\EventHandlers\FieldMenus;
use App\Modules\Field\EventHandlers\Fields\Input\InputChoices;
use App\Modules\Field\EventHandlers\Fields\Input\InputColor;
use App\Modules\Field\EventHandlers\Fields\Input\InputDate;
use App\Modules\Field\EventHandlers\Fields\Input\InputRange;
use App\Modules\Field\EventHandlers\Fields\Input\InputRichText;
use App\Modules\Field\EventHandlers\Fields\Input\InputSelect;
use App\Modules\Field\EventHandlers\Fields\Input\InputText;
use App\Modules\Field\EventHandlers\Fields\Media\MediaAudio;
use App\Modules\Field\EventHandlers\Fields\Media\MediaFileManager;
use App\Modules\Field\EventHandlers\Fields\Media\MediaImage;
use App\Modules\Field\EventHandlers\Fields\Media\MediaImageWithLink;
use App\Modules\Field\EventHandlers\Fields\Menu\Menu;
use App\Modules\Field\EventHandlers\Fields\Modular\FieldFileHandler;
use App\Modules\Field\EventHandlers\Fields\Modular\FieldSelection;
use App\Modules\Field\EventHandlers\Fields\Modular\RowColumn;
use App\Modules\Field\EventHandlers\Fields\Modular\TonicsTemplateSystem;
use App\Modules\Field\EventHandlers\Fields\OpenGraph\Test;
use App\Modules\Field\EventHandlers\Fields\OpenGraph\Test2;
use App\Modules\Field\EventHandlers\Fields\Post\PostAuthorSelect;
use App\Modules\Field\EventHandlers\Fields\Post\PostCategory;
use App\Modules\Field\EventHandlers\Fields\Post\PostCategorySelect;
use App\Modules\Field\EventHandlers\Fields\Post\Posts;
use App\Modules\Field\EventHandlers\Fields\Query;
use App\Modules\Field\EventHandlers\Fields\Track\TrackArtist;
use App\Modules\Field\EventHandlers\Fields\Track\TrackArtistSelect;
use App\Modules\Field\EventHandlers\Fields\Track\TrackGenre;
use App\Modules\Field\EventHandlers\Fields\Track\TrackGenreRadio;
use App\Modules\Field\EventHandlers\Fields\Track\TrackLicenseSelect;
use App\Modules\Field\EventHandlers\Fields\Track\Tracks;
use App\Modules\Field\EventHandlers\Fields\Widget;
use App\Modules\Field\Events\FieldTemplateFile;
use App\Modules\Field\Events\OnFieldCreate;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Routes\Routes;
use Devsrealm\TonicsRouterSystem\Route;

class FieldActivator implements ModuleConfig, PluginConfig
{

    use Routes;

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

            OnFieldItemsSave::class => [
              CacheFieldIDItems::class
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
                Posts::class,
                PostCategory::class,
                PostCategorySelect::class,
                PostAuthorSelect::class,

                # TRACKS
                Tracks::class,
                TrackArtist::class,
                TrackGenre::class,
                TrackLicenseSelect::class,
                TrackGenreRadio::class,
                TrackArtistSelect::class,

                # Media
                MediaFileManager::class,
                MediaImage::class,
                MediaImageWithLink::class,
                MediaAudio::class,

                # Modular
                RowColumn::class,
                FieldSelection::class,
                TonicsTemplateSystem::class,
                FieldFileHandler::class,

                # Menu
                Menu::class,

                # Widget
                Widget::class,

                # Query
                Query::class,
            ],

            FieldTemplateFile::class => [
                Test::class,
                Test2::class,
                FieldMenus::class
            ],

            OnFieldCreate::class => [
            ],

            OnAdminMenu::class => [
                FieldMenus::class
            ],
        ];
    }

    /**
     * @param Route $routes
     * @return Route
     * @throws \ReflectionException
     */
    public function route(Route $routes): Route
    {
        return $this->routeWeb($routes);
    }

    /**
     * @return array
     */
    public function tables(): array
    {
        return
            [
                Tables::getTable(Tables::FIELD) => Tables::getTable(Tables::FIELD),
                Tables::getTable(Tables::FIELD_ITEMS) => Tables::getTable(Tables::FIELD_ITEMS),
            ];
    }

    public function onInstall(): void
    {
        // TODO: Implement onInstall() method.
    }

    public function onUninstall(): void
    {
        // TODO: Implement onUninstall() method.
    }

    public function info(): array
    {
        return [
            "name" => "Field",
            "type" => "Module",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-Ola.1654594213',
            "stable" => 0,
            "description" => "The Field Module",
            "info_url" => '',
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-field-module/releases/latest",
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
}