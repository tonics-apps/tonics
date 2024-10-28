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

class OnComparedSortedFieldCategories implements EventInterface
{
    private array $fieldCategories = [];

    /**
     * @param array $fieldCategories
     */
    public function __construct (array $fieldCategories = [])
    {
        $this->fieldCategories = $fieldCategories;
    }

    /**
     * @inheritDoc
     */
    public function event (): static
    {
        return $this;
    }

    /**
     * @return array
     */
    public function getFieldCategories (): array
    {
        return $this->fieldCategories;
    }

    /**
     * @param array $fieldCategories
     *
     * @return $this
     */
    public function setFieldCategories (array $fieldCategories): OnComparedSortedFieldCategories
    {
        $this->fieldCategories = $fieldCategories;
        return $this;
    }
}