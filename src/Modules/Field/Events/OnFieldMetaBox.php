<?php

namespace App\Modules\Field\Events;

use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsDomParser\Node\NodeTypes\Element;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;
use Devsrealm\TonicsValidation\Validation;
use stdClass;

class OnFieldMetaBox implements EventInterface
{

    private array $FieldBoxSettings = [];
    private $fieldSettings = null;
    private FieldData $fieldData;
    private ?Validation $validation = null;
    private bool $errorEmitted = false;

    public function __construct()
    {
        $this->fieldData = new FieldData();
    }

    /**
     * @throws \Exception
     */
    public function addFieldBox
    (
        string $name,
        string $description = '',
        string $category = 'input',
        string $scriptPath = '',
        callable $settingsForm = null,
        callable $userForm = null,
        callable $handleViewProcessing = null,
    )
    {
        $nameKey = helper()->slug($name);
        $category = strtolower($category);
        if(!key_exists($nameKey, $this->FieldBoxSettings)){
            $this->FieldBoxSettings[$category][$nameKey] = (object)[
                'name' => $name,
                'description' => $description,
                'scriptPath' => $scriptPath,
                'settingsForm' => $settingsForm ?? '',
                'userForm' => $userForm ?? '',
                'handleViewProcessing' => $handleViewProcessing ?? '',
            ];
        }
    }

    public function generateFieldMetaBox(): string
    {
        $htmlFrag = '';
        if (empty($this->FieldBoxSettings)){
            return $htmlFrag;
        }

        foreach ($this->FieldBoxSettings as $fieldCategorySlug => $fieldCategory){
            $checkBoxFrag = '';
            $category = ucfirst($fieldCategorySlug);

            foreach ($fieldCategory as $fieldSlug => $settings){
                $scriptPath = (empty($settings->scriptPath)) ? '': "data-script_path=$settings->scriptPath";
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
        <legend class="bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
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
        $fieldCategory = $explodeSlug[0];
        $fieldSlug = $explodeSlug[1];
        if(!isset($this->FieldBoxSettings[$fieldCategory][$fieldSlug])){
            return '';
        }

        $formCallback = $this->FieldBoxSettings[$fieldCategory][$fieldSlug]->settingsForm;
        if (!is_callable($formCallback)){
            return '';
        }
        return $formCallback($settings);
    }

    /**
     * @param $fieldSlug
     * @param null $settings
     * @return string
     */
    public function getUsersForm($fieldSlug, $settings = null): string
    {
        $explodeSlug = explode('_', $fieldSlug);
        $fieldCategory = $explodeSlug[0];
        $fieldSlug = $explodeSlug[1];
        if(!isset($this->FieldBoxSettings[$fieldCategory][$fieldSlug])){
            return '';
        }

        $formCallback = $this->FieldBoxSettings[$fieldCategory][$fieldSlug]->userForm;
        if (!is_callable($formCallback)){
            return '';
        }
        return $formCallback($settings);
    }

    /**
     * @param $fieldSlug
     * @param null $settings
     * @return string
     */
    public function getViewProcessingFrag($fieldSlug, $settings = null): string
    {
        $explodeSlug = explode('_', $fieldSlug);
        $fieldCategory = $explodeSlug[0];
        $fieldSlug = $explodeSlug[1];
        if(!isset($this->FieldBoxSettings[$fieldCategory][$fieldSlug])){
            return '';
        }

        $formCallback = $this->FieldBoxSettings[$fieldCategory][$fieldSlug]->handleViewProcessing;
        if (!is_callable($formCallback)){
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
        $fieldCategory = $explodeSlug[0];
        $fieldSlug = $explodeSlug[1];
        if(!isset($this->FieldBoxSettings[$fieldCategory][$fieldSlug])){
            return [];
        }

        return $this->FieldBoxSettings[$fieldCategory][$fieldSlug];
    }

    public function getRealName($fieldSlug): string
    {
        $explodeSlug = explode('_', $fieldSlug);
        $fieldCategory = $explodeSlug[0];
        $fieldSlug = $explodeSlug[1];

        return isset($this->FieldBoxSettings[$fieldCategory][$fieldSlug]) ? $this->FieldBoxSettings[$fieldCategory][$fieldSlug]->name : '';
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
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function validationMake(array|stdClass $data, array $inputNameAndRules): string
    {
        $error = '';
        if ($this->validation === null){
            $this->validation = $this->getFieldData()->getValidator();
        }
        $this->validation->reset();
        $validator = $this->validation->make($data, $inputNameAndRules);
        if ($validator->fails()){
            $this->errorEmitted = true;
            $error = "<ul style='margin-left: 0;' class='form-error'>";
            foreach ($validator->errors() as $errors){
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
        foreach ($element->attributes() as $attribute){
            $frag .= ($attribute->value !== '') ?  ' ' .$attribute->name . '="'. $attribute->value . '" ' : $attribute->name;
        }
        return $frag;
    }

    public function handleViewProcessingFrag(string $handleViewProcessing = ''): string
    {
        if ($handleViewProcessing === '1'){
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

        return $frag;
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
}