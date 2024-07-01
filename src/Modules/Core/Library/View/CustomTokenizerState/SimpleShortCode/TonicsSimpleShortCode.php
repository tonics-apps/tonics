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

namespace App\Modules\Core\Library\View\CustomTokenizerState\SimpleShortCode;

use App\Modules\Core\Library\View\CustomTokenizerState\SimpleShortCode\Extensions\Audio;
use App\Modules\Core\Library\View\CustomTokenizerState\SimpleShortCode\Extensions\Caption;
use App\Modules\Core\Library\View\CustomTokenizerState\SimpleShortCode\Extensions\Character;
use App\Modules\Core\Library\View\CustomTokenizerState\SimpleShortCode\Extensions\DMCodeSnippet;
use App\Modules\Core\Library\View\CustomTokenizerState\SimpleShortCode\Extensions\EasyMediaDownload;
use App\Modules\Core\Library\View\CustomTokenizerState\SimpleShortCode\Extensions\TOC;
use App\Modules\Core\Library\View\CustomTokenizerState\SimpleShortCode\Extensions\Video;
use Devsrealm\TonicsTemplateSystem\Content;
use Devsrealm\TonicsTemplateSystem\Loader\TonicsTemplateArrayLoader;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class TonicsSimpleShortCode
{
    private TonicsView|null $init = null;

    /**
     * $overrideSettings example:
     *
     * ```
     * [
     *      'templateLoader' => $templateLoader,
     *      'tokenizerState' => new ...,
     *      'content'        => new Content(),
     *      'handleEOF'      => new ...,
     *      'render'         => new ...,
     * ]
     * ```
     *
     * @param array $overrideSettings
     */
    public function __construct (array $overrideSettings = [])
    {
        ## Tonics View
        $templateLoader = new TonicsTemplateArrayLoader();
        $settings = [
            'templateLoader' => $templateLoader,
            'tokenizerState' => new TonicsSimpleShortCodeTokenizerState(),
            'content'        => new Content(),
            'handleEOF'      => new TonicsSimpleShortCodeHandleEOF(),
            'render'         => new TonicsSimpleShortCodeCustomRenderer(),
        ];
        // Merge user options with default options
        $settings = array_merge($settings, $overrideSettings);
        $view = new TonicsView($settings);
        // clear in-built mode handler
        $view->setModeHandler([]);
        $view->addModeHandler('char', Character::class);
        $view->addModeHandler('caption', Caption::class);
        $view->addModeHandler('dm_code_snippet', DMCodeSnippet::class);
        $view->addModeHandler('audio', Audio::class);
        $view->addModeHandler('video', Video::class);
        $view->addModeHandler('toc', TOC::class);
        $view->addModeHandler('easy_media_download', EasyMediaDownload::class);
        $view->addModeHandler('maxbutton', EasyMediaDownload::class);
        $this->init = $view;
    }

    public function getView (): ?TonicsView
    {
        return $this->init;
    }
}

