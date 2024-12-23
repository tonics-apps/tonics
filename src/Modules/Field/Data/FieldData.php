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

namespace App\Modules\Field\Data;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Events\FieldTemplateFile;
use App\Modules\Field\Events\OnAddFieldSanitization;
use App\Modules\Field\Events\OnAfterPreSavePostEditorFieldItems;
use App\Modules\Field\Events\OnComparedSortedFieldCategories;
use App\Modules\Field\Events\OnFieldFormHelper;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\FieldTemplateFileInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class FieldData extends AbstractDataLayer
{
    use UniqueSlug, Validator;

    const UNWRAP_FIELD_CONTENT_PREVIEW_MODE  = 1;
    const UNWRAP_FIELD_CONTENT_FRONTEND_MODE = 2;
    private ?OnFieldMetaBox $onFieldMetaBox = null;

    public function getFieldTable (): string
    {
        return Tables::getTable(Tables::FIELD);
    }

    public function getFieldItemsTable (): string
    {
        return Tables::getTable(Tables::FIELD_ITEMS);
    }

    public function getFieldColumns (): array
    {
        return ['field_id', 'field_name', 'field_slug', 'created_at', 'updated_at'];
    }

    public function getFieldItemsColumns (): array
    {
        return [
            'id', 'fk_field_id', 'field_id', 'field_name', 'field_options', 'created_at', 'updated_at',
        ];
    }

    /**
     * @param string $slug
     *
     * @return mixed
     * @throws \Exception
     */
    public function getFieldID (string $slug): mixed
    {
        $result = null;
        db(onGetDB: function ($db) use ($slug, &$result) {
            $table = $this->getFieldTable();
            $result = $db->row("SELECT `field_id` FROM $table WHERE `field_slug` = ?", $slug)->field_id ?? null;
        });

        return $result;
    }

    public function getFieldAndFieldItemsCols (): string
    {
        $fieldTable = $this->getFieldTable();
        $fieldItemsTable = $this->getFieldItemsTable();
        return <<<COLUMNS
$fieldTable.field_id as main_field_id, $fieldTable.field_name as main_field_name, $fieldTable.field_slug as main_field_slug,
$fieldItemsTable.field_id as field_id, $fieldItemsTable.field_name as field_name, field_options, `id`, field_parent_id, fk_field_id
COLUMNS;
    }

    /**
     * @param array $slugs
     * @param array $postData
     *
     * @return OnFieldFormHelper
     * @throws \Exception
     */
    public function generateFieldWithFieldSlug (array $slugs, array $postData = []): OnFieldFormHelper
    {
        if (!empty($slugs)) {
            # For Field
            $fields = null;
            db(onGetDB: function ($db) use ($slugs, &$fields) {
                $fields = $db->Select("field_id, field_slug")
                    ->From($this->getFieldTable())->WhereIn('field_slug', $slugs)
                    ->FetchResult();
            });

            # For Field Items
            $fieldIDS = [];
            foreach ($slugs as $slug) {
                foreach ($fields as $field) {
                    if ($field->field_slug === $slug) {
                        $fieldIDS[] = $field->field_id;
                        break;
                    }
                }
            }
            return new OnFieldFormHelper($fieldIDS, $this, $postData);
        }
        return new OnFieldFormHelper([], $this, $postData);
    }

    /**
     * @throws \Exception
     */
    public function getFieldSortedItems (array $slugs = []): array
    {
        # For Field
        $fields = null;
        db(onGetDB: function ($db) use ($slugs, &$fields) {
            $fields = $db->Select('field_id')
                ->From($this->getFieldTable())
                ->WhereIn('field_slug', $slugs)
                ->OrderBy('field_id')->FetchResult();
        });

        # For Field Items
        $fieldIDS = [];
        foreach ($fields as $field) {
            $fieldIDS[] = $field->field_id;
        }

        $onFieldUserForm = new OnFieldFormHelper([], $this);
        return $onFieldUserForm->getFieldSortedItems($fieldIDS);
    }

    /**
     * @throws \Exception
     */
    public function getFieldItems (int $fkFieldID): array
    {
        $result = null;
        db(onGetDB: function ($db) use ($fkFieldID, &$result) {
            $table = $this->getFieldItemsTable();
            $result = $db->run("SELECT * FROM $table WHERE `fk_field_id` = ?", $fkFieldID);
        });

        $result = $this->decodeFieldOptions($result);
        if (!empty($result)) {
            return helper()->generateTree(['parent_id' => 'field_parent_id', 'id' => 'field_id'], $result);
        }
        return $result;
    }

    /**
     * @param $fieldData
     *
     * @return array
     */
    private function decodeFieldOptions ($fieldData): array
    {
        if (!empty($fieldData) && is_array($fieldData)) {
            $fieldData = array_map(function ($value) {
                $value->field_options = json_decode($value->field_options);
                return $value;
            }, $fieldData);
        }
        if (!is_array($fieldData)) {
            return [];
        }
        return $fieldData;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function createField (array $ignore = []): array
    {
        $slug = $this->generateUniqueSlug($this->getFieldTable(),
            'field_slug', helper()->slug(input()->fromPost()->retrieve('field_slug')));

        $field = [];
        $postColumns = array_flip($this->getFieldColumns());
        foreach (input()->fromPost()->all() as $inputKey => $inputValue) {
            if (key_exists($inputKey, $postColumns) && input()->fromPost()->has($inputKey)) {

                if ($inputKey === 'created_at') {
                    $field[$inputKey] = helper()->date(datetime: $inputValue);
                    continue;
                }
                if ($inputKey === 'field_slug') {
                    $field[$inputKey] = $slug;
                    continue;
                }
                $field[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $field);
        if (!empty($ignores)) {
            foreach ($ignores as $v) {
                unset($field[$v]);
            }
        }

        return $field;
    }

    /**
     * @param array $fieldValidationSlug
     * @param string $uniqueID
     *
     * @return string
     * @throws \Exception
     */
    public function getFieldsValidationSelection (array $fieldValidationSlug = [], string $uniqueID = ''): string
    {
        $fieldValidations = $this->getValidatorRuleNames();
        $fieldsFrag = "";
        $hash = helper()->randomString(10);
        if (empty($uniqueID)) {
            $uniqueID = $hash;
        }
        $fieldValidationSlug = array_combine($fieldValidationSlug, $fieldValidationSlug);
        foreach ($fieldValidations as $fieldValidation) {
            $checked = '';
            if (key_exists($fieldValidation, $fieldValidationSlug)) {
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
            <legend class="tonics-legend bg:pure-black color:white padding:tiny d:flex flex-gap:small align-items:center">
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
     * @param OnAddFieldSanitization $fieldSanitization
     * @param string|null $fieldSanitizationSlug
     * @param string $uniqueID
     *
     * @return string
     * @throws \Exception
     */
    public function getFieldsSanitizationSelection (OnAddFieldSanitization $fieldSanitization, ?string $fieldSanitizationSlug = '', string $uniqueID = ''): string
    {
        $fieldsFrag = "";
        $hash = helper()->randomString(10);
        if (empty($uniqueID)) {
            $uniqueID = $hash;
        }

        foreach ($fieldSanitization->getFieldsSanitization() as $fieldSanitizationName => $fieldSanitizationObject) {
            $checked = '';
            if ($fieldSanitizationName === $fieldSanitizationSlug) {
                $checked = "checked";
            }

            $fieldsFrag .= <<<HTML
<li class="field-item">
    <input type="radio" data-collect_checkboxes $checked title="$fieldSanitizationName" id="field_sanitization-$fieldSanitizationName-$uniqueID" name="field_sanitization" value="$fieldSanitizationName">
    <label for="field_sanitization-$fieldSanitizationName-$uniqueID">$fieldSanitizationName</label>
</li>
HTML;
        }

        return <<<HTML
<li tabindex="0" class="menu-arranger-li max-width:350 field-selection-container">

    <form>
            <fieldset class="width:100% padding:default d:flex justify-content:center flex-d:column">
                <legend class="tonics-legend bg:pure-black color:white padding:tiny d:flex flex-gap:small align-items:center">
                    <span class="menu-arranger-text-head">Fields Sanitization</span>
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
    </form>

</li>
HTML;
    }

    /**
     * @param array $fieldIDS
     *
     * @return string
     * @throws \Exception
     */
    public function getFieldsSelection (array $fieldIDS = []): string
    {
        $fields = null;
        db(onGetDB: function ($db) use (&$fields) {
            $table = Tables::getTable(Tables::FIELD);
            $fields = $db->run("SELECT * FROM $table");
        });

        $fieldsFrag = "";
        if (is_array($fieldIDS)) {
            $fieldIDS = array_flip($fieldIDS);
        }
        $hash = helper()->randomString(10);
        foreach ($fields as $field) {
            $checked = '';
            if (key_exists($field->field_id, $fieldIDS) || key_exists($field->field_slug, $fieldIDS)) {
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
            <legend class="tonics-legend bg:pure-black color:white padding:tiny d:flex flex-gap:small align-items:center">
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
     *
     * @return string
     * @throws \Exception
     */
    public function getFieldItemsListing (array $fieldItems): string
    {
        # re-dispatch so we can get the form values
        $onFieldMetaBox = new OnFieldMetaBox();
        $onFieldMetaBox->setSettingsType(OnFieldMetaBox::OnBackEndSettingsType)->dispatchEvent();
        $htmlFrag = '';
        foreach ($fieldItems as $field) {
            $htmlFrag .= $this->getFieldItemsListingFrag($field, $onFieldMetaBox);
        }

        return $htmlFrag;
    }

    /**
     * @param $field
     * @param OnFieldMetaBox $onFieldMetaBox
     *
     * @return string
     */
    protected function getFieldItemsListingFrag ($field, OnFieldMetaBox $onFieldMetaBox): string
    {
        $slug = $field->field_name ?? null;
        if (isset($field->field_options)) {
            $field->field_options->{"_field"} = $field;
        }
        return $onFieldMetaBox->getSettingsForm($slug, $field->field_options ?? null);
    }

    /**
     * @param array $fieldSlugs
     *
     * @return void
     */
    public function sortAndCacheFieldItemsForFrontEnd (array $fieldSlugs): void
    {

        if (empty($fieldSlugs)) {
            return;
        }

        try {
            $sortedFieldItems = $this->getFieldSortedItems($fieldSlugs);

            ## Sort
            foreach ($sortedFieldItems as $k => $sortFieldItem) {
                $sortedFieldItems[$k] = helper()->generateTree(['parent_id' => 'field_parent_id', 'id' => 'field_id'], $sortFieldItem, onData: function ($field) {
                    return $field;
                });
            }

            $sortedFieldItems = array_merge(...$sortedFieldItems);

            $key = 'sortedField_' . implode('_', $fieldSlugs);
            db(onGetDB: function ($db) use ($sortedFieldItems, $key) {
                $db->insertOnDuplicate(
                    Tables::getTable(Tables::GLOBAL),
                    [
                        'key'   => $key,
                        'value' => json_encode($sortedFieldItems, JSON_UNESCAPED_SLASHES),
                    ],
                    ['value'],
                );
            });

        } catch (\Exception $exception) {
            // log...
        }
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function getFieldItemsAPI (): void
    {
        if (url()->getHeaderByKey('action') === 'getFieldItems') {
            url()->removeFromHeader('HTTP_ACTION'); # This fixes the contentEditable appearing in page fields
            $fieldSlugs = json_decode(url()->getHeaderByKey('FIELDSLUG'), true);
            $fieldItems = $this->generateFieldWithFieldSlug($fieldSlugs, [])->getHTMLFrag();
            helper()->onSuccess($fieldItems);
        }
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function getFieldItemsAPIForEditor (): void
    {
        if (url()->getHeaderByKey('action') === 'getFieldItems') {
            $fieldSlugs = json_decode(url()->getHeaderByKey('FIELDSLUG'), true);
            helper()->onSuccess($this->generateFieldWithFieldSlug($fieldSlugs, [])->getHTMLFrag());
        }

        if (url()->getHeaderByKey('action') === 'wrapCollatedFieldItems') {
            $fieldFrag = $this->wrapFieldsForPostEditor(request()->getEntityBody());
            helper()->onSuccess($fieldFrag);
        }

        if (url()->getHeaderByKey('action') === 'unwrapCollatedFieldItems') {
            if (helper()->isJSON(request()->getEntityBody())) {
                $fieldItems = json_decode(request()->getEntityBody());
                $fieldCategories = $this->compareSortAndUpdateFieldItems($fieldItems);
                $htmlFrag = $this->getUsersFormFrag($fieldCategories);
                helper()->onSuccess($htmlFrag);
            }
            helper()->onError(400, 'An Error Occurred Build Field Items');
        }

    }

    /**
     * @throws \Exception
     */
    public function wrapFieldsForPostEditor (string $data, string $preview = ''): string
    {
        $id = helper()->randomString(10);
        $uniqueRadioName = helper()->randomString(5);
        return <<<HTML
<section contenteditable="false" class="tabs tonicsFieldTabsContainer color:black bg:white-one border-width:default border:black">

    <input contenteditable="true" type="radio" id="$id-preview" name="$uniqueRadioName" checked>
          <label contenteditable="true" class="fieldsPreview" style="cursor: pointer; caret-color: transparent;" for="$id-preview">Preview</label>
          <div class="fieldsPreviewContent position:relative d:flex" contenteditable="true">
             $preview
          </div>
      
      <input contenteditable="true" type="radio" id="$id-fields" name="$uniqueRadioName">
      <label class="fields-label fieldsEdit" contenteditable="true" style="cursor: pointer; caret-color: transparent;" for="$id-fields">Edit</label>
      
      <textarea style="display: none;" class="tonicsFieldWrapper">$data</textarea>
      
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
     *
     * @param array $fieldItems
     *
     * @return void
     * @throws \Exception
     */
    public function importFieldItems (array $fieldItems): void
    {
        if (empty($fieldItems)) {
            return;
        }

        $fieldTable = Tables::getTable(Tables::FIELD);
        $fieldSlugToID = [];
        $dbTx = db();
        try {
            $dbTx->beginTransaction();
            foreach ($fieldItems as $k => $item) {
                $json = json_decode($item->field_options, true) ?? [];
                # In the previous version, the fk_field_id was stupidly storing the field.field_name in the fk_field_id
                # and then slugifying it as the slug, the new version now support the field_slug, and slugifying fk_field_id for backward compatibility
                if (isset($item->field_slug)) {
                    $slug = $item->field_slug;
                } elseif (isset($item->fk_field_id)) {
                    $slug = helper()->slug($item->fk_field_id, '-');
                } else {
                    return;
                }

                if (!isset($fieldSlugToID[$slug])) {
                    $result = null;
                    $field = null;
                    db(onGetDB: function (TonicsQuery $db) use ($slug, $item, $fieldTable, &$field, &$result) {
                        $fieldFieldName = $item->field_field_name ?? $item->fk_field_id ?? $slug;
                        $db->FastDelete($fieldTable, db()->Where('field_slug', '=', $slug));
                        $field = $db->insertReturning($fieldTable, ['field_name' => $fieldFieldName, 'field_slug' => $slug], ['field_id'], 'field_id');
                    });

                    /**
                     * "fk_field_id" => 1262
                     * "field_name" => "input_text"
                     * "field_id" => 2
                     * "field_parent_id" => 1
                     * "field_options" =>
                     */

                    if (isset($field->field_id)) {
                        $fieldSlugToID[$slug] = $field->field_id;
                        $item->fk_field_id = $field->field_id;
                        $item->field_options = json_encode($json);
                    }
                } else {
                    $item->fk_field_id = $fieldSlugToID[$slug];
                    $item->field_options = json_encode($json, flags: JSON_UNESCAPED_SLASHES);
                }

                $validItems = [
                    'fk_field_id',
                    'field_id',
                    'field_parent_id',
                    'field_name',
                    'field_options',
                ];

                $newItem = [];
                foreach ($validItems as $key) {
                    if (property_exists($item, $key)) {
                        $newItem[$key] = $item->{$key};
                    }
                }

                $fieldItems[$k] = $newItem;

            }

            db(onGetDB: function ($db) use ($fieldItems) {
                $db->Insert($this->getFieldItemsTable(), $fieldItems);
            });

            $dbTx->commit();
            $dbTx->getTonicsQueryBuilder()->destroyPdoConnection();
        } catch (\Exception $exception) {
            $dbTx->rollBack();
            $dbTx->getTonicsQueryBuilder()->destroyPdoConnection();
        }
    }

    /**
     * @param $fieldSettings
     * @param int $mode
     * @param string $contentKey
     *
     * @return void
     * @throws \Throwable
     */
    public function unwrapFieldContent (&$fieldSettings, int $mode = self::UNWRAP_FIELD_CONTENT_FRONTEND_MODE, string $contentKey = 'post_content'): void
    {
        #
        # For _FieldDetails Which is The Sorted Result of All FieldItems
        #
        if (isset($fieldSettings['_fieldDetails']) && is_array($fieldDetails = json_decode($fieldSettings['_fieldDetails']))) {
            $fieldDetails = helper()->generateTree(['parent_id' => 'field_parent_id', 'id' => 'field_id'], $fieldDetails, onData: function ($field) {
                if (isset($field->field_options) && helper()->isJSON($field->field_options)) {
                    $fieldOption = json_decode($field->field_options);
                    $field->field_options = $fieldOption;
                }
                return $field;
            });
            $fieldSettings['_fieldDetails'] = $fieldDetails;
        }

        # PREVIEW MODE
        if ($mode === self::UNWRAP_FIELD_CONTENT_PREVIEW_MODE) {

            if (helper()->isJSON(request()->getEntityBody())) {

                $entityBody = json_decode(request()->getEntityBody());
                if (isset($entityBody->postData) && helper()->isJSON($entityBody->postData)) {
                    helper()->onSuccess($this->previewFragForFieldHandler($entityBody->postData));
                }

                if (isset($entityBody->layoutSelector)) {
                    $fieldItems = $this->compareSortAndUpdateFieldItems($entityBody->layoutSelector, [], ['sortOriginal' => false]);
                    $dropper = FieldConfig::getFieldSelectionDropper();
                    $dropper->processLogicWithEarlyAndLateCallbacks($fieldItems);
                    $string = view('Modules::Core/Views/Templates/theme', [
                        'SiteURL' => AppConfig::getAppUrl(),
                        'Dropper' => $dropper,
                        'Page'    => new \stdClass(),
                    ], TonicsView::RENDER_CONCATENATE);
                    helper()->onSuccess($string);
                }

            }

        }


        if (isset($fieldSettings[$contentKey])) {
            // fake getFieldItems action header
            url()->addToHeader('HTTP_ACTION', 'getFieldItems');
            $postContent = json_decode($fieldSettings[$contentKey], true);

            if (is_array($postContent)) {
                $fieldSettings[$contentKey] = '';
                foreach ($postContent as $field) {
                    #
                    # We Check If There is a FieldHandler in the PostData (meaning the logic should be handled there), if there is,
                    # we validate it. and pass it for handling...
                    #
                    if ($mode === self::UNWRAP_FIELD_CONTENT_FRONTEND_MODE) {
                        if ($field['raw'] === false) {
                            $postData = is_string($field['postData']) ? $field['postData'] : '';
                            $fieldSettings[$contentKey] .= $this->previewFragForFieldHandler($postData, $field);
                        } else {
                            if (isset($field['content'])) {
                                $fieldSettings[$contentKey] .= $field['content'];
                            }
                        }
                    }
                }
            }
        }

        // this is of questionable character, I think it is useless at this point, would investigate later
        // remove fake header action
        url()->removeFromHeader('HTTP_ACTION');
    }

    /**
     * @param string $postData
     * @param array $field
     *
     * @return string
     * @throws \Exception
     * @throws \Throwable
     */
    public function previewFragForFieldHandler (string $postData, array $field = []): string
    {
        $previewFrag = '';
        $onFieldMetaBox = new OnFieldMetaBox();
        $onFieldMetaBox->setSettingsType(OnFieldMetaBox::OnUserSettingsType)->dispatchEvent();

        $fieldItems = json_decode($postData);
        if (!is_array($fieldItems)) {
            return $previewFrag;
        }
        $fieldCategories = $this->compareSortAndUpdateFieldItems($fieldItems);
        $fieldHandlers = event()->getHandler()->getEventHandlers(new FieldTemplateFile());

        foreach ($fieldHandlers as $fieldHandler) {
            /** @var $fieldHandler FieldTemplateFileInterface */
            if (isset($fieldCategories[$fieldHandler->fieldSlug()])) {
                $fields = $fieldCategories[$fieldHandler->fieldSlug()];
                if ($fieldHandler->canPreSaveFieldLogic() && isset($field['previewFrag'])) {
                    $previewFrag .= $field['previewFrag'];
                } else {
                    $previewFrag .= $fieldHandler->handleFieldLogic($onFieldMetaBox, $fields);
                }
            }
        }

        return $previewFrag;
    }

    /**
     * @param FieldTemplateFileInterface $fieldHandler
     * @param $data
     *
     * @return string
     */
    public function handleWithFieldHandler (FieldTemplateFileInterface $fieldHandler, $data): string
    {
        return $fieldHandler->handleFieldLogic(fields: $data);
    }

    /**
     * @param $fieldSettings
     * @param $contentKey
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function preSavePostEditorFieldItems (&$fieldSettings, $contentKey): mixed
    {
        if (isset($fieldSettings[$contentKey]) && is_array($postEditorsContent = json_decode($fieldSettings[$contentKey], true))) {
            foreach ($postEditorsContent as &$field) {
                if ($field['raw'] === false) {
                    $postData = is_string($field['postData']) ? $field['postData'] : '';
                    $field['previewFrag'] = $this->previewFragForFieldHandler($postData);
                }
            }
            $fieldSettings[$contentKey] = json_encode($postEditorsContent);
        }

        return $fieldSettings;
    }


    /**
     * @param array $data
     * @param string $titleKey
     * @param string $contentKey
     *
     * @return array
     * @throws \Exception
     */
    public function prepareFieldSettingsDataForCreateOrUpdate (array $data, string $titleKey = 'post_title', string $contentKey = 'post_content'): array
    {

        $data['field_settings'] = input()->fromPost()->all();

        if (isset($_POST['field_settings']['token'])) {
            unset($_POST['field_settings']['token'], $data[$contentKey]);
        }

        if (isset($data[$contentKey])) {
            unset($data[$contentKey]);
        }

        if (isset($data[$titleKey])) {
            if (isset($data['field_settings']['seo_title']) && empty($data['field_settings']['seo_title'])) {
                $data['field_settings']['seo_title'] = $data[$titleKey];
            }
            if (!isset($data['field_settings']['seo_title'])) {
                $data['field_settings']['seo_title'] = $data[$titleKey];
            }
        }

        if (isset($data['field_settings'][$contentKey])) {
            if (isset($data['field_settings']['seo_description']) && empty($data['field_settings']['seo_description'])) {
                $data['field_settings']['seo_description'] = substr(strip_tags($data['field_settings'][$contentKey]), 0, 200);
            }
            if (!isset($data['field_settings']['seo_description'])) {
                $data['field_settings']['seo_description'] = substr(strip_tags($data['field_settings'][$contentKey]), 0, 200);
            }
        }

        if (isset($_POST['fieldItemsDataFromEditor'])) {
            $data['field_settings'][$contentKey] = $_POST['fieldItemsDataFromEditor'];
            if (isset($data['field_settings']['fieldItemsDataFromEditor'])) {
                unset($data['field_settings']['fieldItemsDataFromEditor']);
            }
        }

        $this->preSavePostEditorFieldItems($data['field_settings'], $contentKey);
        $onAfterPreSave = new OnAfterPreSavePostEditorFieldItems($data['field_settings']);
        event()->dispatch($onAfterPreSave);
        $data['field_settings'] = json_encode($onAfterPreSave->getFieldSettings());

        return $data;
    }


    /**
     * @param $post
     * @param string $fieldSettingsKey
     *
     * @return void
     * @throws \Exception
     */
    public function unwrapForPost (&$post, string $fieldSettingsKey = 'field_settings')
    {
        $fieldSettings = json_decode($post[$fieldSettingsKey], true);
        $this->unwrapFieldContent($fieldSettings);
        $post = [...$fieldSettings, ...$post];
        $post['created_at_words'] = strtoupper((new \DateTime($post['created_at']))->format('j M, Y'));
        $post['updated_at_words'] = strtoupper((new \DateTime($post['updated_at']))->format('j M, Y'));
    }

    /**
     * @param array $fieldItems
     * @param array $globalPostData
     *
     * @return array
     * @throws \Exception
     */
    /*    public function updateFieldsInputNameValue(array $fieldItems, array $globalPostData)
        {
            foreach ($fieldItems as $fieldItem){
                if (isset($fieldItem->field_options) && helper()->isJSON($fieldItem->field_options)) {
                    $fieldOption = json_decode($fieldItem->field_options);
                    if (isset($fieldOption->field_input_name) && key_exists($fieldOption->field_input_name, $globalPostData)){
                        $fieldOption->{$fieldOption->field_input_name} = $globalPostData[$fieldOption->field_input_name];
                    }
                    $fieldItem->field_options = json_encode($fieldOption);
                }
            }

            return $fieldItems;
        }*/

    /**
     * The purpose of this function is using the $fieldSlugIDS to not only sort the $fieldItems for any updated fields,
     * but also to build its field_options.
     *
     * The $fieldItems is expected to be a decoded field from the POST REQUEST
     *
     * ```
     * The settings param can contain:
     *
     * [
     *      `data` => {...}, // replace data input with the data
     *      `sortOriginal` => true, // if true, it would try to sort and update the fieldItems with properties from the original fields in database
     *      `all` => false, // if true, it would replace each data instance, false for only one instance
     *      `breakNestedField` => false, // if true, it would remove `_field` from fieldItems, this way, you can json_encode
     *      `onFieldItem` => fn($fieldItem) => {}, // whenever a fieldItem is sorted, you can use this callable to get it
     * ]
     * ```
     *
     * @param array $fieldItems
     * @param array $slugIDS
     * @param array $settings
     *
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function compareSortAndUpdateFieldItems (array $fieldItems, array $slugIDS = [], array $settings = []): array
    {
        $fieldCategories = [];
        $fieldSlugIDS = [];
        $data = $settings['data'] ?? null;
        $sortOriginal = $settings['sortOriginal'] ?? true;

        if (is_array($data)) {
            $data = (object)$data;
        }

        $all = $settings['all'] ?? false;

        $fieldItems = helper()->generateTree(['parent_id' => 'field_parent_id', 'id' => 'field_id'], $fieldItems, onData: function ($field) use ($sortOriginal, $slugIDS, &$fieldSlugIDS, $data, $all) {
            if (isset($field->main_field_slug) && !key_exists($field->main_field_slug, $fieldSlugIDS)) {
                $fieldSlugIDS[$field->main_field_slug] = $field->main_field_slug;
            }

            if (isset($field->field_options) && helper()->isJSON($field->field_options)) {
                $fieldOption = json_decode($field->field_options);
                if (isset($fieldOption->field_input_name) && isset($data->{$fieldOption->field_input_name}) && !isset($data->{"$fieldOption->field_input_name" . '_stop'})) {
                    if (!$all) {
                        $data->{"$fieldOption->field_input_name" . '_stop'} = '';
                    }
                    $fieldOption->{$field->field_input_name} = $data->{$fieldOption->field_input_name};
                }

                if (!$sortOriginal) {
                    $field->field_data = $fieldOption;
                } else {
                    $field->field_data = (array)$fieldOption;
                }

                $field->field_options = $fieldOption;
            }

            return $field;
        });

        if (!$sortOriginal) {
            return $fieldItems;
        }

        if (!empty($slugIDS)) {
            $fieldSlugIDS = $slugIDS;
            $fieldSlugIDS = array_combine($slugIDS, $slugIDS);
        }

        if (!empty($fieldSlugIDS)) {
            $fieldTable = $this->getFieldTable();
            $fieldItemsTable = $this->getFieldItemsTable();
            $fieldAndFieldItemsCols = $this->getFieldAndFieldItemsCols();

            $originalFieldIDAndSlugs = null;
            db(onGetDB: function ($db) use ($fieldTable, $fieldSlugIDS, &$originalFieldIDAndSlugs) {
                $originalFieldIDAndSlugs = $db->Select("field_id, field_slug")->From($fieldTable)
                    ->WhereIn('field_slug', $fieldSlugIDS)
                    ->OrderBy('field_id')->FetchResult();
            });

            # For Field Items
            $fieldIDS = [];
            $categoriesFromFieldIDAndSlug = [];
            foreach ($originalFieldIDAndSlugs as $originalFieldIDAndSlug) {
                if (key_exists($originalFieldIDAndSlug->field_slug, $fieldSlugIDS)) {
                    $fieldIDS[] = $originalFieldIDAndSlug->field_id;
                    $categoriesFromFieldIDAndSlug[$originalFieldIDAndSlug->field_slug] = [];
                }
            }

            $originalFieldItems = null;
            db(onGetDB: function (TonicsQuery $db) use ($fieldItemsTable, $fieldAndFieldItemsCols, $fieldIDS, $fieldTable, &$originalFieldItems) {
                $originalFieldItems = $db->Select($fieldAndFieldItemsCols)
                    ->From($fieldItemsTable)
                    ->Join($fieldTable, "$fieldTable.field_id", "$fieldItemsTable.fk_field_id")
                    ->WhereIn('fk_field_id', $fieldIDS)->OrderBy('fk_field_id')->FetchResult();
            });

            $originalFieldCategories = [];
            foreach ($originalFieldItems as $originalFieldItem) {
                if (!key_exists($originalFieldItem->main_field_slug, $originalFieldCategories)) {
                    $originalFieldCategories[$originalFieldItem->main_field_slug] = [];
                }
                $originalFieldCategories[$originalFieldItem->main_field_slug][] = $originalFieldItem;
                $fieldOption = json_decode($originalFieldItem->field_options);
                $originalFieldItem->field_options = $fieldOption;
            }

            foreach ($fieldItems as $fieldItem) {
                if (isset($fieldItem->main_field_slug) && key_exists($fieldItem->main_field_slug, $categoriesFromFieldIDAndSlug)) {
                    $fieldCategories[$fieldItem->main_field_slug][] = $fieldItem;
                }
            }

            // Sort and Arrange OriginalFieldItems
            foreach ($originalFieldCategories as $originalFieldCategoryKey => $originalFieldCategory) {
                $originalFieldCategories[$originalFieldCategoryKey] = helper()->generateTree(['parent_id' => 'field_parent_id', 'id' => 'field_id'], $originalFieldCategory);
            }

            foreach ($originalFieldCategories as $originalFieldCategoryKey => $originalFieldCategory) {
                if (isset($fieldCategories[$originalFieldCategoryKey])) {
                    $userFieldItems = $fieldCategories[$originalFieldCategoryKey];
                    $fieldCategories[$originalFieldCategoryKey] = $this->sortFieldWalkerTree($originalFieldCategory, $userFieldItems, $settings);
                }
            }
        }

        event()->dispatch(new OnComparedSortedFieldCategories($fieldCategories));

        return $fieldCategories;
    }

    /**
     * This would unwrap, compare and sort the fields, it would be appended under the field_settings as _fieldDetailsSorted,
     * the benefit of this is that we wouldn't need to sort it again when rendering the page for fronted user
     *
     * @param $data
     *
     * @return mixed
     * @throws \Exception
     */
    public function unwrapCompareAndSortFieldSettings ($data): mixed
    {
        # Note: The sorted is compressed, to not only save space, but it is too complex for mariadb to save, so compressing it works fine
        $compressJSON = function ($json) {
            $json = json_decode(json_encode($json));
            return helper()->compressFieldItems($json);
        };

        if (is_object($data)) {

            if (isset($data->field_settings)) {
                $data->field_settings = json_decode($data->field_settings, true);
                $data->field_settings = json_encode(
                    [
                        '_fieldDetailsSorted' => $compressJSON($this->compareSortAndUpdateFieldItems(
                            json_decode($data->field_settings['_fieldDetails']),
                            [],
                            ['data' => $data, 'breakNestedField' => true,],
                        )),
                        '_fieldDetails'       => $data->field_settings['_fieldDetails'],
                    ],
                );
            }


            if (isset($data->_fieldDetails)) {
                $data->_fieldDetailsSorted = $compressJSON($this->compareSortAndUpdateFieldItems(
                    json_decode($data->_fieldDetails),
                    [],
                    ['data' => $data, 'breakNestedField' => true],
                ));
            }
        }

        if (is_array($data)) {
            if (isset($data['field_settings'])) {
                $data['field_settings'] = json_decode($data['field_settings'], true);
                $data['field_settings'] = json_encode(
                    [
                        '_fieldDetailsSorted' => $compressJSON($this->compareSortAndUpdateFieldItems(
                            json_decode($data['field_settings']['_fieldDetails']),
                            [],
                            ['data' => $data, 'breakNestedField' => true],
                        )),
                        '_fieldDetails'       => json_decode($data['field_settings']['_fieldDetails']),
                    ],
                );
            }

            if (isset($data['_fieldDetails'])) {
                $data['_fieldDetailsSorted'] = $compressJSON($this->compareSortAndUpdateFieldItems(
                    json_decode($data['_fieldDetails']),
                    [],
                    ['data' => $data, 'breakNestedField' => true],
                ));
            }

        }

        return $data;
    }

    /**
     * @param array $fieldCategories
     *
     * @return string
     * @throws \Exception
     */
    public function getUsersFormFrag (array $fieldCategories): string
    {
        if ($this->onFieldMetaBox === null) {
            # re-dispatch so we can get the form values
            $onFieldMetaBox = new OnFieldMetaBox();
            $onFieldMetaBox->setSettingsType(OnFieldMetaBox::OnUserSettingsType)->dispatchEvent();
            $this->onFieldMetaBox = $onFieldMetaBox;
        }

        $htmlFrag = '';
        foreach ($fieldCategories as $fieldItems) {
            foreach ($fieldItems as $fieldItem) {
                $htmlFrag .= $this->onFieldMetaBox->getUsersForm($fieldItem->field_options->field_slug, $fieldItem->field_options);
            }
        }

        return $htmlFrag;
    }

    /**
     * @param $originalFieldItems
     * @param $userFieldItems
     * @param array $settings
     *
     * @return array
     */
    public function sortFieldWalkerTree ($originalFieldItems, $userFieldItems, array $settings = []): array
    {
        $breakNestedField = $settings['breakNestedField'] ?? false;
        $onFieldItem = $settings['onFieldItem'] ?? null;
        $parent = $settings['__parent'] ?? null;

        $sorted = [];
        foreach ($originalFieldItems as $originalFieldItem) {
            $originalFieldSlugHash = $originalFieldItem->field_options->field_slug_unique_hash;
            $match = false;
            $doneKey = [];
            foreach ($userFieldItems as $userFieldKey => $userFieldItem) {
                if (isset($userFieldItem->field_data)) {
                    $userFieldSlugHash = $userFieldItem->field_data->field_slug_unique_hash ?? $userFieldItem->field_data['field_slug_unique_hash'] ?? null;
                } else {
                    $userFieldSlugHash = $userFieldItem->field_options->field_slug_unique_hash ?? $userFieldItem->field_options['field_slug_unique_hash'];
                }

                # Skip Sorted $userFieldItem
                if (key_exists($userFieldKey, $doneKey)) {
                    continue;
                }

                if ($originalFieldSlugHash === $userFieldSlugHash) {
                    $doneKey[$userFieldKey] = $userFieldKey;
                    $fieldData = null;
                    $fieldData = $userFieldItem->field_data ?? $userFieldItem->field_options;
                    $userFieldItem->field_options = json_decode(json_encode($originalFieldItem->field_options));
                    if ($breakNestedField === false) {
                        $userFieldItem->field_options->{"_field"} = $userFieldItem;
                    }
                    $userFieldItem->field_data = (array)$fieldData;
                    $sorted[] = $userFieldItem;
                    $match = true;

                    // For Nested Children
                    // If Original Field Items has a Children and the UserFieldItem Has a Children, sortFieldWalkerTree,
                    // Otherwise, something is wrong in userFieldItem, fix it by adding the children from the original to it,
                    // nothing is really wrong though, it could be a situation where it is a repeatable field, and user has deleted all the repeated fields,
                    // that is the reason why it appears that $userFieldItem has no children
                    if (isset($originalFieldItem->_children)) {

                        $settings['__parent'] = $userFieldItem;

                        if (isset($userFieldItem->_children)) {
                            $userFieldItem->_children = $this->sortFieldWalkerTree($originalFieldItem->_children, $userFieldItem->_children, $settings);
                        } else {
                            $userFieldItem->_children = $originalFieldItem->_children;
                        }

                        if ($onFieldItem) {
                            $onFieldItem($userFieldItem, $parent);
                        }
                    }
                }
            }

            // TODO
            // if you have exhaust looping, and you couldn't match anything, then it means
            // the originalFields has a new field push it in the sorted
            // for now, we won't do anything...
            if (!$match) {

                $settings['__parent'] = $originalFieldItem;

                $cellName = $originalFieldItem->field_options->field_slug . '_cell';
                if (isset($originalFieldItem->field_options->{$cellName})) {
                    $cellPosition = $originalFieldItem->field_options->{$cellName};
                    $originalFieldItem->field_options->_cell_position = $cellPosition;
                }
                if (isset($originalFieldItem->_children)) {
                    $originalFieldItem->field_options->_children = $originalFieldItem->_children;
                }

                if ($onFieldItem) {
                    $onFieldItem($originalFieldItem, $parent);
                }

                $sorted[] = $originalFieldItem;
            }
        }

        return $sorted;
    }
}