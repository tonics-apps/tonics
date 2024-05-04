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

namespace App\Modules\Field\Events;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnEditorFieldSelection implements EventInterface
{
    private array $fields = [];
    private int $fieldID = 0;

    const CATEGORY_TOOL = 'Tool';
    const CATEGORY_GENERAL = 'General';
    const CATEGORY_StructuredData = 'Structured Data';

    /**
     * @inheritDoc
     */
    public function event(): static
    {
       return $this;
    }

    public function addField(string $name, string $slug, string $icon = null, string $category = self::CATEGORY_GENERAL): static
    {
        $this->fields[$category][] = [
            'id' => $this->fieldID,
            'name' => $name,
            'slug' => $slug,
            'icon' => $icon,
            'category' => $category,
        ];

        ++$this->fieldID;
        return $this;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        foreach ($this->fields as $fieldCategory){
            usort($fieldCategory, function ($a, $b){
                return strcasecmp($a['name'], $b['name']);
            });
        }
        return $this->fields;
    }
}