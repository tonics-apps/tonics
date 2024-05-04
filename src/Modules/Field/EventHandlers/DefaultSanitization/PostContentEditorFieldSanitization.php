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

class PostContentEditorFieldSanitization extends DefaultSanitizationAbstract implements HandlerInterface, FieldValueSanitizationInterface
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
        return 'PostContentEditor';
    }

    /**
     * @param $value
     * @return mixed
     * @throws \Exception
     */
    public function sanitize($value): mixed
    {
        $inputName =  (isset($this->getData()->inputName)) ? $this->getData()->inputName : "";
        $newValue = '';
        if (isset(getPostData()[$inputName])){
            $postContent = getPostData()[$inputName];
            $postContent = json_decode($postContent, true);
            if (is_array($postContent)) {
                $fieldData = $this->getEvent()->getFieldData();
                foreach ($postContent as $field) {
                    if ($field['raw'] === false) {
                        $postData = (is_string($field['postData'])) ? $field['postData'] : '';
                        $newValue .= $fieldData->wrapFieldsForPostEditor($postData, $fieldData->previewFragForFieldHandler($postData));
                    } else {
                        if (isset($field['content'])) {
                            $newValue .= $field['content'];
                        }
                    }
                }
                $value = $newValue;
            }
        }

        return $value;
    }
}