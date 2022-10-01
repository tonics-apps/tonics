<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\EventHandlers\Fields\Modular;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class RowColumnRepeater implements HandlerInterface
{
    private array $repeaters = [];
    private array $nonRepeaters = [];

    private array $oldPostData = [];
    private array $repeaterButton = [];
    private array $childStacks = [];

    private array $fieldsSorted = [];

    private array $toTree = [];
    private array $treeStack = [];
    private bool $inItem = false;
    private string $completeFrag = '';

    private array $repeaterTree = [];

    private array $depthCollector = [];

    private ?int $lastDepth = null;
    private bool $breakLoopBackward = false;

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        $script = AppConfig::getModuleAsset('Core', '/js/views/field/native/script.js');
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox(
            'RowColumnRepeater',
            'A DataTable Repeater Field',
            'Modular',
            $script,
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            }, userForm: function ($data) use ($event) {

            return $this->userForm($event, $data);
        },
            handleViewProcessing: function ($data) use ($event) {
                $this->viewData($event, $data);
            }
        );
    }

    /**
     * @throws \Exception
     */
    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'RowColumnRepeater';
        $row = 1;
        $column = 1;
        $inputName = (isset($data->inputName)) ? $data->inputName : '';

        if (isset($data->row)) {
            $row = $data->row;
        }

        if (isset($data->column)) {
            $column = $data->column;
        }

        $gridTemplateCol = $data->grid_template_col ?? '';
        $repeat_button_text = $data->repeat_button_text ?? 'Repeat Section';

        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';

        $more = <<<HTML
<div class="form-group">
     <label class="menu-settings-handle-name" for="group-$changeID">Repeat Button Text
     <input id="widget-name-$changeID" name="repeat_button_text" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$repeat_button_text" placeholder="Enter Repeat Button Text">
    </label>
</div>
HTML;

        $gridTemplateColFrag = '';
        if (isset($data->grid_template_col)) {
            $gridTemplateColFrag = " grid-template-columns: {$data->grid_template_col};";
        }

        $frag .= <<<HTML
<div class="row-col-parent owl" data-depth="0">
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="widget-name-$changeID">Field Name
            <input id="widget-name-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
 <label class="menu-settings-handle-name" for="inputName-$changeID">Input Name
        <input id="inputName-$changeID" name="inputName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
        value="$inputName" placeholder="Input Name">
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
    </label>
       <label class="menu-settings-handle-name" for="widget-column-$changeID">Grid Template Col
        <input id="widget-column-$changeID" name="grid_template_col" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray" data-name="grid_template_col" 
        value="$gridTemplateCol">
    </label>
</div>
{$event->generateMoreSettingsFrag($data, $more)}
    <div style="--row:$row; --column:$column; $gridTemplateColFrag" class="cursor:pointer form-group d:grid flex-gap:small overflow-x:auto overflow-y:auto rowColumnItemContainer grid-template-rows grid-template-columns">
