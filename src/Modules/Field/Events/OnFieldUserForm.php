<?php

namespace App\Modules\Field\Events;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;
use JetBrains\PhpStorm\Pure;

class OnFieldUserForm implements EventInterface
{
    private FieldData $fieldData;
    private OnFieldMetaBox $fieldMetaBox;
    private string $userForm = '';
    private array $fieldIDS = [];

    /**
     * @param array $fieldIDS
     * @param FieldData $fieldData
     * @param array $postData
     * @param bool $viewProcessing
     * @param array $sortedFieldItems
     * @throws \Exception
     */
    public function __construct(array $fieldIDS, FieldData $fieldData, array $postData = [], bool $viewProcessing = false, array $sortedFieldItems = [])
    {
        $htmlFrag = '';
        $this->fieldData = $fieldData;
        $this->fieldIDS = $fieldIDS;
        if (!empty($fieldIDS)){
            $sortedFieldItems = (empty($sortedFieldItems)) ? $this->getFieldSortedItems($fieldIDS): $sortedFieldItems;
            $htmlFrag = $this->generateHTMLFrags($sortedFieldItems, $postData, $viewProcessing);
        }

        $this->userForm = $htmlFrag;
    }

    /**
     * @param $sortedFieldItems
     * @param array $postData
     * @param bool $viewProcessing
     * @return string
     * @throws \Exception
     */
    public function generateHTMLFrags($sortedFieldItems, array $postData = [], bool $viewProcessing = false): string
    {
        $htmlFrag = '';
        # re-dispatch so we can get the form values
        $onFieldMetaBox = new OnFieldMetaBox();
        /**@var $onFieldMetaBox OnFieldMetaBox */
        $onFieldMetaBox = event()->dispatch($onFieldMetaBox);
        foreach ($sortedFieldItems as $k => $sortFieldItem){
            $sortedFieldItems[$k] = helper()->generateTree(['parent_id' => 'field_parent_id', 'id' => 'field_id'], $sortFieldItem, onData: function ($field) use ($viewProcessing, $postData, $onFieldMetaBox, &$htmlFrag) {
                $settings = $onFieldMetaBox->getFieldMetaSettings($field->field_options->field_slug);
                $scriptPath = isset($settings->scriptPath) && !empty($settings->scriptPath) ? "data-script_path={$settings->scriptPath}" : '';
                $field->field_options->{"_field"} = $field;
                $field->field_options->{"_field"}->canValidate = !empty($postData);
                $field->field_options->{"_field"}->postData = $postData;

                if ($viewProcessing === false){
                    $uniqueHash =  $field->field_options->field_slug_unique_hash;
                    $field->field_options->{"_topHTMLWrapper"} = function ($name, $slug, $hash = '') use ($scriptPath, $postData, $uniqueHash){
                        $hash = $hash ?: $uniqueHash;
                        $hideField = (isset($postData['hide_field'][$hash])) ? "<input type='hidden' name='hide_field[$hash]' value='$hash'>" : '';
                        $toggle = [
                            'button' => 'dropdown-toggle bg:transparent border:none cursor:pointer toggle-on',
                            'aria-expanded' => 'true',
                            'aria-label' => 'Collapse child menu',
                            'svg' => 'icon:admin tonics-arrow-down color:white',
                            'use' => '#tonics-arrow-down',
                            'div' => 'swing-in-top-fwd d:flex',
                        ];
                        if (!empty($hideField)){
                            $toggle = [
                                'button' => 'dropdown-toggle bg:transparent border:none cursor:pointer',
                                'aria-expanded' => 'false',
                                'aria-label' => 'Expand child menu',
                                'svg' => 'icon:admin tonics-arrow-up color:white',
                                'use' => '#tonics-arrow-up',
                                'div' => 'swing-out-top-fwd d:none',
                            ];
                        }
                        return <<<HTML
<li tabIndex="0"
class="width:100% draggable menu-arranger-li cursor:move field-builder-items"
$scriptPath>
        <fieldset
            class="width:100% padding:default d:flex justify-content:center flex-d:column owl">
            <legend class="bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
                <span class="menu-arranger-text-head">$name</span>
                <button class="{$toggle['button']}"
                        aria-expanded="{$toggle['aria-expanded']}" aria-label="{$toggle['aria-label']}" type="button">
                    <svg class="{$toggle['svg']}">
                        <use class="svgUse" xlink:href="{$toggle['use']}"></use>
                    </svg>
                </button>
            </legend>
            <div role="form" data-widget-form="true" class="widgetSettings flex-d:column menu-widget-information cursor:pointer owl width:100% margin-top:0 {$toggle['div']}">
                $hideField
                <input type="hidden" name="field_slug" value="$slug">
                <input type="hidden" name="field_slug_unique_hash" value="$hash">
HTML;
                    };
                    $field->field_options->{"_bottomHTMLWrapper"} = <<<HTML
            </div>
        </fieldset>
    </li>
HTML;
                }
                return $field;
            });
        }

        foreach ($sortedFieldItems as $fieldBox){
            foreach ($fieldBox as $field){
                if ($viewProcessing){
                    $htmlFrag .= $onFieldMetaBox->getViewProcessingFrag($field->field_options->field_slug, $field->field_options);
                } else {
                    $htmlFrag .= $onFieldMetaBox->getUsersForm($field->field_options->field_slug, $field->field_options);
                }
                # clear closure, this way, things are cacheable hia.
                $field->field_options->{"_topHTMLWrapper"} = null;
            }
        }
        $this->fieldMetaBox = $onFieldMetaBox;

        return $htmlFrag;
    }

    /**
     * @param $fieldIDS
     * @return array
     * @throws \Exception
     */
    public function getFieldSortedItems($fieldIDS): array
    {
        $sortedFieldItems = []; $fieldData = $this->fieldData;
        if (empty($sortedFieldItems)){
            if (empty($fieldIDS)){
                return $sortedFieldItems;
            }
            $questionMarks = helper()->returnRequiredQuestionMarks($fieldIDS);
            $fieldItems =  $fieldData->selectWithCondition($fieldData->getFieldItemsTable(), ['*'], "fk_field_id IN ($questionMarks) ORDER BY id", $fieldIDS, false);
            foreach ($fieldItems as $fieldItem){
                $fieldOption = json_decode($fieldItem->field_options);
                $fieldItem->field_options = $fieldOption;
                $sortedFieldItems[$fieldItem->fk_field_id][] = $fieldItem;
                $globalKey = (isset($fieldOption->inputName) && !empty($fieldOption->inputName)) ? $fieldOption->inputName : $fieldOption->field_slug;
                AppConfig::initLoaderMinimal()::addToGlobalVariable($globalKey, $fieldOption);
            }

            ksort($sortedFieldItems);
        }

        return $sortedFieldItems;
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    /**
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->fieldData;
    }

    /**
     * @return string
     */
    public function getHTMLFrag(): string
    {
        return $this->userForm;
    }

    /**
     * @param string $userForm
     */
    public function setUserForm(string $userForm): void
    {
        $this->userForm = $userForm;
    }

    #[Pure] public function hasError(): bool
    {
        return $this->fieldMetaBox->isErrorEmitted();
    }

    /**
     * @return array
     */
    public function getFieldIDS(): array
    {
        return $this->fieldIDS;
    }

    /**
     * @param array $fieldIDS
     */
    public function setFieldIDS(array $fieldIDS): void
    {
        $this->fieldIDS = $fieldIDS;
    }
}