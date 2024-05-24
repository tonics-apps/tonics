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
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class CloudAutomations extends FieldSelectionDropper implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('CloudAutomations', 'Cloud Automation Handlers', 'TonicsCloud',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event) {
                return $this->userForm($event, $data);
            },
            handleViewProcessing: function () {},
        );
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Cloud Automations';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
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

        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Cloud Automations';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $slug = $data->field_slug;
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";

        $defaultValue = (isset($data->defaultValue)) ? $data->defaultValue : '';
        $keyValue = $event->getKeyValueInData($data, $data->inputName);
        $defaultValue = $keyValue ?: $defaultValue;

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