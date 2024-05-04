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

namespace App\Modules\Core\Library;

abstract class DefaultFieldEventAbstract
{
    private array $fieldSlug = [];
    private array $hiddenFieldSlug = [];

    public function addDefaultField($slug, bool $hideOnForm = false): static
    {
        if ($hideOnForm){
            $this->hiddenFieldSlug[] = $slug;
        } else {
            $this->fieldSlug[] = $slug;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    /**
     * This gets the field slug but not the hidden slug
     * @return array
     */
    public function getFieldSlug(): array
    {
        return $this->fieldSlug;
    }

    /**
     * This gets the field slug (including the hidden slug)
     * @return array
     */
    public function getAllFieldSlug(): array
    {
        return [...$this->fieldSlug, ...$this->hiddenFieldSlug];
    }

    /**
     * @param array $fieldSlug
     * @return static
     */
    public function setFieldSlug(array $fieldSlug): static
    {
        $this->fieldSlug = $fieldSlug;
        return $this;
    }

    /**
     * @return array
     */
    public function getHiddenFieldSlug(): array
    {
        return $this->hiddenFieldSlug;
    }

    /**
     * @param array $hiddenFieldSlug
     */
    public function setHiddenFieldSlug(array $hiddenFieldSlug): void
    {
        $this->hiddenFieldSlug = $hiddenFieldSlug;
    }
}