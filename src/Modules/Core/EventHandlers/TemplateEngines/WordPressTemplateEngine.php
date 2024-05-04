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

namespace App\Modules\Core\EventHandlers\TemplateEngines;

use App\Modules\Core\Library\View\CustomTokenizerState\WordPress\WordPressShortCode;
use App\Modules\Core\Library\View\CustomTokenizerState\WordPress\WordPressWPContentURL;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class WordPressTemplateEngine implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        $wordpressShortCode = new WordPressShortCode();
        $wordpressWPContentURL = new WordPressWPContentURL();
        $event->addTemplateEngine('WordPressShortCode', $wordpressShortCode->getView());
        $event->addTemplateEngine('WordPressWPContentURL', $wordpressWPContentURL->getView());
    }
}