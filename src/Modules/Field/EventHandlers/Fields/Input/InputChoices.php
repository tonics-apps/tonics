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

use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class InputChoices implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('Choices', 'A field for selecting and or deselecting single value out of many',
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Choice';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $choiceType = (isset($data->choiceType)) ? $data->choiceType : 'checkbox';
        $choices = (isset($data->choices)) ? helper()->htmlSpecChar($data->choices) : '';
        $choiceTypes = [
            'Checkbox' => 'checkbox',
            'Radio'    => 'radio',
            'Color'    => 'color',
        ];
        $choiceFrag = '';
        foreach ($choiceTypes as $choiceK => $choiceV) {
            if ($choiceV === $choiceType) {
                $choiceFrag .= <<<HTML
<option value="$choiceV" selected>$choiceK</option>
HTML;
            } else {
                $choiceFrag .= <<<HTML
<option value="$choiceV">$choiceK</option>
HTML;
            }
        }
        $defaultValue = (isset($data->defaultValue)) ? $data->defaultValue : '';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
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

<div class="form-group">
     <label class="menu-settings-handle-name" for="choice-type-$changeID">Choice Type
     <select name="choiceType" class="default-selector mg-b-plus-1" id="choice-type-$changeID">
        $choiceFrag
     </select>
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="choices-$changeID">Choices (format: v1:k1, v2:k2), (uses key as value if kv is empty)
     <textarea name="choices" id="choices-$changeID" placeholder="Key and Value should be separated by comma">$choices</textarea>
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="choice-default-value-$changeID">Default Value
            <input id="choice-default-value-$changeID" name="defaultValue" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$defaultValue" placeholder="Enter the key to use as default, e.g k1">
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Choice';
        $selectedChoices = $event->getKeyValueInData($data, $data->inputName);
        if (empty($selectedChoices)) {
            $selectedChoices = $event->getKeyValueInData($data, "$data->inputName[]");
        }

        if (!is_array($selectedChoices)) {
            $selectedChoices = [$selectedChoices];
        }

        $selectedChoices = array_combine($selectedChoices, $selectedChoices);

        $textType = (isset($data->choiceType)) ? $data->choiceType : 'checkbox';
        $isColor = false;
        if ($textType === 'color') {
            $textType = 'radio';
            $isColor = true;
        }
        $defaultValue = (isset($data->defaultValue)) ? $data->defaultValue : '';
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $choices = (isset($data->choices)) ? $data->choices : '';
        $choiceKeyValue = [];
        if (!empty($choices)) {
            $choices = explode(',', $choices);
        }

        if (is_array($choices)) {
            foreach ($choices as $choice) {
                $choice = explode(':', $choice);
                $key = $choice[0] ?? '';
                $value = $choice[1] ?? '';

                # Check if key or value is not only whitespace, if it is not only whitespace, we strip all whitespaces,
                # if it contains only whitespace, it is left alone
                if (!ctype_space($key)) {
                    $key = preg_replace('/\s+/', '', $key);
                }

                if (!ctype_space($value)) {
                    $value = preg_replace('/\s+/', '', $value);
                }

                $choiceKeyValue[$key] = $value;
            }
        }

        $slug = $data->field_slug;
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";

        $choiceFrag = '';
        foreach ($choiceKeyValue as $key => $value) {

            $selected = '';
            if ($key === $defaultValue) {
                $selected = 'checked';
            } elseif (isset($selectedChoices[$key])) {
                $selected = 'checked';
            }

            if ($isColor) {
                $color = trim($key);
                $choiceFrag .= <<<LABEL
<li data-draggable-ignore>
    <label  title="Color $value">
        <input type="$textType" title="$value" name="{$inputName}[]" value="$color" $selected>
        <input disabled title="Color $value" value="$color" type="color" class="pointer-events:none">
    </label>
</li>
LABEL;
            } else {
                $choiceFrag .= <<<HTML
<li data-draggable-ignore>
    <label>
        <input $selected type="$textType" title="$value" name="{$inputName}[]" value="$key">
        $value
    </label>
</li>
HTML;
            }

        }

        $class = 'list:style:none margin-top:0 max-height:500px overflow-x:auto';
        if ($isColor) {
            $class .= " d:flex flex-d:row flex-wrap:wrap";
        }
        $frag .= <<<FORM
<div data-draggable-ignore class="form-group margin-top:0">
    <ul style="margin-left: 0;" class="$class">
        $choiceFrag
    </ul>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }
}