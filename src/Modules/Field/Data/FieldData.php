<?php

namespace App\Modules\Field\Data;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Events\FieldTemplateFile;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Events\OnFieldUserForm;
use App\Modules\Field\Interfaces\FieldTemplateFileInterface;

class FieldData extends AbstractDataLayer
{
    use UniqueSlug, Validator;

    public function getFieldTable(): string
    {
        return Tables::getTable(Tables::FIELD);
    }

    public function getFieldItemsTable(): string
    {
        return Tables::getTable(Tables::FIELD_ITEMS);
    }

    public function getFieldColumns(): array
    {
        return [ 'field_id', 'field_name', 'field_slug', 'created_at', 'updated_at' ];
    }

    public function getFieldItemsColumns(): array
    {
        return [
            'id', 'fk_field_id', 'field_id', 'field_name', 'field_options', 'created_at', 'updated_at'
        ];
    }

    /**
     * @param string $slug
     * @return mixed
     * @throws \Exception
     */
    public function getFieldID(string $slug): mixed
    {
        $table = $this->getFieldTable();
        return db()->row("SELECT `field_id` FROM $table WHERE `field_slug` = ?", $slug)->field_id ?? null;
    }

    public function getFieldAndFieldItemsCols(): string
    {
        $fieldTable = $this->getFieldTable(); $fieldItemsTable = $this->getFieldItemsTable();
        return <<<COLUMNS
$fieldTable.field_id as main_field_id, $fieldTable.field_name as main_field_name, $fieldTable.field_slug as main_field_slug,
$fieldItemsTable.field_id as field_id, $fieldItemsTable.field_name as field_name, field_options, `id`, field_parent_id, fk_field_id
COLUMNS;
    }


    /**
     * @param array $slugs
     * @param array $postData
     * @return OnFieldUserForm
     * @throws \Exception
     */
    public function generateFieldWithFieldSlug(array $slugs, array $postData = []): OnFieldUserForm
    {
        if (!empty($slugs)){
            $questionMarks = helper()->returnRequiredQuestionMarks($slugs);
            # For Field
            $fields =  $this->selectWithCondition($this->getFieldTable(), ['field_id', 'field_slug'], "field_slug IN ($questionMarks) ORDER BY field_id", $slugs, false);
            # For Field Items
            $fieldIDS = [];
            foreach ($fields as $field){
                $fieldIDS[] = $field->field_id;
            }

            return new OnFieldUserForm($fieldIDS, $this, $postData);
        }
        return new OnFieldUserForm([], $this, $postData);
    }

    /**
     * @throws \Exception
     */
    public function getFieldSortedItems(array $slugs = []): array
    {
        $questionMarks = helper()->returnRequiredQuestionMarks($slugs);
        # For Field
        $fields =  $this->selectWithCondition($this->getFieldTable(), ['field_id'], "field_slug IN ($questionMarks) ORDER BY field_id", $slugs, false);
        # For Field Items
        $fieldIDS = [];
        foreach ($fields as $field){
            $fieldIDS[] = $field->field_id;
        }

        $onFieldUserForm = new OnFieldUserForm([], $this);
        return $onFieldUserForm->getFieldSortedItems($fieldIDS);
    }

    /**
     * @throws \Exception
     */
    public function getFieldItems(int $fkFieldID): array
    {
        $table = $this->getFieldItemsTable();
        $result = db()->run("SELECT * FROM $table WHERE `fk_field_id` = ?", $fkFieldID);
        $result = $this->decodeFieldOptions($result);
        if (!empty($result)){
            return helper()->generateTree(['parent_id' => 'field_parent_id', 'id' => 'field_id'], $result);
        }
        return $result;
    }

    /**
     * @param $fieldData
     * @return array
     */
    private function decodeFieldOptions($fieldData): array
    {
        if (!empty($fieldData) && is_array($fieldData)){
            $fieldData = array_map(function ($value){
                $value->field_options = json_decode($value->field_options);
                return $value;
            }, $fieldData);
        }
        if (!is_array($fieldData)){
            return [];
        }
        return $fieldData;
    }