HTML;

        $cell = $row * $column;
        if (isset($data->_field)) {
            for ($i = 1; $i <= $cell; $i++) {
                $changeID = helper()->randString(10);

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
     <div class="form-group d:flex flex-d:column flex-gap:small">
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

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;

    }

    /**
     * @param $data
     * @return void
     */
    private function unnestRepeater($data): void
    {
        if ($data->field_slug === 'modular_rowcolumnrepeater') {
            $this->repeaters[$data->field_slug_unique_hash] = $data;
            if (isset($data->_field->_children)) {
                foreach ($data->_field->_children as $child) {
                    $this->unnestRepeater($child->field_options);
                }
            }
        } else {
            $this->nonRepeaters[$data->field_slug_unique_hash] = $data;
        }
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @return string
     * @throws \Exception
     */
    private function repeatersButton(OnFieldMetaBox $event, $data): string
    {
        return $this->handleUserFormFrag($event, $data,
            function ($field) use ($event) {
                $frag = '';
                if ($field->field_slug === 'modular_rowcolumnrepeater') {
                    $frag = $this->repeatersButton($event, $field);
                }
                return $frag;
            },
            function ($field, $repeatButtonFrag) {
                $this->repeaterButton[$field->field_slug_unique_hash] = $repeatButtonFrag;
                return $repeatButtonFrag;
            });
    }

    /**
     * @param $item
     * @return void
     * @throws \Exception
     */
    private function walkTreeAndDoTheDo($item): void
    {
        $this->fieldsSorted[] = $item;
        if (isset($item->_children)) {
            $item->_children = $this->sortWalkerTreeChildren($item);
            $children = $item->_children;
            unset($item->_children);
            foreach ($children as $child){
                if ($child->field_slug === 'modular_rowcolumnrepeater'){
                    $this->walkTreeAndDoTheDo($child);
                } else {
                    $this->fieldsSorted[] = $child;
                }
            }
        }

        $this->childStacks[$item->field_slug_unique_hash] = $item;
    }

    /**
     * @param $item
     * @return array
     */
    private function sortWalkerTreeChildren($item): array
    {
        $sorted = [];
        if (isset($this->repeaters[$item->field_slug_unique_hash])) {
            $originalFields = $this->repeaters[$item->field_slug_unique_hash]->_field->_children;
            $treeFields = $item->_children;
            foreach ($originalFields as $originalField) {
                $originalFieldSlugHash = $originalField->field_options->field_slug_unique_hash;
                $match = false;
                foreach ($treeFields as $treeField) {
                    $treeFieldSlugHash = $treeField->field_slug_unique_hash;
                    if ($originalFieldSlugHash === $treeFieldSlugHash) {
                        $sorted[] = $treeField;
                        $match = true;
                    }
                }

                // TODO
                // if you have exhaust looping, and you couldn't match anything, then it means
                // the originalFields has a new field push it in the sorted
                // for now, we won't do anything...
                if (!$match) {
                    // $sorted[] = ;
                }
            }
        }

        return $sorted;
    }

    private function sortAndCollectDepthFrag(OnFieldMetaBox $event, $items)
    {
        dd($items);
        foreach ($items as $item){
            $itemHash = $item['hash'];
            if (isset($this->repeaters[$itemHash])){
                $repeaterField = $this->repeaters[$itemHash];
                $frag = $this->getTopWrapper($event, $repeaterField);

                $frag .= <<<OPEN_UL_TAG
<ul style="margin-left: 0; transform: unset; box-shadow: unset;" class="row-col-item-user owl">
OPEN_UL_TAG;


                $currentDepth = (int)$item['depth'];
                // first encounter
                if ($this->lastDepth === null){
                    $this->lastDepth = $currentDepth;
                    $item['frag'] = $frag . $item['frag'];
                    $this->toTree[] = $item;
                } else {
                    if ($currentDepth === $this->lastDepth){
                        $this->lastDepth = $currentDepth;
                       $this->toTree[array_key_last($this->toTree)]['frag'] .= <<<CLOSE_LAST_REPEATER
            </ul>
        </div>
   </div>
</div>
{$event->_bottomHTMLWrapper()}
CLOSE_LAST_REPEATER;
                       $item['frag'] = $frag . $item['frag'];
                       $this->toTree[] = $item;
                    }elseif ($currentDepth < $this->lastDepth){
                        $this->lastDepth = $currentDepth;
                        $lastDepthHash = $this->toTree[array_key_last($this->toTree)]['hash'];
                        $backwardFrag = '';
                        if (isset($this->repeaterButton[$lastDepthHash])){
                            // loop backward and keep popping as long as current depth is lesser than last depth
                            foreach ($this->loopBackward($this->toTree) as $backwardItem){
                                $backwardItemDepth = (int)$backwardItem['depth'];
                                if ($currentDepth < $backwardItemDepth){
                                    $popBackwardItem = array_pop($this->toTree);
                                    $backwardFrag .= $popBackwardItem['frag'];
                                } else {
                                    $this->breakLoopBackward = true;
                                }
                            }

                            $repeatersButton = $this->repeaterButton[$lastDepthHash];
                            $backwardFrag .=$repeatersButton;
                            $item['frag'] = $frag . $item['frag'] . $backwardFrag;
                            $this->toTree[] = $item;
                        }
                    } elseif ($currentDepth > $this->lastDepth){
                        $this->lastDepth = $currentDepth;
                        $this->toTree[array_key_last($this->toTree)]['frag'] .= <<<CLOSE_LAST_REPEATER
            </ul>
        </div>
   </div>
</div>
{$event->_bottomHTMLWrapper()}
CLOSE_LAST_REPEATER;
                        $item['frag'] = $frag . $item['frag'];
                        $this->toTree[] = $item;
                    }
                }
            }
        }

        return $this->toTree[0]['frag'] . <<<CLOSE_LAST_REPEATER
            </ul>
        </div>
   </div>
</div>
{$event->_bottomHTMLWrapper()}
CLOSE_LAST_REPEATER;
    }

    /**
     * @param $items
     * @return \Generator
     */
    private function loopBackward($items): \Generator
    {
        for ($i = count($items) - 1; $i >= 0; $i--){
            if ($this->breakLoopBackward){
                break;
            }
            yield $items[$i];
        }
        $this->breakLoopBackward = false;
    }

    /**
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $inputData = (isset(getPostData()[$data->inputName])) ? getPostData()[$data->inputName] : '';
        $inputData = json_decode($inputData);
        $frag = '';

        $this->oldPostData = getPostData();
        addToGlobalVariable('Data', []);
        // return $this->handleUserFormFrag($event, $data);
        $this->unnestRepeater($data);
        $this->repeatersButton($event, $data);

        foreach ($inputData->tree->_data as $tree_data){
            $this->walkTreeAndDoTheDo($tree_data);
        }

        return $this->handleRepeaterUserFormFrag($event, $this->fieldsSorted);
       // return $this->sortAndCollectDepthFrag($event, $this->depthCollector);
        // return $this->depthCollector[13]['frag'];
        dd($inputData, $data, $this);
        if (isset($inputData->treeTimes)) {
            // return $this->handleUserFormFrag($event, $data);
            foreach ($inputData->treeTimes as $key => $fields) {
                $frag .= $this->handleUserFormFrag($event, $data, function ($child, $parent) use ($data, $event, $key, $inputData) {
                    return $this->handleChild($child, $parent, $event, $key, $inputData);
                });
            }
        } else {
            $frag = $this->handleUserFormFrag($event, $data);
        }

        return $frag;
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @return string
     * @throws \Exception
     */
    private function getTopWrapper(OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->field_name)) ? $data->field_name : '';
        if (empty($fieldName)){
            $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'DataTable_Repeater';
        }

        $row = 1;
        $column = 1;
        if (isset($data->row)) {
            $row = $data->row;
        }

        if (isset($data->column)) {
            $column = $data->column;
        }

        $depth = $data->_field->depth ?? $data->depth;

        $frag = $event->_topHTMLWrapper($fieldName, $data, true);

        $gridTemplateCol = '';
        # from js tree input
        if (isset($data->field_name)){
            $gridTemplateCol = "$data->grid_template_col";
        } else {
            if (isset($data->grid_template_col)) {
                $gridTemplateCol = " grid-template-columns: {$data->grid_template_col};";
            }
        }

        $repeat_button_text = $data->repeat_button_text ?? 'Repeat Section';

        $inputName = $data->inputName ?? '';
        $frag .= <<<HTML
<style>
.remove-row-col-repeater-button:hover + .rowColumnItemContainer {
    background: #c2dbffa3;
}
</style>
<div class="row-col-parent repeater-field position:relative cursor:move owl draggable draggable-repeater" 
data-row="$row" 
data-col="$column" 
data-grid_template_col="$gridTemplateCol" 
data-repeater_repeat_button_text="$repeat_button_text" 
data-repeater_field_name="$fieldName" 
data-repeater_depth="$depth" 
data-repeater_input_name="$inputName">
    <button type="button" class="position:absolute height:2em d:flex align-items:center right:0 remove-row-col-repeater-button text-align:center bg:transparent border:none 
        color:black bg:white-one border-width:default border:black padding:small cursor:pointer"><span>Delete</span></button>
    <div style="border: 2px dashed #000; padding: 1em;--row:$row; --column:$column; $gridTemplateCol" class="cursor:pointer form-group d:grid cursor:move owl flex-gap:small overflow-x:auto overflow-y:auto rowColumnItemContainer grid-template-rows grid-template-columns">
HTML;

        return $frag;
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @param callable|null $interceptChild
     * @param callable|null $interceptBottom
     * @return string
     * @throws \Exception
     */
    private function handleUserFormFrag(OnFieldMetaBox $event, $data, callable $interceptChild = null, callable $interceptBottom = null): string
    {
        $row = 1;
        $column = 1;
        if (isset($data->row)) {
            $row = $data->row;
        }

        if (isset($data->column)) {
            $column = $data->column;
        }

        $cell = $row * $column;

        $repeat_button_text = $data->repeat_button_text ?? 'Repeat Section';

        $frag = $this->getTopWrapper($event, $data);

        for ($i = 1; $i <= $cell; $i++) {
            if (!isset($data->_field->_children)) {
                continue;
            }

            $frag .= <<<HTML
<ul style="margin-left: 0; transform: unset; box-shadow: unset;" data-cell_position="$i" class="row-col-item-user owl">
HTML;

            if (isset($data->_field->_children)) {
                foreach ($data->_field->_children as $child) {
                    $childCellNumber = (isset($child->field_options->{$child->field_name . "_cell"}))
                        ? (int)$child->field_options->{$child->field_name . "_cell"}
                        : $i;

                    if ($childCellNumber === $i) {
                        if (isset($child->field_options)) {
                            $child->field_options->{"_field"} = $child;
                        }
                        $interceptChildFrag = '';
                        if ($interceptChild) {
                            $interceptChildFrag = $interceptChild($child->field_options, $data);
                        }
                        $frag .= (empty($interceptChildFrag)) ? $event->getUsersForm($child->field_name, $child->field_options ?? null) : $interceptChildFrag;
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


        $frag .= $event->_bottomHTMLWrapper();

        $frag .= <<<HTML
<button type="button" class="margin-top:1em row-col-repeater-button width:200px text-align:center bg:transparent border:none 
color:black bg:white-one border-width:default border:black padding:default cursor:pointer">
  $repeat_button_text
  <template class="repeater-frag">
    $frag
  </template>
</button>
HTML;

        if ($interceptBottom) {
            return $interceptBottom($data, $frag);
        }

        return $frag;
    }


    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @return string
     * @throws \Exception
     */
    private function handleRepeaterUserFormFrag(OnFieldMetaBox $event, $items): string
    {
        foreach ($items as $key => $item){
            $fieldSlugHash = $item->field_slug_unique_hash;
            $data = null;
            if (key_exists($fieldSlugHash, $this->repeaters) || key_exists($fieldSlugHash, $this->nonRepeaters)){
                $data = (isset($this->repeaters[$fieldSlugHash])) ? $this->repeaters[$fieldSlugHash] : $this->nonRepeaters[$fieldSlugHash];
            }
            $openTopWrapper = $this->getTopWrapper($event, $data);
            $item->frag = '';
            if ($key === 0){
                $item->frag = $this->getTopWrapper($event, $data);
                $this->toTree[] = $item;
                dd($this);
            } else {
                $lastItemInStack = $this->toTree[array_key_last($this->toTree)];
                $lastItemDepth = (int)$lastItemInStack->depth;
                if ($this->inItem === false){
                    if ($lastItemInStack->field_slug === 'modular_rowcolumnrepeater' && $item->field_slug !== 'modular_rowcolumnrepeater'){
                        $lastItemInStack->frag .= <<<HTML
<ul style="margin-left: 0; transform: unset; box-shadow: unset;" class="row-col-item-user owl">
HTML;
                        $this->inItem = true;
                    }
                }

                if ($item->field_slug === 'modular_rowcolumnrepeater'){
                    $this->toTree[] = $item;
                    $currentDepth = (int)$item->depth;
                    // close last item
                    if ($lastItemDepth === $currentDepth){
                        $lastItemInStack->frag .= <<<CLOSE_LAST_ITEM
    </ul>
    </div>
</div>
{$event->_bottomHTMLWrapper()}
$openTopWrapper
CLOSE_LAST_ITEM;
                    }

                    if ($currentDepth > $lastItemDepth){
                        $this->inItem = false;
                        $lastItemInStack->frag .= <<<HTML
</ul>
<ul style="margin-left: 0; transform: unset; box-shadow: unset;" class="row-col-item-user owl">
HTML;
                        $item->frag = $openTopWrapper;
                    }

                    if ($currentDepth < $lastItemDepth){
                        $timeToClose = 0;
                        foreach ($this->loopBackward($this->toTree) as $backItem){
                            $backItemDepth = (int)$backItem->depth;
                            dd($items, $backItem, $this);
                        }
                    }

                } else {
                    addToGlobalVariable('Data', (array)$item);
                    $data = $this->nonRepeaters[$fieldSlugHash];
                    $lastItemInStack->frag .= $event->getUsersForm($data->field_slug, $data ?? null);
                }

            }
        }
        $depth = (int)$data->depth;
        $this->toTree[] = $data;
        $frag = $this->getTopWrapper($event, $data);
        $frag .= <<<HTML
<ul style="margin-left: 0; transform: unset; box-shadow: unset;" class="row-col-item-user owl">
HTML;

        foreach ($data->_children as $child) {
            $lastField = $this->toTree[array_key_last($this->toTree)];
            $lastFieldDepth = (int)$lastField->depth;
            if ($child->field_slug === 'modular_rowcolumnrepeater'){
                $childDepth = (int)$child->depth;
            //    dd($lastFieldDepth, $childDepth);
                if ($childDepth > $lastFieldDepth || $childDepth < $lastFieldDepth){
                    $frag .= "</ul>";
                    $frag .= <<<HTML
<ul style="margin-left: 0; transform: unset; box-shadow: unset;" class="row-col-item-user owl">
HTML;
                }


                $frag .= $this->walkTreeAndDoTheDo($event, $child);

            } else {
                $childFieldSlugHash = $child->field_slug_unique_hash;
                if (isset($this->nonRepeaters[$childFieldSlugHash])){
                    $data = $this->nonRepeaters[$childFieldSlugHash];
                    addToGlobalVariable('Data', (array)$child);
                    $data = $data->_field;
                    $frag .= $event->getUsersForm($data->field_name, $data->field_options ?? null);

                }
            }

        }

        $frag .= <<<HTML
        </ul>
    </div>
</div>
HTML;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function viewData(OnFieldMetaBox $event, $data): string
    {
        $frag = '';
        if (isset($data->_field->_children)) {
            foreach ($data->_field->_children as $child) {
                if (isset($child->field_options)) {
                    $child->field_options->{"_field"} = $child;
                }
                $event->getViewProcessingFrag($child->field_name, $child->field_options ?? null);
            }
        }

        return $frag;
    }
}