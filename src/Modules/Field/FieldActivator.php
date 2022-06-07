<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Field;

use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\Tables;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Field\EventHandlers\FieldMenus;
use App\Modules\Field\EventHandlers\Fields\Input\InputChoices;
use App\Modules\Field\EventHandlers\Fields\Input\InputColor;
use App\Modules\Field\EventHandlers\Fields\Input\InputDate;
use App\Modules\Field\EventHandlers\Fields\Input\InputRange;
use App\Modules\Field\EventHandlers\Fields\Input\InputSelect;
use App\Modules\Field\EventHandlers\Fields\Input\InputText;
use App\Modules\Field\EventHandlers\Fields\Input\InputRichText;
use App\Modules\Field\EventHandlers\Fields\Media\MediaAudio;
use App\Modules\Field\EventHandlers\Fields\Media\MediaFileManager;
use App\Modules\Field\EventHandlers\Fields\Media\MediaImage;
use App\Modules\Field\EventHandlers\Fields\Media\MediaImageWithLink;
use App\Modules\Field\EventHandlers\Fields\Menu\Menu;
use App\Modules\Field\EventHandlers\Fields\Modular\FieldSelection;
use App\Modules\Field\EventHandlers\Fields\Modular\RowColumn;
use App\Modules\Field\EventHandlers\Fields\Modular\TonicsTemplateSystem;
use App\Modules\Field\EventHandlers\Fields\Post\PostAuthorSelect;
use App\Modules\Field\EventHandlers\Fields\Post\PostCategory;
use App\Modules\Field\EventHandlers\Fields\Post\PostCategorySelect;
use App\Modules\Field\EventHandlers\Fields\Post\Posts;
use App\Modules\Field\EventHandlers\Fields\Track\TrackArtist;
use App\Modules\Field\EventHandlers\Fields\Track\TrackArtistSelect;
use App\Modules\Field\EventHandlers\Fields\Track\TrackGenre;
use App\Modules\Field\EventHandlers\Fields\Track\TrackGenreRadio;
use App\Modules\Field\EventHandlers\Fields\Track\TrackLicenseSelect;
use App\Modules\Field\EventHandlers\Fields\Track\Tracks;
use App\Modules\Field\EventHandlers\Fields\Widget;
use App\Modules\Field\Events\OnFieldCreate;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Routes\Routes;
use Devsrealm\TonicsRouterSystem\Route;

class FieldActivator implements ModuleConfig
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

                # Menu
                Menu::class,

                # Widget
                Widget::class,
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
                Tables::getTable(Tables::WIDGET_LOCATIONS) => Tables::getTable(Tables::WIDGET_LOCATIONS),
                Tables::getTable(Tables::WIDGETS) => Tables::getTable(Tables::WIDGETS),
                Tables::getTable(Tables::WIDGET_ITEMS) => Tables::getTable(Tables::WIDGET_ITEMS),
            ];
    }
}