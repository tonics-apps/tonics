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

namespace App\Apps\TonicsAI\EventHandlers;

use App\Apps\TonicsToc\Controller\TonicsTocController;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\FieldTemplateFileInterface;

class TonicsAIOpenAIImageFieldHandler implements FieldTemplateFileInterface
{

    /**
     * @throws \Exception
     */
    public function handleFieldLogic(OnFieldMetaBox $event = null, $fields = null): string
    {
        if (isset($fields[0]) && $fields[0]->main_field_slug === $this->fieldSlug()){
            dd($fields[0]);
            return $this->getResult($fields[0]?->field_data);
        }
        return '';
    }

    public function fieldSlug(): string
    {
        return 'app-tonicsai-openai-image';
    }

    public function name(): string
    {
        return 'Image';
    }

    public function canPreSaveFieldLogic(): bool
    {
        return true;
    }

    /**
     * @throws \Exception
     */
    public function getResult($fieldData): string
    {

    }
}