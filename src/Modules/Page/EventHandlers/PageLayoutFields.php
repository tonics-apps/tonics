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

namespace App\Modules\Page\EventHandlers;

use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Field\Events\FieldSelectionDropper\OnAddFieldSelectionDropperEvent;
use App\Modules\Page\Services\PageService;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class PageLayoutFields implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnAddFieldSelectionDropperEvent */
        $event->addFields('tonicsPageBuilderLayoutSelector',
            $this->fields(),
        );

        /** @var $event OnAddFieldSelectionDropperEvent */
        $event->addFields('tonicsPageBuilderFieldSelector',
            $this->fields(),
        );
    }

    /**
     * @return array[]
     */
    private function fields (): array
    {
        return [
            'page-inheritance' => [
                'children' => function (onAddFieldSelectionDropperEvent $event) {
                    $event->hookIntoFieldDataKey('field_input_name', [
                        'pages-inheritance-input' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $pages = $event->accessFieldData($field, 'pages-inheritance-input', '', true);
                            if (!empty($pages) && is_iterable($pages)) {
                                $pages = PageService::getPagesBy($pages) ?? [];
                                $event->handlePageInheritance($pages);
                            }
                        },
                    ]);
                },
                'close'    => fn($field, $event) => $event->autoCloseField($field),
            ],
            'page-import'      => [
                'children' => function (onAddFieldSelectionDropperEvent $event) {

                    $event->hookIntoFieldDataKey('field_input_name', [

                        'hookPageImport' => function ($field, OnAddFieldSelectionDropperEvent $event) {

                            $fresh = $event->processLogicFresh($field->_children ?? [], function (OnAddFieldSelectionDropperEvent $ev) { $ev->setCurrentOpenElement(new \stdClass()); });
                            $event->removeFieldChildren($field);
                            $event->getCOE()->__storages = $fresh->storages();
                            $event->getCOE()->__caches = $fresh->getCache();

                            $fieldImport = $event->getCOE()->__pageImportField;

                            $pages = $event->accessFieldData($fieldImport, 'pages-import-input', '', true);

                            if (!empty($pages)) {

                                $pages = PageService::getPagesBy([$pages]) ?? [];
                                $layoutSelectors = FieldConfig::LayoutSelectorsForPages($pages);

                                $freshEvent = $event->processLogicFresh($layoutSelectors, function (OnAddFieldSelectionDropperEvent $ev) use ($layoutSelectors, $event) {
                                    $ev->setStorage($event->getCOE()->__storages ?? [])->setCache($event->getCOE()->__caches ?? []);
                                }, $event::OUTPUT_ONLY_TYPE);

                                $event->setOutputInlineStyle($freshEvent->getOutputInlineStyle())
                                    ->storageAdd($event::HOOK_NAMES_STORAGE_KEY, $freshEvent->storageGet($event::HOOK_NAMES_STORAGE_KEY));

                                return $freshEvent->getOutput();
                            }

                            return '';
                        },

                        'pages-import-input' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $event->getCOE()->__pageImportField = $field;
                        },
                    ]);

                },
                'close'    => fn($field, $event) => $event->autoCloseField($field),
            ],
        ];
    }
}