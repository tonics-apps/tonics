<?php

namespace App\Modules\Field\Events;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\OnSelectTonicsTemplateHooks;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsDomParser\Node\NodeTypes\Element;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;
use Devsrealm\TonicsTemplateSystem\Content;
use Devsrealm\TonicsTemplateSystem\Loader\TonicsTemplateArrayLoader;
use Devsrealm\TonicsTemplateSystem\TonicsView;
use Devsrealm\TonicsValidation\Validation;
use stdClass;

class OnFieldMetaBox implements EventInterface
{

    private array $FieldBoxSettings = [];
    private $fieldSettings = null;
    private FieldData $fieldData;
    private ?Validation $validation = null;
    private bool $errorEmitted = false;
    private ?stdClass $currentFieldBox = null;
    private object $onSelectHooks;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->fieldData = new FieldData();
        $this->onSelectHooks = event()->dispatch(new OnSelectTonicsTemplateHooks());
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
     * @param $settings
     * @param $value
     * @return string
     * @throws \Exception
     */
    public function getTemplateEngineValue($settings, $value): string
    {
        if (isset($settings->templateEngine)) {
            $name = $settings->templateEngine;
            $tonicsTemplateEngine = AppConfig::initLoaderOthers()->getTonicsTemplateEngines();
            if ($tonicsTemplateEngine->exist($name)) {
                $engine = $tonicsTemplateEngine->getTemplateEngine($name);
                $postData = (isset($settings->_field->postData)) ? $settings->_field->postData : [];
                AppConfig::initLoaderMinimal()::addToGlobalVariable('Data', $postData);
                $engine->setVariableData(AppConfig::initLoaderMinimal()::getGlobalVariable());
                $engine->splitStringCharByChar($value);
                $engine->reset()->tokenize();
                return $engine->outputContentData($engine->getContent()->getContents());
            }
        }

        return $value;
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
     * @return string
     * @throws \Exception
     */
    public function _topHTMLWrapper(string $name, $data): string
    {
        $slug = isset($data->field_slug) ? $data->field_slug : '';
        $hash = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $postData = getPostData();

        $settings = $this->currentFieldBox ?? $this->getFieldMetaSettings($slug);
        $scriptPath = isset($settings->scriptPath) && !empty($settings->scriptPath) ? "data-script_path={$settings->scriptPath}" : '';
        $hideField = (isset($postData['hide_field'][$hash])) ? "<input type='hidden' name='hide_field[$hash]' value='$hash'>" : '';

        // meaning settingsForm
        if (empty($hideField) && empty($postData)) {
            $hideField = ' ';
        }

        $toggle = [
            'button' => 'dropdown-toggle bg:transparent border:none cursor:pointer toggle-on',
            'aria-expanded' => 'true',
            'aria-label' => 'Collapse child menu',
            'svg' => 'icon:admin tonics-arrow-up color:white',
            'use' => '#tonics-arrow-up',
            'div' => 'swing-in-top-fwd d:flex',
        ];
        if (!empty($hideField)) {
            $toggle = [
                'button' => 'dropdown-toggle bg:transparent border:none cursor:pointer',
                'aria-expanded' => 'false',
                'aria-label' => 'Expand child menu',
                'svg' => 'icon:admin tonics-arrow-down color:white',
                'use' => '#tonics-arrow-down',
                'div' => 'swing-out-top-fwd d:none',
            ];
        }

        $isEditorLi = (url()->getHeaderByKey('action') === 'getFieldItems') ? 'contenteditable="false"' : '';
        $isEditorWidgetSettings = (url()->getHeaderByKey('action') === 'getFieldItems') ? 'contenteditable="true"' : '';

        $field_table_slug = (isset($data->_field->main_field_slug)) ? "<input type='hidden' name='main_field_slug' value='{$data->_field->main_field_slug}'>" : '';
        return <<<HTML
<li $isEditorLi tabIndex="0"
class="width:100% draggable menu-arranger-li cursor:move field-builder-items"
$scriptPath>
        <fieldset
            class="width:100% padding:default d:flex justify-content:center flex-d:column owl">
            <legend class="tonics-legend bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
                <span class="menu-arranger-text-head">$name</span>
                <button class="{$toggle['button']}"
                        aria-expanded="{$toggle['aria-expanded']}" aria-label="{$toggle['aria-label']}" type="button">
                    <svg class="{$toggle['svg']}">
                        <use class="svgUse" xlink:href="{$toggle['use']}"></use>
                    </svg>
                </button>
            </legend>
            <div $isEditorWidgetSettings role="form" data-widget-form="true" class="widgetSettings flex-d:column menu-widget-information cursor:pointer owl width:100% margin-top:0 {$toggle['div']}">
                $hideField
                <input type="hidden" name="field_slug" value="$slug">
                $field_table_slug
                <input type="hidden" name="field_slug_unique_hash" value="$hash">
HTML;
    }

    public function _bottomHTMLWrapper(bool $isUserForm = false): string
    {
        if ($isUserForm) {
            return <<<HTML
            </div>
        </fieldset>
    </li>
HTML;
        }

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

    /**
     * @throws \Exception
     */
    public function getTemplateEngineFrag($data = null): string
    {
        $current = (isset($data->templateEngine)) ? $data->templateEngine : '';
        $hookName = (isset($data->nativeTemplateHook)) ? $data->nativeTemplateHook : '';
        $tonicsTemplateFrag = (isset($data->tonicsTemplateFrag)) ? $data->tonicsTemplateFrag : '';
        $engine = AppConfig::initLoaderOthers()->getTonicsTemplateEngines();
        $engineFrag = '<option value="" selected>None</option>';
        foreach ($engine->getTemplateEngineNames() as $engineName) {
            $engineSelected = ($engineName === $current) ? 'selected' : '';
            $engineFrag .= <<<HTML
<option value="$engineName" $engineSelected>$engineName</option>
HTML;
        }

        $hookFrag = '<option value="" selected>None</option>';
        foreach ($this->getOnSelectHooks()->getTemplateHooks() as $hook) {
            $hookSelected = ($hook === $hookName) ? 'selected' : '';
            $hookFrag .= <<<HTML
<option value="$hook" $hookSelected>$hook</option>
HTML;
        }

        $changeID = helper()->randomString(10);
        return <<<FORM
<li tabindex="0" class="menu-arranger-li max-width:350">
        <fieldset class="width:100% padding:default d:flex justify-content:center flex-d:column">
            <legend class="tonics-legend bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
                <span class="menu-arranger-text-head">Template Engine</span>
                <button class="dropdown-toggle bg:transparent border:none cursor:pointer" aria-expanded="false" aria-label="Expand child menu">
                <svg class="icon:admin tonics-arrow-down color:white">
                    <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
                </svg>
            </button>
            </legend>
            <div class="menu-widget-information width:100% flex-d:column owl d:none">
                <div class="form-group">
                     <label class="menu-settings-handle-name" for="templateEngine-$changeID">Select Template Engines (Non-Native Needs Hook Placement)
                         <select name="templateEngine" class="default-selector mg-b-plus-1" id="templateEngine-$changeID">
                            $engineFrag
                         </select>
                    </label>
                </div>
                <div class="form-group">
                     <label class="menu-settings-handle-name" for="nativeTemplateHook-$changeID">Hook Into (Supports All Engine)
                         <select name="nativeTemplateHook" class="default-selector mg-b-plus-1" id="nativeTemplateHook-$changeID">
                            $hookFrag
                         </select>
                    </label>
                </div>
                <div class="form-group">
                     <label class="menu-settings-handle-name" for="tonicsTemplateFrag-$changeID">Template Text: (`Native` Engine Only Supports Hook)
                            <textarea rows="10" id="tonicsTemplateFrag-$changeID" name="tonicsTemplateFrag" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
                             placeholder="Start writing the template logic, you have access to the template functions">$tonicsTemplateFrag</textarea>
                    </label>
                </div>
            </div>
        </fieldset>
</li>
FORM;
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
     * @param $data
     * @return void
     * @throws \Exception
     */
    public function handleTemplateEngineView($data): void
    {
        $engineName = (isset($data->templateEngine)) ? $data->templateEngine : '';
        $content = (isset($data->tonicsTemplateFrag)) ? $data->tonicsTemplateFrag : '';
        $hookName = (isset($data->nativeTemplateHook)) ? $data->nativeTemplateHook : '';


        if (templateEngines()->exist($engineName) && !empty($content)) {
            $tonicsEngine = templateEngines()->getTemplateEngine($engineName);
            if ($engineName === 'Native') {
                loadTemplateBase();
            } else {
                $tonicsEngine->setTemplateLoader(new TonicsTemplateArrayLoader(['template' => $content]));
                $content = $tonicsEngine->render('template', TonicsView::RENDER_CONCATENATE);
            }

            if ($this->getOnSelectHooks()->hookExist($hookName)) {
                $content = <<<HTML
[[place_into('$hookName')
    $content
]]
HTML;
            }

            ## De-Activate Some Extensions
            // getBaseTemplate()->removeModeHandlers(['combine', 'import', 'event', '__event']);

            getBaseTemplate()->setVariableData(AppConfig::initLoaderMinimal()::getGlobalVariable());
            # Keep the old content and fork a new one since we don't want to mess with the initialContent
            $initialContent = getBaseTemplate()->getContent();
            $newContentInstance = new Content();
            getBaseTemplate()->setContent($newContentInstance); // this would be used to import blocks too...
            getBaseTemplate()->splitStringCharByChar($content);
            getBaseTemplate()->reset();
            getBaseTemplate()->tokenize()->setContent($initialContent->addBlocks($newContentInstance->getBlocks())); // tokenize and reset the initial content
        }
    }

    /**
     * @param $varKey
     * @param $data
     * @return string
     * @throws \Exception
     */
    public function defaultInputViewHandler($varKey, $data): void
    {
        $displayName =  (isset($data->fieldName)) ? $data->fieldName : 'Select';
        if (isset($data->inputName)){
            $inputName =  (isset(getPostData()[$data->inputName])) ? getPostData()[$data->inputName] : '';
            $defaultValue = (isset($data->defaultValue) && !empty($inputName)) ? $inputName : $data->defaultValue;
            $inputName = (isset($data->inputName)) ? $data->inputName : '';
            addToGlobalVariable("{$varKey}_$inputName", ['Name' => $displayName, 'InputName' => $inputName, 'Value' => $defaultValue]);
            $this->handleTemplateEngineView($data);
        }
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
     * @throws \Exception
     */
    public function flatHTMLTagAttributes(string $attributes): string
    {
        $html = <<<HTML
<html $attributes>
</html>
HTML;
        /**@var $element Element */
        $element = dom()->parse($html)->getTree()->getDocument()->getNodes()[0];
        $frag = '';
        foreach ($element->attributes() as $attribute) {
            $frag .= ($attribute->value !== '') ? ' ' . $attribute->name . '="' . $attribute->value . '" ' : $attribute->name;
        }
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function handleViewProcessingFrag($data = null): string
    {
        $handleViewProcessing = (isset($data->handleViewProcessing)) ? $data->handleViewProcessing : '';
        if ($handleViewProcessing === '1') {
            $frag = <<<HTML
<option value="0">False</option>
<option value="1" selected>True</option>
HTML;
        } else {
            $frag = <<<HTML
<option value="0" selected>False</option>
<option value="1">True</option>
HTML;
        }

        $changeID = helper()->randomString(10);
        return <<<FORM
<div class="form-group">
     <label class="menu-settings-handle-name" for="handleViewProcessing-$changeID">Automatically Handle View Processing
     <select name="handleViewProcessing" class="default-selector mg-b-plus-1" id="handleViewProcessing-$changeID">
        $frag
     </select>
    </label>
</div>
FORM;
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
     * @return OnSelectTonicsTemplateHooks
     */
    public function getOnSelectHooks(): object
    {
        return $this->onSelectHooks;
    }

}