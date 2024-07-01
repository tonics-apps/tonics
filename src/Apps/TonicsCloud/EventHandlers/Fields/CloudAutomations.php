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

namespace App\Apps\TonicsCloud\EventHandlers\Fields;

use App\Apps\TonicsCloud\Controllers\ContainerController;
use App\Apps\TonicsCloud\Events\OnAddCloudAutomationEvent;
use App\Apps\TonicsCloud\Interfaces\CloudAutomationInterface;
use App\Modules\Field\EventHandlers\Fields\Modular\FieldSelectionDropper;
use App\Modules\Field\Events\OnFieldMetaBox;

class CloudAutomations extends FieldSelectionDropper
{

    public function fieldBoxName (): string
    {
        return 'CloudAutomations';
    }

    public function fieldBoxDescription (): string
    {
        return 'Cloud Automation Handlers';
    }

    public function fieldBoxCategory (): string
    {
        return self::CATEGORY_TONICS_CLOUD;
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     *
     * @return string
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
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;

    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function userForm (OnFieldMetaBox $event, $data): string
    {
        if (ContainerController::getCurrentControllerMethod() === ContainerController::EDIT_METHOD) {
            return '';
        }

        $onAddCloudAutomationEvent = new OnAddCloudAutomationEvent();
        event()->dispatch($onAddCloudAutomationEvent);

        $frag = $this->getField()->getTopHTMLWrapper();
        $changeID = $this->getField()->getFieldChangeID();
        $inputName = $this->getField()->getFieldInputName();
        $defaultValue = $this->getField()->getDefaultValue();

        $fieldSelectionFrag = '';
        $defaultFieldSlugFrag = '';
        $handlers = $onAddCloudAutomationEvent->getCloudAutomations();

        /** @var CloudAutomationInterface $handler */
        foreach ($handlers as $handler) {
            $fieldSelected = '';
            if ($defaultValue === $handler->name()) {
                $fieldSelected = 'selected';
                $defaultFieldSlugFrag = $event->getFieldData()->generateFieldWithFieldSlug(
                    [$handler->name()],
                    getPostData(),
                )->getHTMLFrag();
            }
            $fieldSelectionFrag .= <<<HTML
<option value="{$handler->name()}" $fieldSelected>{$handler->displayName()}</option>
HTML;
        }

        $fieldSelectDropperFrag = <<<FieldSelectionDropperFrag
<div class="tonics-field-selection-dropper-container">
        <ul style="margin-left: 0; transform: unset; box-shadow: unset;" data-cell_position="1" class="tonics-field-selection-dropper-ul row-col-item-user margin-top:0 owl">
                $defaultFieldSlugFrag
         </ul>
    </div>
FieldSelectionDropperFrag;

        $frag .= <<<HTML
<div class="form-group tonics-field-selection-dropper-form-group margin-top:0 owl">
     <label class="field-settings-handle-name owl" for="fieldSlug-$changeID">Choose Automation
     <select name="$inputName" class="default-selector mg-b-plus-1 tonics-field-selection-dropper-select" id="fieldSlug-$changeID">
        $fieldSelectionFrag
     </select>
    </label>
    $fieldSelectDropperFrag
</div>
HTML;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }
}