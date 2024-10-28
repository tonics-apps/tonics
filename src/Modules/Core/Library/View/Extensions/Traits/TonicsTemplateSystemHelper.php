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

namespace App\Modules\Core\Library\View\Extensions\Traits;

trait TonicsTemplateSystemHelper
{
    # Same as the upper TonicsTemplateSystemHelper, but nested intentionally, so we can keep the namespace for existing code that uses that
    # Between, if you are wondering why this is done, it is because I moved the functionality into the below TonicsTemplateSystemHelper,
    # this way, I can use it for core modes
    use \Devsrealm\TonicsTemplateSystem\Traits\TonicsTemplateSystemHelper;
}