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

namespace App\Modules\Field\EventHandlers\Fields\Input;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\AbstractFieldHandler;

class InputColorPicker extends AbstractFieldHandler
{

    /**
     * @return string
     */
    public function fieldBoxName (): string
    {
        return 'ColorPicker';
    }

    /**
     * @return string
     */
    public function fieldBoxDescription (): string
    {
        return 'Input Color Picker';
    }

    /**
     * @return string
     */
    public function fieldBoxCategory (): string
    {
        return static::CATEGORY_INPUT;
    }

    /**
     * @return string
     */
    public function fieldScriptPath (): string
    {
        return AppConfig::getModuleAsset('Core', 'js/tools/ColorIs.min.js');
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function settingsForm (OnFieldMetaBox $event, $data = null): string
    {
        $field = $this->getField();
        $field->processData($event, [
            'fieldName' => $this->fieldBoxDescription(),
        ]);

        $fieldName = $field->getFieldName();
        $inputName = $field->getInputName();
        $changeID = $field->getFieldChangeIDOnSettingsForm();
        $frag = $field->getTopHTMLWrapper();
        $defaultValue = (isset($data->defaultValue)) ? $data->defaultValue : '#000000';

        $frag .= <<<FORM
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Field Name
            <input id="fieldName-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
    <label class="menu-settings-handle-name" for="inputName-$changeID">Input Name
            <input id="inputName-$changeID" name="inputName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$inputName" placeholder="(Optional) Input Name">
    </label>
</div>

<div class="form-group d:flex flex-d:column">
     <label class="menu-settings-handle-name" for="defaultValue-$changeID">Default Color </label>
     <input name="defaultValue" value="$defaultValue" type="text" data-coloris class="menu-name color:black border-width:default border:black placeholder-color:gray" id="defaultValue-$changeID">
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }


    /**
     * @param OnFieldMetaBox $event
     * @param $data
     *
     * @return string
     * @throws \Throwable
     */
    public function userForm (OnFieldMetaBox $event, $data): string
    {
        $frag = $this->getField()->getTopHTMLWrapper();
        $changeID = $this->getField()->getFieldChangeID();
        $inputName = $this->getField()->getInputName();
        $defaultValue = $this->getField()->getDefaultValue();

        $frag .= <<<FORM
<div data-draggable-ignore class="form-group">
     <label class="menu-settings-handle-name" for="inputColor-$changeID">Choose Color
     <input name="$inputName" value="$defaultValue" type="text" data-coloris class="menu-name color:black border-width:default border:black placeholder-color:gray" id="inputColor-$changeID">
    </label>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }
}