<?php

namespace App\Modules\Field\Events;

use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;
use JetBrains\PhpStorm\Pure;

class OnFieldUserForm implements EventInterface
{
    private FieldData $fieldData;
    private OnFieldMetaBox $fieldMetaBox;
    private string $userForm = '';

    /**
     * @throws \Exception
     */
    public function __construct(array $fieldIDS, FieldData $fieldData, $postData = [], $viewProcessing = false)
    {
        $htmlFrag = '';
        if (!empty($fieldIDS)){
            $this->fieldData = $fieldData;
            $questionMarks = helper()->returnRequiredQuestionMarks($fieldIDS);
            $fieldItems =  $fieldData->selectWithCondition($fieldData->getFieldItemsTable(), ['*'], "fk_field_id IN ($questionMarks) ORDER BY id", $fieldIDS, false);
            $sortFieldItemsByFK = [];
            foreach ($fieldItems as $fieldItem){
                $sortFieldItemsByFK[$fieldItem->fk_field_id][] = $fieldItem;
            }
            ksort($sortFieldItemsByFK);
            # re-dispatch so we can get the form values
            $onFieldMetaBox = new OnFieldMetaBox();
            /**@var $onFieldMetaBox OnFieldMetaBox */
            $onFieldMetaBox = event()->dispatch($onFieldMetaBox);
            foreach ($sortFieldItemsByFK as $k => $sortFieldItem){
                $sortFieldItemsByFK[$k] = helper()->generateTree(['parent_id' => 'field_parent_id', 'id' => 'field_id'], $sortFieldItem, onData: function ($field) use ($postData, $onFieldMetaBox, &$htmlFrag) {
                    $field->field_options = json_decode($field->field_options);
                    $settings = $onFieldMetaBox->getFieldMetaSettings($field->field_options->field_slug);
                    $scriptPath = isset($settings->scriptPath) && !empty($settings->scriptPath) ? "data-script_path={$settings->scriptPath}" : '';
                    $field->field_options->{"_field"} = $field;
                    $field->field_options->{"_field"}->canValidate = !empty($postData);
                    $field->field_options->{"_field"}->postData = $postData;
                    $field->field_options->{"_topHTMLWrapper"} = function ($name, $slug) use ($scriptPath){
                        return <<<HTML
<li tabIndex="0"
class="width:100% draggable menu-arranger-li cursor:move field-builder-items"
$scriptPath>
        <fieldset
            class="width:100% padding:default d:flex justify-content:center flex-d:column owl">
            <legend class="bg:pure-black color:white padding:default d:flex flex-gap:small align-items:center">
                <span class="menu-arranger-text-head">$name</span>
                <button class="dropdown-toggle bg:transparent border:none cursor:pointer"
                        aria-expanded="true" aria-label="Collapse child menu" type="button">
                    <svg class="icon:admin tonics-arrow-down color:white">
                        <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
                    </svg>
                </button>
            </legend>
            <div role="form" data-widget-form="true" class="widgetSettings flex-d:column menu-widget-information cursor:pointer owl width:100% margin-top:0 swing-in-top-fwd d:flex">
                <input type="hidden" name="field_slug" value="$slug">
HTML;
                    };
                    $field->field_options->{"_bottomHTMLWrapper"} = <<<HTML
            </div>
        </fieldset>
    </li>
HTML;
                    return $field;
                });
            }

            foreach ($sortFieldItemsByFK as $fieldBox){
                foreach ($fieldBox as $field){
                    if ($viewProcessing){
                        $htmlFrag .= $onFieldMetaBox->getViewProcessingFrag($field->field_options->field_slug, $field->field_options);
                    } else {
                        $htmlFrag .= $onFieldMetaBox->getUsersForm($field->field_options->field_slug, $field->field_options);
                    }
                }
            }
            $this->fieldMetaBox = $onFieldMetaBox;
        }

        $this->userForm = $htmlFrag;
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
}