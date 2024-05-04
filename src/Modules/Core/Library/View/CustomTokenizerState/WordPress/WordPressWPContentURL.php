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

namespace App\Modules\Core\Library\View\CustomTokenizerState\WordPress;

use App\Modules\Core\Library\View\CustomTokenizerState\WordPress\Extensions\WordPressWPContentURL\URL;
use Devsrealm\TonicsTemplateSystem\Content;
use Devsrealm\TonicsTemplateSystem\Exceptions\TonicsTemplateRangeException;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRendererInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsTemplateCustomRendererInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsTemplateHandleEOF;
use Devsrealm\TonicsTemplateSystem\Loader\TonicsTemplateArrayLoader;
use Devsrealm\TonicsTemplateSystem\Node\Tag;
use Devsrealm\TonicsTemplateSystem\TonicsView;
use Devsrealm\TonicsTemplateSystem\Tree\Mode\Handlers\CharacterModeHandler;

class WordPressWPContentURL
{
    private TonicsView|null $init = null;

    public function __construct()
    {
        ## Tonics View
        $templateLoader = new TonicsTemplateArrayLoader();
        $settings = [
            'templateLoader' => $templateLoader,
            'tokenizerState' => new WordPressWPContentURLTokenizerState(),
            'content' => new Content(),
            'handleEOF' => new WordPressWPContentURLHandleEOF(),
            'render' => new WordPressWPContentURLCustomRenderer()
        ];
        $view = new TonicsView($settings);
        // clear in-built mode handler
        $view->setModeHandler([]);
        $view->addModeHandler('url', URL::class);
        $view->addModeHandler('char', CharacterModeHandler::class);
        $this->init = $view;
    }

    public function getView(): ?TonicsView
    {
        return $this->init;
    }
}

