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

namespace App\Modules\Field\Interfaces;

use App\Modules\Field\Data\Field;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

abstract class AbstractFieldHandler implements HandlerInterface
{
    const CATEGORY_TOOL         = 'Tool';
    const CATEGORY_INPUT        = 'Input';
    const CATEGORY_POST         = 'Post';
    const CATEGORY_PAGE         = 'Page';
    const CATEGORY_CUSTOMER     = 'Customer';
    const CATEGORY_TRACK        = 'Track';
    const CATEGORY_MEDIA        = 'Media';
    const CATEGORY_MODULAR      = 'Modular';
    const CATEGORY_MENU         = 'Menu';
    const CATEGORY_WIDGET       = 'Widget';
    const CATEGORY_INTERFACE    = 'Interface';
    const CATEGORY_TONICS_CLOUD = 'TonicsCloud';
    private ?Field $field = null;

    /**
     * @return string
     */
    abstract public function fieldBoxName (): string;

    /**
     * @return string
     */
    abstract public function fieldBoxDescription (): string;

    /**
     * @return string
     */
    abstract public function fieldBoxCategory (): string;

    public function fieldScriptPath (): string
    {
        return '';
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     *
     * @return string
     */
    abstract public function settingsForm (OnFieldMetaBox $event, $data): string;

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     *
     * @return string
     */
    abstract public function userForm (OnFieldMetaBox $event, $data): string;

    public function viewData (OnFieldMetaBox $event, $data) {}

    /**
     * @throws \Exception
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $this->field = new Field($event);
        $event->addFieldBox(
            $this->fieldBoxName(),
            $this->fieldBoxDescription(),
            $this->fieldBoxCategory(),
            $this->fieldScriptPath(),
            settingsForm: function ($data) use ($event) {
                $this->field->processData($event);
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event) {
                $this->field->processData($event);
                return $this->userForm($event, $data);
            },
        );
    }

    public function getField (): ?Field
    {
        return $this->field;
    }

    public function setField (?Field $field): void
    {
        $this->field = $field;
    }
}