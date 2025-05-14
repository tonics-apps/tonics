<?php
/*
 *     Copyright (c) 2025. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Post\EventHandlers;

use App\Modules\Post\Events\OnBeforePostSave;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class ProcessPostCustomMeta implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /**
         * @var OnBeforePostSave $event
         */
        $customMetaName = '_CustomMeta_';
        if (isset($event->getData()['field_settings'])) {
            $data = $event->getData();
            $fieldSettings = json_decode($data['field_settings']);

            if (isset($fieldSettings->_fieldDetails)) {

                $fields = json_decode($fieldSettings->_fieldDetails);
                $metaName = $customMetaName;

                foreach ($fields as $field) {
                    if ($field->field_input_name === 'customMetaName') {
                        $metaName = $metaName . $event->getFieldOption($field)?->customMetaName;
                    }
                    if ($field->field_input_name === 'customMetaValue') {
                        $fieldSettings->{$metaName} = $event->getFieldOption($field)?->customMetaValue;
                        $metaName = $customMetaName; # Reset
                    }
                }
            }

            $data['field_settings'] = json_encode($fieldSettings);
            $event->setData($data);
        }

    }
}