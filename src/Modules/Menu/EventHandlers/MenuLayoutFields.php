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

namespace App\Modules\Menu\EventHandlers;

use App\Modules\Field\Events\FieldSelectionDropper\OnAddFieldSelectionDropperEvent;
use App\Modules\Menu\Data\MenuData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class MenuLayoutFields implements HandlerInterface
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
            'menu-element' => [
                'open'     => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $event->autoOpenField($field, '');
                },
                'close'    => fn($field, $event) => $event->autoCloseField($field),
                'children' => function (OnAddFieldSelectionDropperEvent $event) {
                    $event->hookIntoFieldDataKey('field_input_name', [
                        'menu' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $element = $event->getCurrentOpenElement();
                            $menu = $event->accessFieldData($field, 'menu');
                            $element->_childrenFrag = (new MenuData())->getMenuFrontendFragment($menu);
                        },
                    ]);
                },
            ],
        ];
    }
}