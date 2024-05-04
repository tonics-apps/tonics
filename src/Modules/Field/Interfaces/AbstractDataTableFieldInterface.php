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

namespace App\Modules\Field\Interfaces;

use App\Modules\Field\Events\OnFieldMetaBox;

abstract class AbstractDataTableFieldInterface
{
    private ?OnFieldMetaBox $event = null;
    private $data = null;

    public function header(): array
    {
        return [
            'type' => 'TEXT',
            'title' => 'Unknown Header',
            'minmax' => '150px, 1fr',
        ];
    }

    abstract public function renderDataTableView(): string;

    /**
     * @return OnFieldMetaBox|null
     */
    public function getEvent(): ?OnFieldMetaBox
    {
        return $this->event;
    }

    /**
     * @param OnFieldMetaBox|null $event
     * @return AbstractDataTableFieldInterface
     */
    public function setEvent(?OnFieldMetaBox $event): AbstractDataTableFieldInterface
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param null $data
     */
    public function setData($data): AbstractDataTableFieldInterface
    {
        $this->data = $data;
        return $this;
    }


}