<?php

namespace App\Modules\Field\EventHandlers\Fields\Modular;

use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class RowColumn implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox(
            'RowColumn',
            'Add an Unlimited Number of Row or Column',
            'Modular',
            '/js/views/field/native/script.js',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            }, userForm: function ($data) use ($event) {
            return $this->userForm($event, $data);
        },
            handleViewProcessing: function ($data) use ($event) {
                return $this->viewFrag($event, $data);
            }
        );
    }

    /**
     * @throws \Exception
     */
    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'RowColumn';
        $row = 1;
        $column = 1;
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $elementName = (isset($data->elementName)) ? $data->elementName : '';
        $attributes = (isset($data->attributes)) ? helper()->htmlSpecChar($data->attributes) : '';
        if (isset($data->row)) {
            $row = $data->row;
        }

        if (isset($data->column)) {
            $column = $data->column;
        }

        $frag = '';
        if (isset($data->_topHTMLWrapper)) {
            $topHTMLWrapper = $data->_topHTMLWrapper;
            $slug = $data->_field->field_name ?? null;
            $frag = $topHTMLWrapper($fieldName, $slug);
        }

        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';
        $handleViewProcessingFrag = $event->handleViewProcessingFrag((isset($data->handleViewProcessing)) ? $data->handleViewProcessing : '');
        $frag .= <<<HTML
<div class="row-col-parent owl" data-depth="0">
    <div class="form-group d:flex flex-gap align-items:flex-end">
         <label class="menu-settings-handle-name" for="widget-name-$changeID">Field Name
                <input id="widget-name-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
                value="$fieldName" placeholder="Field Name">
        </label>
     <label class="menu-settings-handle-name" for="inputName-$changeID">Input Name
            <input id="inputName-$changeID" name="inputName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$inputName" placeholder="(Optional) Input Name">
    </label>
    </div>
    <div class="form-group d:flex flex-d:row flex-gap:small rowColumn">
        <label class="menu-settings-handle-name" for="widget-row-$changeID">Row
            <input id="widget-row-$changeID" name="row" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray" data-name="row" 
            value="$row" placeholder="Overwrite the widget name">
        </label>
           <label class="menu-settings-handle-name" for="widget-column-$changeID">Column
            <input id="widget-column-$changeID" name="column" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray" data-name="column" 
            value="$column">
        </label>
    </div>
    <div class="form-group">
     <label class="menu-settings-handle-name" for="elementName-$changeID">Element Name
            <input id="elementName-$changeID" name="elementName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$elementName" placeholder="div, section, etc">
    </label>
</div>
    <div class="form-group">
      <label class="menu-settings-handle-name" for="element-attributes-$changeID">Element Attributes
            <input id="element-attributes-$changeID" name="attributes" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$attributes" placeholder="e.g class='class-name' id='id-name' or any attributes">
    </label>
</div>
<div class="form-group">
     <label class="menu-settings-handle-name" for="handleViewProcessing-$changeID">Automatically Handle View Processing
     <select name="handleViewProcessing" class="default-selector mg-b-plus-1" id="handleViewProcessing-$changeID">
        $handleViewProcessingFrag
     </select>
    </label>
</div>
    <div style="--row:$row; --column:$column;" class="cursor:pointer form-group d:grid flex-gap:small overflow-x:auto overflow-y:auto rowColumnItemContainer grid-template-rows grid-template-columns">