    /**
     * @throws \Exception
     */
    public function adminFieldListing($fields): string
    {
        $csrfToken = session()->getCSRFToken();
        $htmlFrag = '';
        foreach ($fields as $k => $field) {
            $htmlFrag .= <<<HTML
    <li 
    data-list_id="$k" data-id="$field->field_id"  
    data-field_id="$field->field_id" 
    data-field_slug="$field->field_slug" 
    data-field_name="$field->field_name"
    data-db_click_link="/admin/tools/field/$field->field_slug/edit"
    tabindex="0" 
    class="admin-widget-item-for-listing d:flex flex-d:column align-items:center justify-content:center cursor:pointer no-text-highlight">
        <fieldset class="padding:default width:100% d:flex justify-content:center">
            <legend class="bg:pure-black color:white padding:default">$field->field_name</legend>
            <div class="admin-widget-information owl width:100%">
            <div class="text-on-admin-util text-highlight">$field->field_name</div>
         
                <div class="form-group d:flex flex-gap:small flex-wrap:wrap">
                     <a href="/admin/tools/field/$field->field_slug/edit" class="listing-button text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cursor:pointer">Edit</a>
                        
                         <a href="/admin/tools/field/items/$field->field_slug/builder" class="listing-button text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cursor:pointer">Builder</a>
                   
                   <form method="post" class="d:contents" action="/admin/tools/field/$field->field_slug/delete">
                    <input type="hidden" name="token" value="$csrfToken" >
                       <button data-click-onconfirmdelete="true" type="button" class="listing-button bg:pure-black color:white border:none border-width:default border:black padding:gentle
                        margin-top:0 cursor:pointer">Delete</button>
                    </form>
                </div>
                
            </div>
        </fieldset>
    </li>
HTML;
        }

        return $htmlFrag;
    }

