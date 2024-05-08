<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Field\Events;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnFieldTopHTMLWrapperUserSettings implements EventInterface
{
    private array|\stdClass $data;

    public function __construct(array|\stdClass $data = [])
    {
        $this->data = $data;
    }

    public function event(): static
    {
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array|\stdClass
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array|\stdClass $data): void
    {
        $this->data = $data;
    }
}