HTML;

        $cell = $row * $column;
        if (isset($data->_field)) {
            for ($i = 1; $i <= $cell; $i++) {
                $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';
                $frag .= <<<HTML
<ul style="margin-left: 0; transform: unset; box-shadow: unset;" class="row-col-item">
     <div class="form-group">
      <label class="menu-settings-handle-name" for="cell-select-$changeID">Select & Choose Field
        <input id="cell-select-$changeID" type="checkbox" name="cell">
      </label>
     </div>
HTML;
                if (isset($data->_field->_children)) {
                    foreach ($data->_field->_children as $child) {
                        $childCellNumber = (isset($child->field_options->{$child->field_name . "_cell"}))
                            ? (int)$child->field_options->{$child->field_name . "_cell"}
                            : $i;

                        if ($childCellNumber === $i) {
                            if (isset($child->field_options)) {
                                $child->field_options->{"_field"} = $child;
                                $child->field_options->{"_topHTMLWrapper"} = $data->_topHTMLWrapper;
                                $child->field_options->{"_bottomHTMLWrapper"} = $data->_bottomHTMLWrapper;
                            }
                            $frag .= $event->getSettingsForm($child->field_name, $child->field_options ?? null);
                        }
                    }
                }

                $frag .= <<<HTML
</ul>
HTML;
            }
        } else {
            $frag .= <<<HTML
<ul style="margin-left: 0; transform: unset; box-shadow: unset;" class="row-col-item">
     <div class="form-group">
      <label class="menu-settings-handle-name" for="cell-select-$changeID">Select & Choose Field
        <input id="cell-select-$changeID" type="checkbox" name="cell">
      </label>
     </div>
HTML;
        }
        $frag .= <<<HTML
    </div>
</div>
HTML;
        if (isset($data->_bottomHTMLWrapper)) {
            $frag .= $data->_bottomHTMLWrapper;
        }

        return $frag;

    }

    /**
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'RowColumn';
        $row = 1;
        $column = 1;
        if (isset($data->row)) {
            $row = $data->row;
        }

        if (isset($data->column)) {
            $column = $data->column;
        }

        $frag = '';
        if (isset($data->_topHTMLWrapper)) {
            $topHTMLWrapper = $data->_topHTMLWrapper;
            $slug = $data->_field->field_name ?? null;
            $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
            $frag = $topHTMLWrapper($fieldName, $slug, $changeID);
        }

        $frag .= <<<HTML
<div class="row-col-parent owl" data-depth="0">
    <div style="--row:$row; --column:$column;" class="cursor:pointer form-group d:grid flex-gap:small overflow-x:auto overflow-y:auto rowColumnItemContainer grid-template-rows grid-template-columns">
HTML;
        $cell = $row * $column;
        for ($i = 1; $i <= $cell; $i++) {
            if (!isset($data->_field->_children)) {
                continue;
            }
            $frag .= <<<HTML
<ul style="margin-left: 0; transform: unset; box-shadow: unset;" class="row-col-item">
HTML;
            if (isset($data->_field->_children)) {
                foreach ($data->_field->_children as $child) {
                    $childCellNumber = (isset($child->field_options->{$child->field_name . "_cell"}))
                        ? (int)$child->field_options->{$child->field_name . "_cell"}
                        : $i;

                    if ($childCellNumber === $i) {
                        if (isset($child->field_options)) {
                            $child->field_options->{"_field"} = $child;
                            $child->field_options->{"_topHTMLWrapper"} = $data->_topHTMLWrapper;
                            $child->field_options->{"_bottomHTMLWrapper"} = $data->_bottomHTMLWrapper;
                        }
                        $frag .= $event->getUsersForm($child->field_name, $child->field_options ?? null);
                    }
                }
            }

            $frag .= <<<HTML
</ul>
HTML;
        }

        $frag .= <<<HTML
    </div>
</div>
HTML;
        if (isset($data->_bottomHTMLWrapper)) {
            $frag .= $data->_bottomHTMLWrapper;
        }

        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function viewFrag(OnFieldMetaBox $event, $data): string
    {
        $frag = '';
        $elementName = strtolower($data->elementName);
        if (isset($data->handleViewProcessing) && $data->handleViewProcessing === '1'){
            if (key_exists($elementName, helper()->htmlTags())) {
                $frag .= <<<HTML
<$elementName 
HTML;
                if (!empty($data->attributes)) {
                    $attributes = $event->flatHTMLTagAttributes($data->attributes);
                    $frag .= $attributes;
                }
                $frag .= ">";

            if (isset($data->_field->_children)) {
                foreach ($data->_field->_children as $child) {
                    if (isset($child->field_options)) {
                        $child->field_options->{"_field"} = $child;
                    }
                    $frag .= $event->getViewProcessingFrag($child->field_name, $child->field_options ?? null);
                }
            }
                $frag .= <<<HTML
</$elementName>
HTML;
            }
        }

        return $frag;
    }
}