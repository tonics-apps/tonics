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

namespace App\Apps\TonicsToc\EventHandler;

use App\Apps\TonicsToc\Controller\TonicsTocController;
use App\Modules\Field\Events\FieldSelectionDropper\OnAddFieldSelectionDropperEvent;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\FieldTemplateFileInterface;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TonicsTocFieldHandler implements FieldTemplateFileInterface, HandlerInterface
{

    public function handleEvent (object $event): void
    {
        /** @var $event OnAddFieldSelectionDropperEvent */
        $event->hookIntoFieldDataKey('field_input_name', [
            'toc_label' => [
                'open' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $event->addProcessedFragToOpenElement($this->getTocResult($field?->field_options));
                },
            ],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function handleFieldLogic (OnFieldMetaBox $event = null, $fields = null): string
    {
        if (isset($fields[0]) && $fields[0]->main_field_slug === 'app-tonicstoc') {
            return $this->getTocResult($fields[0]?->field_data);
        }
        return '';
    }

    public function fieldSlug (): string
    {
        return 'app-tonicstoc';
    }

    public function name (): string
    {
        return 'Tonics Table Of Content';
    }

    public function canPreSaveFieldLogic (): bool
    {
        return true;
    }

    /**
     * @throws \Exception
     */
    public function getTocResult ($fieldData): string
    {
        if (is_array($fieldData)) {
            $fieldData = json_decode(json_encode($fieldData));
        }

        $settings = TonicsTocController::getSettingsData();
        $result = '';
        if (isset($fieldData->{'tableOfContentData'})) {
            if ($fieldData->{'tableOfContentData'}->headersFound >= $settings['toc_trigger']) {
                $settings['toc_label'] = (empty($fieldData->{'toc_label'})) ? $settings['toc_label'] : $fieldData->{'toc_label'};
                foreach ($fieldData->{'tableOfContentData'}->tree as $item) {
                    $result .= $item->data;
                }
            }
        }
        $tocClass = trim($settings['toc_class'], '.');
        return "<div class='{$tocClass}'><ul class='tonics-toc-ul'><{$settings['toc_label_tag']} class='tonics-toc-label-tag-class'> {$settings['toc_label']} </{$settings['toc_label_tag']}> $result </ul></div>";
    }
}