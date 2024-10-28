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

namespace App\Modules\Field\EventHandlers\Fields\Modular;

use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class FieldSelection implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox(
            'FieldSelection',
            'Add a Field',
            'Modular',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event) {
                return $this->userForm($event, $data);
            },
        );
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function settingsForm (OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Field';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $fieldSlug = (isset($data->fieldSlug)) ? $data->fieldSlug : '';
        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $fields = null;
        db(onGetDB: function ($db) use (&$fields) {
            $table = Tables::getTable(Tables::FIELD);
            $fields = $db->run("SELECT * FROM $table");
        });

        $fieldFrag = '';
        foreach ($fields as $field) {
            $uniqueSlug = "$field->field_slug";
            $fieldSelected = ($fieldSlug === $uniqueSlug) ? 'selected' : '';
            $fieldFrag .= <<<HTML
<option value="$uniqueSlug" $fieldSelected>$field->field_name</option>
HTML;
        }

        $group = $event->booleanOptionSelect($data->group ?? '0');
        $toggleable = $event->booleanOptionSelectWithNull($data->toggleable ?? '');
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';

        $moreSettings = $event->generateMoreSettingsFrag($data, <<<HTML
<div class="form-group d:flex flex-gap align-items:flex-end">

    <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="group-$changeID">Group
        <select name="group" class="default-selector mg-b-plus-1" id="group-$changeID">
            $group
        </select>
    </label>
    
    <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="toggleable-$changeID">Toggable
        <select name="toggleable" class="default-selector mg-b-plus-1" id="toggleable-$changeID">
            $toggleable
        </select>
    </label>
    
</div>
HTML,
        );

        $frag .= <<<FORM
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="field-settings-handle-name" for="fieldName-$changeID">Field Name
            <input id="fieldName-$changeID" name="fieldName" type="text" class="field-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
    <label class="field-settings-handle-name" for="inputName-$changeID">Input Name
            <input id="inputName-$changeID" name="inputName" type="text" class="field-name color:black border-width:default border:black placeholder-color:gray"
            value="$inputName" placeholder="(Optional) Input Name">
    </label>
</div>
<div class="form-group">
    <label class="field-settings-handle-name" for="fieldSlug-$changeID">Choose Field
        <select name="fieldSlug" class="default-selector mg-b-plus-1" id="fieldSlug-$changeID">
        $fieldFrag
        </select>
    </label>
</div>
$moreSettings
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Field';
        $keyValue = $event->getKeyValueInData($data, $data->inputName);
        $fieldSlug = (isset($data->fieldSlug) && !empty($keyValue)) ? $keyValue : $data->fieldSlug;

        $htmlFrag = '';
        helper()->garbageCollect(function () use ($fieldSlug, $event, &$htmlFrag) {
            $htmlFrag = FieldConfig::expandFieldWithChildrenFromMetaBox($event, $fieldSlug);
        });

        $isGroup = isset($data->group) && $data->group === '1';
        $isToggleable = null;
        if (isset($data->toggleable) && $data->toggleable !== '') {
            $isToggleable = $data->toggleable === '1';
        }

        if ($isGroup) {
            $frag = $event->_topHTMLWrapper($fieldName, $data, true, function ($isEditorWidgetSettings, $toggle) use ($data, $event) {
                $slug = $data->field_slug ?? '';
                $hash = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
                $inputName = (isset($data->inputName)) ? $data->inputName : '';
                $field_table_slug = (isset($data->_field->main_field_slug)) ? "<input type='hidden' name='main_field_slug' value='{$data->_field->main_field_slug}'>" : '';

                return <<<HTML
<li tabIndex="0" class="width:100% field-builder-items overflow:auto">
            <div $isEditorWidgetSettings role="form" data-widget-form="true" class="widgetSettings flex-d:column menu-widget-information cursor:pointer width:100% {$toggle['div']}">
<input type="hidden" name="field_slug" value="$slug">
$field_table_slug
<input type="hidden" name="field_slug_unique_hash" value="$hash">
<input type="hidden" name="field_input_name" value="$inputName">
HTML;
            });
        } else {
            $frag = $event->_topHTMLWrapper($fieldName, $data, true, toggleUserSettings: $isToggleable);
        }

        $frag .= <<<FieldSelectionDropperFrag
<div style="margin: 0;" class="tonics-field-selected-container">
        <ul style="margin-left: 0; transform: unset; box-shadow: unset;"  class="tonics-field-selected-ul row-col-item-user">
                $htmlFrag
         </ul>
    </div>
FieldSelectionDropperFrag;

        if ($isGroup) {
            $frag .= $event->_bottomHTMLWrapper(function () {
                return "</div></li>";
            });
        } else {
            $frag .= $event->_bottomHTMLWrapper();
        }

        return $frag;
    }

}