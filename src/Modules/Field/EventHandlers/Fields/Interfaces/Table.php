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

namespace App\Modules\Field\EventHandlers\Fields\Interfaces;

use App\Modules\Core\Configs\DatabaseConfig;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class Table implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Table', 'This lets you interface with a table',
            'Interface',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event) {
                return $this->userForm($event, $data);
            },
        );
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     *
     * @return string
     * @throws \Exception
     * @throws \Throwable
     */
    public function settingsForm (OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Interface Table';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';
        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $multiSelect = (isset($data->multiSelect)) ? $data->multiSelect : '0';
        $multiSelect = $event->booleanOptionSelect($multiSelect);

        $tableName = (isset($data->tableName)) ? $data->tableName : '';
        $orderBy = (isset($data->orderBy)) ? $data->orderBy : '';
        $colNameDisplay = (isset($data->colNameDisplay)) ? $data->colNameDisplay : '';
        $colNameValue = (isset($data->colNameValue)) ? $data->colNameValue : '';

        $moreSettings = $event->generateMoreSettingsFrag($data, <<<HTML

<div class="form-group d:flex flex-gap align-items:flex-end">

    <label class="menu-settings-handle-name d:flex width:100% flex-d:column" for="required-$changeID">Multi-Select
        <select name="multiSelect" class="default-selector mg-b-plus-1" id="multiSelect-$changeID">
        $multiSelect
        </select>
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

<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Table Name
            <input id="fieldName-$changeID" name="tableName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$tableName" placeholder="Name of the table, don't add any prefix">
    </label>
    <label class="menu-settings-handle-name" for="inputName-$changeID">OrderBy
            <input id="inputName-$changeID" name="orderBy" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$orderBy" placeholder="Column to order by">
    </label>
</div>

<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Column Name To Display
            <input id="fieldName-$changeID" name="colNameDisplay" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$colNameDisplay" placeholder="Name of the column to display">
    </label>
    <label class="menu-settings-handle-name" for="inputName-$changeID">Column Name for Value
            <input id="inputName-$changeID" name="colNameValue" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$colNameValue" placeholder="This would be used for the display name value">
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
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function userForm (OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Select';

        $keyValue = $event->getKeyValueInData($data, $data->inputName);
        $defaultValue = $keyValue;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $multiSelect = (isset($data->multiSelect)) ? $data->multiSelect : '0';
        $multiple = ($multiSelect === '1') ? 'multiple' : '';

        $tableName = DatabaseConfig::getPrefix() . $data->tableName ?: '';
        if (!table()->isTable($tableName)) {
            throw new \Exception("$tableName is not a valid table name");
        }

        $orderBy = (isset($data->orderBy)) ? $data->orderBy : '';
        $orderBy = table()->pickTable($tableName, [$orderBy]);
        $colNameDisplay = (isset($data->colNameDisplay)) ? $data->colNameDisplay : '';
        $colNameValue = (isset($data->colNameValue)) ? $data->colNameValue : '';
        # Validate colNameValue
        table()->pickTable($tableName, [$colNameValue]);
        # Validate colNameDisplay
        table()->pickTable($tableName, [$colNameDisplay]);

        $colString = "$colNameDisplay";
        if ($colNameDisplay !== $colNameValue) {
            $colString .= ", $colNameValue";
        }

        $rows = null;
        db(onGetDB: function (TonicsQuery $db) use ($tableName, &$rows, $orderBy, $colString) {
            $rows = $db->Select($colString)
                ->From($tableName)
                ->OrderBy($orderBy)
                ->Take(100)
                ->FetchResult();
        });

        $slug = $data->field_slug;
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";

        $choiceFrag = '';
        $error = '';
        foreach ($rows as $value) {
            $key = $value->{$colNameValue};
            $displayValue = $value->{$colNameDisplay};

            $selected = '';
            if (!is_array($defaultValue)) {
                $defaultValue = [$defaultValue];
            }

            if (is_array($defaultValue) && in_array($key, $defaultValue)) {
                $selected = 'selected';
            }

            $choiceFrag .= <<<HTML
<option $selected title="$displayValue" value="$key">$displayValue</option>
HTML;

        }
        $frag .= <<<FORM
<div class="form-group margin-top:0">
$error
<select class="default-selector mg-b-plus-1" name="$inputName" $multiple>
    <option label=" "></option>
    $choiceFrag
</select>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }
}