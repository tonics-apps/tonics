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

namespace App\Modules\Field\EventHandlers\Fields\Modular;

use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\AbstractFieldHandler;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class TemplateHooks extends AbstractFieldHandler
{
    private array $hooksTemplate = [];

    public function fieldBoxName (): string
    {
        return 'TemplateHooks';
    }

    public function fieldBoxDescription (): string
    {
        return 'Template Hook';
    }

    public function fieldBoxCategory (): string
    {
        return static::CATEGORY_MODULAR;
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     *
     * @return string
     * @throws \Throwable
     */
    public function settingsForm (OnFieldMetaBox $event, $data): string
    {
        $field = $this->getField();
        $field->processData($event, [
            'fieldName' => $this->fieldBoxDescription(),
        ]);

        $fieldName = $field->getFieldName();
        $inputName = $field->getInputName();
        $changeID = $field->getFieldChangeIDOnSettingsForm();
        $frag = $field->getTopHTMLWrapper();
        $defaultValue = (isset($data->defaultValue)) ? $data->defaultValue : 'in_main_content';

        $templateName = (isset($data->templateName)) ? $data->templateName : '';

        $moreSettings = $event->generateMoreSettingsFrag($data, <<<HTML
<div class="form-group d:flex flex-gap align-items:flex-end">
    <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="templateName-$changeID">Template Name
        <input id="templateName-$changeID" name="templateName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
        value="$templateName" placeholder="e.g: Modules::Core/Views/Templates/theme">
    </label>
   <label class="menu-settings-handle-name" for="defaultValue-$changeID">Default HookName
     <input name="defaultValue" value="$defaultValue" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray" id="defaultValue-$changeID">
    </label>
</div>
HTML,
        );

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
$moreSettings
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
        $inputName = $this->getField()->getFieldInputName();
        $defaultValue = $this->getField()->getDefaultValue();

        $templateName = (isset($data->templateName)) ? $data->templateName : '';

        if (!isset($this->hooksTemplate[$templateName])) {
            /** @var TonicsView $view */
            $view = view($templateName, [], TonicsView::RENDER_TOKENIZE_ONLY);
            $hooks = $view->getModeStorage('add_hook');
            if (!empty($hooks)) {
                $hooks = array_keys($hooks);
                $this->hooksTemplate[$templateName] = $hooks;
            }
        } else {
            $hooks = $this->hooksTemplate[$templateName];
        }


        $fieldSelectionFrag = '';
        foreach ($hooks as $hook) {
            $fieldSelected = '';
            if ($defaultValue === $hook) {
                $fieldSelected = 'selected';
            }
            $fieldSelectionFrag .= <<<HTML
<option value="$hook" $fieldSelected>$hook</option>
HTML;
        }

        $frag .= <<<HTML
<div class="form-group tonics-field-selection-dropper-form-group margin-top:0 owl">
     <label class="field-settings-handle-name owl" for="fieldSlug-$changeID">Hook Into
     <select name="$inputName" class="default-selector mg-b-plus-1 tonics-field-selection-dropper-select" id="fieldSlug-$changeID">
        $fieldSelectionFrag
     </select>
    </label>
</div>
HTML;
        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }
}