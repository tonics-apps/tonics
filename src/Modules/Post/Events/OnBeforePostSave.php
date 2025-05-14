<?php
/*
 *     Copyright (c) 2022-2025. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Post\Events;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;


/**
 * This could be used to filter the $postData before post save or post update.
 */
class OnBeforePostSave implements EventInterface
{

    private array $data;

    public function __construct(array $postData = [])
    {
        $this->data = $postData;
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @param mixed $field
     *
     * @return mixed
     */
    public function getFieldOption(mixed $field): mixed
    {
        if (is_object($field->field_options)) {
            return $field->field_options;
        } else {
            return json_decode($field->field_options);
        }
    }
}