<?php

namespace App\Modules\Field\Data;

use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Events\OnFieldUserForm;

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


    /**
     * @param array $slugs
     * @param array $postData
     * @param bool $viewProcessing
     * @return OnFieldUserForm
     * @throws \Exception
     */
    public function generateFieldWithFieldSlug(array $slugs, array $postData = [], bool $viewProcessing = false): OnFieldUserForm
    {
        if (!empty($slugs)){
            $questionMarks = helper()->returnRequiredQuestionMarks($slugs);
            # For Field
            $fields =  $this->selectWithCondition($this->getFieldTable(), ['field_id'], "field_slug IN ($questionMarks) ORDER BY field_id", $slugs, false);
            # For Field Items
            $fieldIDS = [];
            foreach ($fields as $field){
                $fieldIDS[] = $field->field_id;
            }
            return new OnFieldUserForm($fieldIDS, $this, $postData, $viewProcessing);
        }
        return new OnFieldUserForm([], $this, $postData, $viewProcessing);
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
    data-Field_id="$field->field_id" 
    data-Field_slug="$field->field_slug" 
    data-Field_name="$field->field_name"
    data-db_click_link="/admin/tools/field/$field->field_slug/edit"
    tabindex="0" 
    class="admin-widget-item-for-listing d:flex flex-d:column align-items:center justify-content:center cursor:pointer no-text-highlight">
        <fieldset class="padding:default width:100% box-shadow-variant-1 d:flex justify-content:center">
            <legend class="bg:pure-black color:white padding:default">$field->field_name</legend>
            <div class="admin-widget-information owl width:100%">
            <div class="text-on-admin-util text-highlight">$field->field_name</div>
         
                <div class="form-group d:flex flex-gap:small">
                     <a href="/admin/tools/field/$field->field_slug/edit" class="listing-button text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Edit</a>
                        
                         <a href="/admin/tools/field/items/$field->field_slug/builder" class="listing-button text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Builder</a>
                   
                   <form method="post" class="d:contents" action="/admin/tools/field/$field->field_slug/delete">
                    <input type="hidden" name="token" value="$csrfToken" >
                       <button data-click-onconfirmdelete="true" type="button" class="listing-button bg:pure-black color:white border:none border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Delete</button>
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
            <legend class="bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
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
            <legend class="bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
                <span class="menu-arranger-text-head">Fields</span>
                <button type="button" class="dropdown-toggle bg:transparent border:none cursor:pointer" aria-expanded="false" aria-label="Expand child menu">
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
     * @throws \Exception
     */
    protected function getFieldItemsListingFrag($field, OnFieldMetaBox $onFieldMetaBox): string
    {
        $slug = $field->field_name ?? null;
        $settings = $onFieldMetaBox->getFieldMetaSettings($slug);
        $scriptPath = isset($settings->scriptPath) && !empty($settings->scriptPath) ? "data-script_path={$settings->scriptPath}" : '';
        if (isset($field->field_options)){
            $field->field_options->{"_field"} = $field;
            $unique_hash = (isset($field->field_options->field_slug_unique_hash)) ? $field->field_options->field_slug_unique_hash : 'CHANGEID';
            $field->field_options->{"_topHTMLWrapper"} = function ($name, $slug) use ($scriptPath, $unique_hash) {
                return <<<HTML
<li tabIndex="0"
class="width:100% draggable menu-arranger-li cursor:move"
$scriptPath
>
        <fieldset
            class="width:100% padding:default box-shadow-variant-1 d:flex justify-content:center flex-d:column owl">
            <legend class="bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
                <span class="menu-arranger-text-head">$name</span>
                <button class="dropdown-toggle bg:transparent border:none cursor:pointer"
                        aria-expanded="false" aria-label="Expand child menu">
                    <svg class="icon:admin tonics-arrow-down color:white">
                        <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
                    </svg>
                </button>
            </legend>
            <div role="form" data-widget-form="true" class="widgetSettings d:none flex-d:column menu-widget-information cursor:pointer owl width:100% margin-top:0">
                <input type="hidden" name="field_slug" value="$slug">
                <input type="hidden" name="field_slug_unique_hash" value="$unique_hash">
HTML;

            };

            $field->field_options->{"_bottomHTMLWrapper"} = <<<HTML
                <div class="form-group">
                    <button name="delete" class="delete-menu-arrange-item listing-button border:none bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cursor:pointer act-like-button">
                        Delete Field Item
                    </button>
                </div>
            </div>
        </fieldset>
    </li>
HTML;
        }

        return $onFieldMetaBox->getSettingsForm($slug, $field->field_options ?? null);
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
}