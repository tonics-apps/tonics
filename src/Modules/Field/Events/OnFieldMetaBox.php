<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\Events;

use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;
use Devsrealm\TonicsValidation\Validation;
use stdClass;

class OnFieldMetaBox implements EventInterface
{

    const OnBackEndSettingsType = 'OnSettingsType';
    const OnUserSettingsType = 'OnUserSettingsType';
    const OnViewSettingsType = 'OnViewType';

    private array $FieldBoxSettings = [];
    private $fieldSettings = null;
    private FieldData $fieldData;
    private ?Validation $validation = null;
    private bool $errorEmitted = false;
    private ?stdClass $currentFieldBox = null;

    private bool $disableTopHTMLWrapper = false;
    private bool $disableBottomHTMLWrapper = false;

    private string $settingsType = OnFieldMetaBox::OnBackEndSettingsType;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->fieldData = new FieldData();
    }

    /**
     * @param string $name
     * @param string $description
     * @param string $category
     * @param string $scriptPath
     * @param callable|null $settingsForm
     * @param callable|null $userForm
     * @param callable|null $handleViewProcessing
     * @return void
     * @throws \Exception
     */
    public function addFieldBox
    (
        string   $name,
        string   $description = '',
        string   $category = 'input',
        string   $scriptPath = '',
        callable $settingsForm = null,
        callable $userForm = null,
        callable $handleViewProcessing = null,
    )
    {
        $nameKey = helper()->slug($name);
        $category = strtolower($category);
        if (!key_exists($nameKey, $this->FieldBoxSettings)) {
            $this->FieldBoxSettings[$category][$nameKey] = (object)[
                'name' => $name,
                'category' => $category,
                'description' => $description,
                'scriptPath' => $scriptPath,
                'settingsForm' => $settingsForm ?? '',
                'userForm' => $userForm ?? '',
                'handleViewProcessing' => $handleViewProcessing ?? ''
            ];
        }
    }

    public function generateFieldMetaBox(): string
    {
        $htmlFrag = '';
        if (empty($this->FieldBoxSettings)) {
            return $htmlFrag;
        }

        foreach ($this->FieldBoxSettings as $fieldCategorySlug => $fieldCategory) {
            $checkBoxFrag = '';
            $category = ucfirst($fieldCategorySlug);

            foreach ($fieldCategory as $fieldSlug => $settings) {
                $scriptPath = (empty($settings->scriptPath)) ? '' : "data-script_path=$settings->scriptPath";
                $checkBoxFrag .= <<<HTML
<li class="field-item">
    <input type="checkbox"
    data-action="getForm"
    data-name = "$settings->name"
    data-slug="{$fieldCategorySlug}_$fieldSlug"
    $scriptPath
    data-slug_category="$fieldCategorySlug"
    title="$settings->description"
    id="{$category}_$fieldSlug" name="field-item" value="$fieldSlug">
    <label for="{$category}_$fieldSlug">$settings->name</label>
</li>
HTML;
            }

            $htmlFrag .= <<<HTML
<li class="width:100% menu-item-parent-picker menu-box-li cursor:pointer">
    <fieldset class="padding:default d:flex">
        <legend class="tonics-legend bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
        $category
            <button class="dropdown-toggle bg:transparent border:none cursor:pointer" aria-expanded="false" aria-label="Expand child menu">
                <svg class="icon:admin tonics-arrow-down color:white">
                    <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
                </svg>
            </button>
        </legend>
        <div class="d:none child-menu width:100% flex-d:column">
            <div class="menu-box-checkbox-items max-height:300px overflow:auto">
                <ul class="list:style:none">
                    $checkBoxFrag
                </ul>
            </div>
            <button class="is-menu-checked listing-button border:none bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cursor:pointer act-like-button">Add To Field
            </button>
        </div>
    </fieldset>
</li>
HTML;
        }

        return $htmlFrag;

    }

    /**
     * @param $fieldSlug
     * @param null $settings
     * @return string
     */
    public function getSettingsForm($fieldSlug, $settings = null): string
    {
        $explodeSlug = explode('_', $fieldSlug);
        if (!key_exists(0, $explodeSlug) || !key_exists(1, $explodeSlug)) {
            return '';
        }
        $fieldCategory = $explodeSlug[0];
        $slug = $explodeSlug[1];
        if (!isset($this->FieldBoxSettings[$fieldCategory][$slug])) {
            return '';
        }
        $this->currentFieldBox = $this->FieldBoxSettings[$fieldCategory][$slug];
        $formCallback = $this->FieldBoxSettings[$fieldCategory][$slug]->settingsForm;
        if (!is_callable($formCallback)) {
            return '';
        }
        if ($settings === null) {
            $settings = new stdClass();
            $settings->field_slug = $fieldSlug;
        }
        return $formCallback($settings);
    }

    /**
     * @param $fieldSlug
     * @param null $settings
     * @return string
     * @throws \Exception
     */
    public function getUsersForm($fieldSlug, $settings = null): string
    {
        $hideFrag = (isset($settings->hideInUserEditForm)) ? $settings->hideInUserEditForm : '';

        if($hideFrag === '1') {
            return '';
        }

        $explodeSlug = explode('_', $fieldSlug);
        if (!key_exists(0, $explodeSlug) || !key_exists(1, $explodeSlug)) {
            return '';
        }
        $fieldCategory = $explodeSlug[0];
        $slug = $explodeSlug[1];
        if (!isset($this->FieldBoxSettings[$fieldCategory][$slug])) {
            return '';
        }

        $formCallback = $this->FieldBoxSettings[$fieldCategory][$slug]->userForm;
        if (!is_callable($formCallback)) {
            return '';
        }

        if ($settings === null) {
            $settings = new stdClass();
            $settings->field_slug = $fieldSlug;
        }

        return $formCallback($settings);
    }

    /**
     * @param $fieldSlug
     * @param null $settings
     * @return string
     * @throws \Exception
     */
    public function getViewProcessingFrag($fieldSlug, $settings = null): string
    {
        $explodeSlug = explode('_', $fieldSlug);
        if (!key_exists(0, $explodeSlug) || !key_exists(1, $explodeSlug)) {
            return '';
        }
        $fieldCategory = $explodeSlug[0];
        $fieldSlug = $explodeSlug[1];
        if (!isset($this->FieldBoxSettings[$fieldCategory][$fieldSlug])) {
            return '';
        }

        $formCallback = $this->FieldBoxSettings[$fieldCategory][$fieldSlug]->handleViewProcessing;
        if (!is_callable($formCallback)) {
            return '';
        }
        return $formCallback($settings) ?? '';
    }

    /**
     * @param $fieldSlug
     * @return mixed
     */
    public function getFieldMetaSettings($fieldSlug): mixed
    {
        $explodeSlug = explode('_', $fieldSlug);
        if (isset($explodeSlug[0]) && isset($explodeSlug[1])) {
            $fieldCategory = $explodeSlug[0];
            $fieldSlug = $explodeSlug[1];
            if (!isset($this->FieldBoxSettings[$fieldCategory][$fieldSlug])) {
                return [];
            }

            return $this->FieldBoxSettings[$fieldCategory][$fieldSlug];
        }
        return [];
    }

    public function getRealName($fieldSlug): string
    {
        $explodeSlug = explode('_', $fieldSlug);
        $fieldCategory = $explodeSlug[0];
        $fieldSlug = $explodeSlug[1];

        return isset($this->FieldBoxSettings[$fieldCategory][$fieldSlug]) ? $this->FieldBoxSettings[$fieldCategory][$fieldSlug]->name : '';
    }

    /**
     * @param string $name
     * @param $data
     * @param bool $root
     * @param callable|null $handleTop
     * @return string
     * @throws \Exception
     */
    public function _topHTMLWrapper(string $name, $data, bool $root = false, callable $handleTop = null): string
    {
        if ($this->isDisableBottomHTMLWrapper()){
            return '';
        }
        $slug = $data->field_slug ?? '';
        $hash = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $postData = getPostData();

        $settings = $this->currentFieldBox ?? $this->getFieldMetaSettings($slug);
        $scriptPath = isset($settings->scriptPath) && !empty($settings->scriptPath) ? "data-script_path={$settings->scriptPath}" : '';
        $hideField = (isset($postData['hide_field'][$hash])) ? "<input type='hidden' name='hide_field[$hash]' value='$hash'>" : '';

        $isEditorLi = (url()->getHeaderByKey('action') === 'getFieldItems') ? 'contenteditable="false"' : '';
        $isEditorWidgetSettings = (url()->getHeaderByKey('action') === 'getFieldItems') ? 'contenteditable="true"' : '';
        $field_table_slug = (isset($data->_field->main_field_slug)) ? "<input type='hidden' name='main_field_slug' value='{$data->_field->main_field_slug}'>" : '';
        $rootOwl = ($root) ? 'owl' : '';

        $openToggle = [
            'button' => 'dropdown-toggle bg:transparent border:none cursor:pointer toggle-on',
            'aria-expanded' => 'true',
            'aria-label' => 'Collapse child menu',
            'svg' => 'icon:admin tonics-arrow-up color:white',
            'use' => '#tonics-arrow-up',
            'div' => 'swing-in-top-fwd d:flex',
        ];

        $closeToggle = [
            'button' => 'dropdown-toggle bg:transparent border:none cursor:pointer',
            'aria-expanded' => 'false',
            'aria-label' => 'Expand child menu',
            'svg' => 'icon:admin tonics-arrow-down color:white',
            'use' => '#tonics-arrow-down',
            'div' => 'swing-out-top-fwd d:none',
        ];

        $toggle = $openToggle;

        if ($handleTop){
            return $handleTop($isEditorWidgetSettings, $toggle);
        }

        $result = '';
        if ($this->getSettingsType() === $this::OnBackEndSettingsType){
            $toggle = $closeToggle;
            $result .=<<<HTML
<li $isEditorLi tabIndex="0"
class="width:100% draggable menu-arranger-li cursor:move field-builder-items overflow:auto"
$scriptPath>
        <fieldset
            class="width:100% padding:default d:flex justify-content:center flex-d:column $rootOwl">
            <legend class="tonics-legend bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
                <span class="menu-arranger-text-head">$name</span>
                <button class="{$toggle['button']}"
                        aria-expanded="{$toggle['aria-expanded']}" aria-label="{$toggle['aria-label']}" type="button">
                    <svg class="{$toggle['svg']}">
                        <use class="svgUse" xlink:href="{$toggle['use']}"></use>
                    </svg>
                </button>
            </legend>
            <div $isEditorWidgetSettings role="form" data-widget-form="true" class="widgetSettings owl flex-d:column menu-widget-information cursor:pointer width:100% {$toggle['div']}">
HTML;
        }

        if ($this->getSettingsType() === $this::OnUserSettingsType){
            $result .=<<<HTML
<li $isEditorLi tabIndex="0"
class="width:100% field-builder-items overflow:auto"
data-slug="$slug"
$scriptPath>
        <fieldset
            class="width:100% padding:default d:flex justify-content:center flex-d:column $rootOwl">
            <legend class="tonics-legend bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
                <span class="menu-arranger-text-head">$name</span>
            </legend>
            <div $isEditorWidgetSettings role="form" data-widget-form="true" class="widgetSettings flex-d:column menu-widget-information cursor:pointer width:100% {$toggle['div']}">
HTML;
        }

        return $result . <<<HTML
                $hideField
                <input type="hidden" name="field_slug" value="$slug">
                $field_table_slug
                <input type="hidden" name="field_slug_unique_hash" value="$hash">
HTML;
    }

    public function _bottomHTMLWrapper(callable $handleBottom = null): string
    {
        if ($this->isDisableBottomHTMLWrapper()){
            return '';
        }

        if ($this->getSettingsType() === $this::OnUserSettingsType){
            if ($handleBottom){
                return $handleBottom();
            }
            return <<<HTML
            </div>
        </fieldset>
    </li>
HTML;
        }

        if ($this->getSettingsType() === $this::OnBackEndSettingsType){
            return <<<HTML
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

        return '';

    }

    /**
     * @throws \Exception
     */
    public function generateMoreSettingsFrag($data = null, string $more = ''): string
    {
        $hideFrag = (isset($data->hideInUserEditForm)) ? $data->hideInUserEditForm : '';
        if ($hideFrag === '1') {
            $hideFrag = <<<HTML
<option value="0">False</option>
<option value="1" selected>True</option>
HTML;
        } else {
            $hideFrag = <<<HTML
<option value="0" selected>False</option>
<option value="1">True</option>
HTML;
        }

        $changeID = helper()->randomString(10);
        return <<<FORM
<li tabindex="0" class="menu-arranger-li max-width:350">
        <fieldset class="width:100% padding:default d:flex justify-content:center flex-d:column">
            <legend class="tonics-legend bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
                <span class="menu-arranger-text-head">More Settings</span>
                <button class="dropdown-toggle bg:transparent border:none cursor:pointer" aria-expanded="false" aria-label="Expand child menu">
                <svg class="icon:admin tonics-arrow-down color:white">
                    <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
                </svg>
            </button>
            </legend>
            <div class="menu-widget-information width:100% flex-d:column owl d:none">
                <div class="form-group">
                     <label class="menu-settings-handle-name" for="hideInUserEditForm-$changeID">Hide In User Edit Form
                         <select name="hideInUserEditForm" class="default-selector mg-b-plus-1" id="hideInUserEditForm-$changeID">
                            $hideFrag
                         </select>
                    </label>
                </div>
                $more
            </div>
        </fieldset>
</li>
FORM;
    }


    /**
     * @param $varKey
     * @param $data
     * @return void
     * @throws \Exception
     */
    public function defaultInputViewHandler($varKey, $data): void
    {
        $displayName =  (isset($data->fieldName)) ? $data->fieldName : 'Select';
        if (isset($data->inputName)){
            $inputName =  (isset(getPostData()[$data->inputName])) ? getPostData()[$data->inputName] : '';
            $defaultValue = (isset($data->defaultValue) && !empty($inputName)) ? $inputName : $data->defaultValue;
            $inputName = $data->inputName;
            addToGlobalVariable("{$varKey}_$inputName", ['Name' => $displayName, 'InputName' => $inputName, 'Value' => $defaultValue]);
        }
    }

    public function booleanOptionSelect($value = '1'): string
    {
        if ($value === '1'){
            return <<<HTML
<option value="0">False</option>
<option value="1" selected>True</option>
HTML;
        }

            return <<<HTML
<option value="0" selected>False</option>
<option value="1">True</option>
HTML;
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    /**
     * @return array
     */
    public function getFieldBoxSettings(): array
    {
        return $this->FieldBoxSettings;
    }

    /**
     * @param array $FieldBoxSettings
     */
    public function setFieldBoxSettings(array $FieldBoxSettings): void
    {
        $this->FieldBoxSettings = $FieldBoxSettings;
    }

    /**
     * @return null
     */
    public function getFieldSettings()
    {
        return $this->fieldSettings;
    }

    /**
     * @param null $fieldSettings
     */
    public function setFieldSettings($fieldSettings): void
    {
        $this->fieldSettings = $fieldSettings;
    }

    /**
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->fieldData;
    }

    /**
     * @param array|stdClass $data
     * @param array $inputNameAndRules
     * @return string
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function validationMake(array|stdClass $data, array $inputNameAndRules): string
    {
        $error = '';
        if ($this->validation === null) {
            $this->validation = $this->getFieldData()->getValidator();
        }
        $this->validation->reset();
        $validator = $this->validation->make($data, $inputNameAndRules);
        if ($validator->fails()) {
            $this->errorEmitted = true;
            $error = "<ul style='margin-left: 0;' class='form-error'>";
            foreach ($validator->errors() as $errors) {
                foreach ($errors as $msg) {
                    $error .= "<li><span class='text list-error-span'>⚠</span>$msg</li>";
                }
            }
            $error .= "</ul>";
        }

        return $error;
    }

    /**
     * @return bool
     */
    public function isErrorEmitted(): bool
    {
        return $this->errorEmitted;
    }

    /**
     * @param bool $errorEmitted
     */
    public function setErrorEmitted(bool $errorEmitted): void
    {
        $this->errorEmitted = $errorEmitted;
    }

    public function resetErrorEmission(): static
    {
        $this->errorEmitted = false;
        return $this;
    }

    /**
     * @return stdClass|null
     */
    public function getCurrentFieldBox(): ?stdClass
    {
        return $this->currentFieldBox;
    }

    /**
     * @param stdClass|null $currentFieldBox
     */
    public function setCurrentFieldBox(?stdClass $currentFieldBox): void
    {
        $this->currentFieldBox = $currentFieldBox;
    }

    /**
     * @return string
     */
    public function getSettingsType(): string
    {
        return $this->settingsType;
    }

    /**
     * @param string $settingsType
     * @return OnFieldMetaBox
     */
    public function setSettingsType(string $settingsType): OnFieldMetaBox
    {
        $this->settingsType = $settingsType;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function dispatchEvent(): OnFieldMetaBox
    {
        return event()->dispatch($this);
    }

    /**
     * @return bool
     */
    public function isDisableTopHTMLWrapper(): bool
    {
        return $this->disableTopHTMLWrapper;
    }

    /**
     * @param bool $disableTopHTMLWrapper
     * @return OnFieldMetaBox
     */
    public function setDisableTopHTMLWrapper(bool $disableTopHTMLWrapper): OnFieldMetaBox
    {
        $this->disableTopHTMLWrapper = $disableTopHTMLWrapper;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDisableBottomHTMLWrapper(): bool
    {
        return $this->disableBottomHTMLWrapper;
    }

    /**
     * @param bool $disableBottomHTMLWrapper
     * @return OnFieldMetaBox
     */
    public function setDisableBottomHTMLWrapper(bool $disableBottomHTMLWrapper): OnFieldMetaBox
    {
        $this->disableBottomHTMLWrapper = $disableBottomHTMLWrapper;
        return $this;
    }

}