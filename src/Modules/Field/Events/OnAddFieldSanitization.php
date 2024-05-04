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

use App\Modules\Field\Interfaces\FieldValueSanitizationInterface;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnAddFieldSanitization implements EventInterface
{
    private array $fieldsSanitization = [];

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    public function addField(FieldValueSanitizationInterface $fieldValueSanitization): static
    {
        $this->fieldsSanitization[$fieldValueSanitization->sanitizeName()] = $fieldValueSanitization;
        return $this;
    }

    /**
     * @return array
     */
    public function getFieldsSanitization(): array
    {
        return $this->fieldsSanitization;
    }

    /**
     * @param array $fieldsSanitization
     * @return OnAddFieldSanitization
     */
    public function setFieldsSanitization(array $fieldsSanitization): OnAddFieldSanitization
    {
        $this->fieldsSanitization = $fieldsSanitization;
        return $this;
    }
}