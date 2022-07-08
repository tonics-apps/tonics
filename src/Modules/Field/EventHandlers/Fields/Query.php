<?php

namespace App\Modules\Field\EventHandlers\Fields;

use App\Modules\Field\Events\OnFieldMetaBox;

class Query implements \Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox(
            'Query',
            'Add Query',
            'Query',
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Query';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $sqlTemplateFrag = (isset($data->sqlTemplateFrag)) ? $data->sqlTemplateFrag : <<<'SQL'
[[SQL('table_count')
  [[SELECT('table_name')
    [[SQLFUNC('COUNT', "*")]]
  ]]
  [[FROM('table_name')]]
]]

[[SQL('search_table_count')
  [[SELECT('table_name')
    [[SQLFUNC('COUNT', "*")]]
  ]]
  [[FROM('table_name')]]
  [[WHERE('col_to_search')
    [[OP('=')]] [[PARAM('value')]]
  ]]
]]

[[SQL('search_row_with_offset_limit')
  [[SELECT('table_name')
     [[COLS('ALL')]]
  ]]
  [[FROM('table_name')]]
  [[WHERE('col_to_search')
    [[COLS('PICK', 'col_to_search')]] ]] [[KEYWORD('LIKE')]] [[SQLFUNC('CONCAT', "%,value,%")]]
    [[ORDER('table.col', 'DESC')]]
    [[KEYWORD('LIKE')]] [[PARAM('20')]]
    [[KEYWORD('OFFSET')]] [[PARAM('10')]]
  ]]
]]

[[SQL('get_row_with_offset_limit')
  [[SELECT('table_name')
     [[COLS('ALL')]]
  ]]
  [[FROM('table_name')]]
  [[WHERE('col_to_search')
    [[ORDER('table.col', 'DESC')]]
    [[KEYWORD('LIKE')]] [[PARAM('20')]]
    [[KEYWORD('OFFSET')]] [[PARAM('10')]]
  ]]
]]
SQL;

        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';
        $frag .= <<<FORM
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Field Name
            <input id="fieldName-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
    <label class="menu-settings-handle-name" for="inputName-$changeID">Input Name
            <input id="inputName-$changeID" name="inputName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$inputName" placeholder="Input Name">
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="sqlTemplateFrag-$changeID">SQL Template Text: (Requires 4 sql tags, table_count, search_table_count, search_row_with_offset_limit, get_row_with_offset_limit)
            <textarea rows="10" id="sqlTemplateFrag-$changeID" name="sqlTemplateFrag" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
             placeholder="Start writing the sql template logic...">$sqlTemplateFrag</textarea>
    </label>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        // $sqlTemplateFrag = (isset($data->sqlTemplateFrag)) ? $data->sqlTemplateFrag :
        return '';
    }

    /**
     * @throws \Exception
     */
    public function viewFrag(OnFieldMetaBox $event, $data): string
    {
        $sqlTemplateFrag = (isset($data->sqlTemplateFrag)) ? $data->sqlTemplateFrag : '';
        dd($sqlTemplateFrag);
        return '';
    }
}