    /**
     * @throws \Exception
     */
    public function createField(array $ignore = []): array
    {
        $slug = $this->generateUniqueSlug($this->getFieldTable(),
            'field_slug', helper()->slug(input()->fromPost()->retrieve('field_slug')));

        $field = []; $postColumns = array_flip($this->getFieldColumns());
        foreach (input()->fromPost()->all() as $inputKey => $inputValue){
            if (key_exists($inputKey, $postColumns) && input()->fromPost()->has($inputKey)){

                if($inputKey === 'created_at'){
                    $field[$inputKey] = helper()->date(timestamp: $inputValue);
                    continue;
                }
                if ($inputKey === 'field_slug'){
                    $field[$inputKey] = $slug;
                    continue;
                }
                $field[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $field);
        if (!empty($ignores)){
            foreach ($ignores as $v){
                unset($field[$v]);
            }
        }

        return $field;
    }

    /**
     * @param array $fieldValidationSlug
     * @param string $uniqueID
     * @return string
     * @throws \Exception
     */
    public function getFieldsValidationSelection(array $fieldValidationSlug = [], string $uniqueID = ''): string
    {
        $fieldValidations = $this->getValidatorRuleNames();
        $fieldsFrag = "";
        $hash = helper()->randomString(10);
        if (empty($uniqueID)){
            $uniqueID = $hash;
        }
        $fieldValidationSlug = array_combine($fieldValidationSlug, $fieldValidationSlug);
        foreach ($fieldValidations as $fieldValidation){
            $checked = '';
            if (key_exists($fieldValidation, $fieldValidationSlug)){
                $checked = "checked";
            }

            $fieldsFrag .= <<<HTML
<li class="field-item">
    <input type="checkbox" data-collect_checkboxes $checked title="$fieldValidation" id="field-validation-$fieldValidation-$uniqueID" name="field_validations" value="$fieldValidation">
    <label for="field-validation-$fieldValidation-$uniqueID">$fieldValidation</label>
</li>
HTML;
        }

        return <<<HTML
<li tabindex="0" class="menu-arranger-li max-width:350 field-selection-container">
        <fieldset class="width:100% padding:default d:flex justify-content:center flex-d:column">
            <legend class="tonics-legend bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
                <span class="menu-arranger-text-head">Fields Validation</span>
                <button class="dropdown-toggle bg:transparent border:none cursor:pointer" aria-expanded="false" aria-label="Expand child menu">
                <svg class="icon:admin tonics-arrow-down color:white">
                    <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
                </svg>
            </button>
            </legend>
            <div class="menu-widget-information width:100% flex-d:column d:none">
            <div class="menu-box-checkbox-items max-height:300px overflow:auto">
                <ul style="margin-left: 0;" class="list:style:none margin-top:0">
                    $fieldsFrag
                </ul>
            </div>
        </div>
        </fieldset>
</li>
HTML;
    }

    /**
     * @param array $fieldIDS
     * @return string
     * @throws \Exception
     */
    public function getFieldsSelection(array $fieldIDS = []): string
    {
        $table = Tables::getTable(Tables::FIELD);
        $fields = db()->run("SELECT * FROM $table");
        $fieldsFrag = "";
        if (is_array($fieldIDS)){
            $fieldIDS = array_flip($fieldIDS);
        }
        $hash = helper()->randomString(10);
        foreach ($fields as $field){
            $checked = '';
            if (key_exists($field->field_id, $fieldIDS) || key_exists($field->field_slug, $fieldIDS)){
                $checked = "checked data-cant_retrieve_field_items='true'";
            }

            $fieldsFrag .= <<<HTML
<li class="field-item">
    <input type="checkbox" $checked title="$field->field_name" id="field_{$field->field_slug}_$hash" name="field_ids[]" value="$field->field_slug">
    <label for="field_{$field->field_slug}_$hash">$field->field_name</label>
</li>
HTML;
        }

        return <<<HTML
<li tabindex="0" class="menu-arranger-li max-width:350 field-selection-container">
        <fieldset class="width:100% padding:default d:flex justify-content:center flex-d:column">
            <legend class="tonics-legend bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
                <span class="menu-arranger-text-head">Fields</span>
                <button type="button" class="dropdown-toggle bg:transparent border:none cursor:pointer" aria-expanded="false" aria-label="Expand child menu">
                <svg class="icon:admin tonics-arrow-down color:white">
                    <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
                </svg>
            </button>
            </legend>
            <div class="menu-widget-information width:100% flex-d:column d:none">
            <div class="menu-box-checkbox-items max-height:145px overflow:auto">
                <ul style="margin-left: 0;" class="list:style:none margin-top:0">
                    $fieldsFrag
                </ul>
            </div>
            <button type="button" class="field-add-button is-menu-checked listing-button border:none bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cursor:pointer act-like-button">Add Field
            </button>
        </div>
        </fieldset>
    </li>
HTML;
    }

    /**
     * @param array $fieldItems
     * @return string
     * @throws \Exception
     */
    public function getFieldItemsListing(array $fieldItems): string
    {
        # re-dispatch so we can get the form values
        $onFieldMetaBox = new OnFieldMetaBox();
        /**@var $onFieldMetaBox OnFieldMetaBox */
        $onFieldMetaBox = event()->dispatch($onFieldMetaBox);
        $htmlFrag = '';
        foreach ($fieldItems as $field){
            $htmlFrag .= $this->getFieldItemsListingFrag($field, $onFieldMetaBox);
        }

        return $htmlFrag;
    }

    /**
     * @param $field
     * @param OnFieldMetaBox $onFieldMetaBox
     * @return string
     */
    protected function getFieldItemsListingFrag($field, OnFieldMetaBox $onFieldMetaBox): string
    {
        $slug = $field->field_name ?? null;
        if (isset($field->field_options)){
            $field->field_options->{"_field"} = $field;
        }
        return $onFieldMetaBox->getSettingsForm($slug, $field->field_options ?? null);
    }


    /**
     * @param array $fieldSlugs
     * @return void
     */
    public function sortAndCacheFieldItemsForFrontEnd(array $fieldSlugs): void
    {

        if (empty($fieldSlugs)){
            return;
        }

        try {
            $sortedFieldItems = $this->getFieldSortedItems($fieldSlugs);

            ## Sort
            foreach ($sortedFieldItems as $k => $sortFieldItem){
                $sortedFieldItems[$k] = helper()->generateTree(['parent_id' => 'field_parent_id', 'id' => 'field_id'], $sortFieldItem, onData: function ($field) {
                    return $field;
                });
            }

            $sortedFieldItems = array_merge(...$sortedFieldItems);

            $key = 'sortedField_' . implode('_', $fieldSlugs);
            db()->insertOnDuplicate(
                Tables::getTable(Tables::GLOBAL),
                [
                    'key' => $key,
                    'value' => json_encode($sortedFieldItems,  JSON_UNESCAPED_SLASHES)
                ],
                ['value']
            );
        }catch (\Exception $exception){
            // log...
        }
    }

    /**
     * @throws \Exception
     */
    public function getFieldItemsAPI()
    {
        if (url()->getHeaderByKey('action') === 'getFieldItems') {
            $fieldSlugs = json_decode(url()->getHeaderByKey('FIELDSLUG'), true);
            $fieldItems = $this->generateFieldWithFieldSlug($fieldSlugs, [])->getHTMLFrag();
            helper()->onSuccess($fieldItems);
        }
    }

    /**
     * @throws \Exception
     */
    public function getFieldItemsAPIForEditor()
    {
        if (url()->getHeaderByKey('action') === 'getFieldItems') {
            $fieldSlugs = json_decode(url()->getHeaderByKey('FIELDSLUG'), true);
            $fieldItems = $this->wrapFieldsForPostEditor($this->generateFieldWithFieldSlug($fieldSlugs, [])->getHTMLFrag());
            helper()->onSuccess(str_replace('width:100%', '', $fieldItems));
        }
    }

    /**
     * @throws \Exception
     */
    public function wrapFieldsForPostEditor(string $data): string
    {
        $id = helper()->randomString(10);
        $uniqueRadioName = helper()->randomString(5);
        return <<<HTML
<section contenteditable="false" class="tabs tonicsFieldTabsContainer color:black bg:white-one border-width:default border:black">
      <input contenteditable="true" type="radio" id="$id-fields" name="$uniqueRadioName" checked>
      <label class="fields-label" contenteditable="true" style="cursor: pointer; caret-color: transparent;" for="$id-fields">Fields</label>
      
     <div contenteditable="true">
        <ul class="field-menu-ul menu-arranger tonics-field-items-unique list:style:none d:flex align-content:flex-start flex-wrap:wrap flex-d:column flex-gap">
            $data
        </ul>
     </div>
     
     <input contenteditable="true" type="radio" id="$id-preview" name="$uniqueRadioName">
      <label contenteditable="true" class="fieldsPreview" style="cursor: pointer; caret-color: transparent;" for="$id-preview">Preview</label>
      <div class="fieldsPreviewContent" contenteditable="true">
      </div>
      
      <input contenteditable="true" type="radio" id="$id-delete" name="$uniqueRadioName">
      <label contenteditable="true" class="fieldsDelete color:white border-width:default border:black" style="background: black !important; cursor: pointer; caret-color: transparent;" for="$id-delete">
        Delete Field
      </label>
</section>
HTML;

    }

    /**
     * Note: fk_field_id should be the field.field_name and not field_items.fk_field_id,
     * I am assuming you exported it as a json from a db tool, so, I'll re-add the appropriate fk_field_id
     * @param array $fieldItems
     * @return void
     * @throws \Exception
     */
    public function importFieldItems(array $fieldItems)
    {
        $fieldNameToID = [];
        foreach ($fieldItems as $k => $item){
            $json = json_decode($item->field_options, true) ?? [];
            if (isset($item->fk_field_id) && is_string($item->fk_field_id)){
                if (!isset($fieldNameToID[$item->fk_field_id])){
                    $return = db()->insertReturning(Tables::getTable(Tables::FIELD),  ['field_name' => $item->fk_field_id, 'field_slug' => helper()->slug($item->fk_field_id)], ['field_id']);
                    if (isset($return->field_id)){
                        $fieldNameToID[$item->fk_field_id] = $return->field_id;
                        $item->fk_field_id = $return->field_id;
                        $item->field_options = json_encode($json);
                    }
                } else {
                    $item->fk_field_id = $fieldNameToID[$item->fk_field_id];
                    $item->field_options = json_encode($json, flags: JSON_UNESCAPED_SLASHES);
                }
            }
            $fieldItems[$k] = (array)$item;
        }

        try {
            db()->insertBatch($this->getFieldItemsTable(), $fieldItems);
        }catch (\Exception $exception){
            // log...
            var_dump($exception->getMessage(), $exception->getTraceAsString());
        }
    }

    const UNWRAP_FIELD_CONTENT_PREVIEW_MODE = 1;
    const UNWRAP_FIELD_CONTENT_EDITOR_MODE = 2;
    const UNWRAP_FIELD_CONTENT_FRONTEND_MODE = 3;

    /**
     * @param $fieldSettings
     * @param int $mode
     * @param string $contentKey
     * @return void
     * @throws \Exception
     */
    public function unwrapFieldContent(&$fieldSettings, int $mode = self::UNWRAP_FIELD_CONTENT_FRONTEND_MODE, string $contentKey = 'post_content'): void
    {
        $onFieldUserForm = new OnFieldUserForm([], $this);

        $fieldTableSlugsInEditor = $fieldSettings['fieldTableSlugsInEditor'] ?? null;

        # PREVIEW MODE
        if ($mode === self::UNWRAP_FIELD_CONTENT_PREVIEW_MODE){
            $fieldTableSlugsInEditor = url()->getHeaderByKey('fieldTableSlugsInEditor');
        }

        if (!$fieldTableSlugsInEditor){
            return;
        }

        $fieldTableSlugs = json_decode($fieldTableSlugsInEditor, true);
        $oldPostData = AppConfig::initLoaderMinimal()::getGlobalVariableData('Data');
        $fieldItemsByMainFieldSlug = [];
        if (is_array($fieldTableSlugs) && !empty($fieldTableSlugs)){
            $fieldTableSlugs = array_values($fieldTableSlugs);
            $questionMarks = helper()->returnRequiredQuestionMarks($fieldTableSlugs);
            $fieldTable = $this->getFieldTable(); $fieldItemsTable = $this->getFieldItemsTable();
            $cols = $this->getFieldAndFieldItemsCols();

            $sql = <<<SQL
SELECT $cols FROM $fieldItemsTable 
JOIN $fieldTable ON $fieldTable.field_id = $fieldItemsTable.fk_field_id
WHERE $fieldTable.field_slug IN ($questionMarks)
ORDER BY id;
SQL;
            $fieldItems = db()->run($sql, ...$fieldTableSlugs);
            foreach ($fieldItems as $fieldItem) {
                $fieldOption = json_decode($fieldItem->field_options);
                $fieldItem->field_options = $fieldOption;
                $fieldItemsByMainFieldSlug[$fieldItem->main_field_slug][] = $fieldItem;
            }
        }

        # PREVIEW MODE
        if ($mode === self::UNWRAP_FIELD_CONTENT_PREVIEW_MODE){
            $previewFrag = '';
            $fieldPostDataInEditor = url()->getHeaderByKey('fieldPostDataInEditor');
            $postDataInstance = json_decode($fieldPostDataInEditor, true) ?? [];
            addToGlobalVariable('Data', $postDataInstance);
            foreach ($fieldItemsByMainFieldSlug as $fields){
                $previewFrag .= $onFieldUserForm->getViewFrag($fields);
            }
            helper()->onSuccess($previewFrag);
        }

        if (isset($fieldSettings[$contentKey])){
            // fake getFieldItems action header
            url()->addToHeader('HTTP_ACTION', 'getFieldItems');
            $postContent = json_decode($fieldSettings[$contentKey], true);
            if (is_array($postContent)){
                $fieldSettings[$contentKey] = '';
                foreach ($postContent as $field){
                    if (isset($field['fieldTableSlug']) && isset($fieldItemsByMainFieldSlug[$field['fieldTableSlug']])){
                        # Instance of each postData
                        addToGlobalVariable('Data',  $field['postData'] ?? []);
                        if ($mode === self::UNWRAP_FIELD_CONTENT_EDITOR_MODE){
                            $fieldSettings[$contentKey] .= $this->wrapFieldsForPostEditor($onFieldUserForm->getUsersFormFrag($fieldItemsByMainFieldSlug[$field['fieldTableSlug']]));
                        }

                        #
                        # We Check If There is a FieldHandler in the PostData (meaning the logic should be handled there), if there is,
                        # we validate it. and pass it for handling...
                        #
                        # If there is no FieldHandler in the PostData, then we pass it to getViewFrag (this might be slow if you have multiple fields),
                        # so it is not recommended...
                        #
                        if ($mode === self::UNWRAP_FIELD_CONTENT_FRONTEND_MODE){
                            if (isset($field['postData']['FieldHandler']) && ($fieldHandler = event()->getHandler()->getHandlerInEvent(FieldTemplateFile::class, $field['postData']['FieldHandler'])) !== null){
                                $fieldSettings[$contentKey] .=$this->handleWithFieldHandler($fieldHandler, getPostData());
                            }else {
                                $fieldSettings[$contentKey] .= $onFieldUserForm->getViewFrag($fieldItemsByMainFieldSlug[$field['fieldTableSlug']]);
                            }
                        }


                    }
                    if (isset($field['content'])){
                        $fieldSettings[$contentKey] .= $field['content'];
                    }
                }
            }
        }

        // restore old postData;
        addToGlobalVariable('Data', $oldPostData);
        // remove fake header action
        url()->removeFromHeader('HTTP_ACTION');
    }

    public function handleWithFieldHandler(FieldTemplateFileInterface $fieldHandler, $data): string
    {
        return $fieldHandler->handleFieldLogic(data: $data);
    }

    /**
     * @throws \Exception
     */
    public function unwrapForPost(&$post)
    {
        $fieldSettings = json_decode($post['field_settings'], true);
        $this->unwrapFieldContent($fieldSettings);
        $post = [...$fieldSettings, ...$post];
        $date = new \DateTime($post['post_created_at']);
        $post['created_at_words'] = strtoupper($date->format('j M, Y'));
    }
}