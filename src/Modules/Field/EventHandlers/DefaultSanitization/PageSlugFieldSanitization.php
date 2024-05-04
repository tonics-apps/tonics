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

namespace App\Modules\Field\EventHandlers\DefaultSanitization;

use App\Modules\Field\Events\OnAddFieldSanitization;
use App\Modules\Field\Interfaces\FieldValueSanitizationInterface;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class PageSlugFieldSanitization extends DefaultSanitizationAbstract implements HandlerInterface, FieldValueSanitizationInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnAddFieldSanitization */
        $event->addField($this);
    }

    public function sanitizeName(): string
    {
        return 'PageSlug';
    }

    /**
     * @param $value
     * @return string|array
     * @throws \Exception
     */
    public function sanitize($value): string|array
    {
        return helper()->slugForPage($value, '-');
    }
}