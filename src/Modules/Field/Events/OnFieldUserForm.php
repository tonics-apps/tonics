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

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Tables;
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
     * @param array $sortedFieldItems
     * @throws \Exception
     */
    public function __construct(array $fieldIDS, FieldData $fieldData, array $postData = [], array $sortedFieldItems = [])
    {
        $htmlFrag = '';
        $this->fieldData = $fieldData;
        $this->fieldIDS = $fieldIDS;
        if (!empty($fieldIDS)) {
            $sortedFieldItems = (empty($sortedFieldItems)) ? $this->getFieldSortedItems($fieldIDS) : $sortedFieldItems;
            $htmlFrag = $this->generateHTMLFrags($sortedFieldItems, $postData);
        }

        $this->userForm = $htmlFrag;
    }

    /**
     * @param $sortedFieldItems
     * @param array $postData
     * @return string
     * @throws \Exception
     */
    public function generateHTMLFrags($sortedFieldItems, array $postData = []): string
    {
        AppConfig::initLoaderMinimal()::addToGlobalVariable('Data', $postData);
        $htmlFrag = '';

        foreach ($sortedFieldItems as $k => $sortFieldItem) {
            $sortedFieldItems[$k] = helper()->generateTree(['parent_id' => 'field_parent_id', 'id' => 'field_id'], $sortFieldItem, onData: function ($field) {
                $field->field_options->{"_field"} = $field;
                $field->field_options->{"_field"}->canValidate = !empty($postData);
                return $field;
            });
        }

        foreach ($sortedFieldItems as $fieldBox) {
            $htmlFrag .= $this->getUsersFormFrag($fieldBox, $postData);
        }
        return $htmlFrag;
    }

    /**
     * @throws \Exception
     */
    public function getUsersFormFrag($fields, array $postData = []): string
    {
        $htmlFrag = '';
        # re-dispatch so we can get the form values=
        /**@var $onFieldMetaBox OnFieldMetaBox */
        $onFieldMetaBox = event()->dispatch(new OnFieldMetaBox());
        foreach ($fields as $field) {
            $field->field_options->{"_field"} = $field;
            $field->field_options->{"_field"}->canValidate = !empty($postData);
            $htmlFrag .= $onFieldMetaBox->getUsersForm($field->field_options->field_slug, $field->field_options);
        }
        $this->fieldMetaBox = $onFieldMetaBox;
        return $htmlFrag;
    }

    /**
     * @throws \Exception
     */
    public function getViewFrag($fields): string
    {
        $htmlFrag = '';
        # re-dispatch so we can get the form values=
        /**@var $onFieldMetaBox OnFieldMetaBox */
        $onFieldMetaBox = event()->dispatch(new OnFieldMetaBox());
        foreach ($fields as $field) {
            $field->field_options->{"_field"} = $field;
            $htmlFrag .= $onFieldMetaBox->getViewProcessingFrag($field->field_options->field_slug, $field->field_options);
        }
        $this->fieldMetaBox = $onFieldMetaBox;
        return $htmlFrag;
    }

    /**
     * @param array $fieldSlugs
     * @param array $postData
     * @return void
     */
    public function handleFrontEnd(array $fieldSlugs, array $postData = []): void
    {
        if (empty($fieldSlugs)) {
            return;
        }

        foreach ($fieldSlugs as $k => $fieldSlug) {
            $fieldSlugs[$k] = 'sortedField_' . $fieldSlug;
        }

        $table = Tables::getTable(Tables::GLOBAL);

        try {
            AppConfig::initLoaderMinimal()::addToGlobalVariable('Data', $postData);
            $questionMarks = helper()->returnRequiredQuestionMarks($fieldSlugs);
            $fieldItemsSortedString = db()->run("SELECT `value` FROM $table WHERE `key` IN ($questionMarks)", ...$fieldSlugs);

            if (!is_array($fieldItemsSortedString)) {
                return;
            }

            # re-dispatch so we can get the form values
            $onFieldMetaBox = new OnFieldMetaBox();
            /**@var $onFieldMetaBox OnFieldMetaBox */
            $onFieldMetaBox = event()->dispatch($onFieldMetaBox);
            foreach ($fieldItemsSortedString as $fields) {
                if (isset($fields->value)) {
                    $fields = json_decode($fields->value ?: '') ?? [];
                    foreach ($fields as $field) {
                        $field->field_options->{"_field"} = $field;
                        $onFieldMetaBox->getViewProcessingFrag($field->field_options->field_slug, $field->field_options);
                    }
                }
            }
        } catch (\Exception $exception) {
            dd($exception->getMessage());
            // log...
        }
    }

    /**
     * @param $fieldIDS
     * @return array
     * @throws \Exception
     */
    public function getFieldSortedItems($fieldIDS): array
    {
        $sortedFieldItems = [];
        $fieldData = $this->fieldData;
        if (empty($sortedFieldItems)) {
            if (empty($fieldIDS)) {
                return $sortedFieldItems;
            }
            $questionMarks = helper()->returnRequiredQuestionMarks($fieldIDS);
            $fieldTable = $fieldData->getFieldTable(); $fieldItemsTable = $fieldData->getFieldItemsTable();
            $cols = $fieldData->getFieldAndFieldItemsCols();

            $sql = <<<SQL
SELECT $cols FROM $fieldItemsTable 
JOIN $fieldTable ON $fieldTable.field_id = $fieldItemsTable.fk_field_id
WHERE fk_field_id IN ($questionMarks)
ORDER BY id;
SQL;
            $fieldItems = db()->run($sql, ...$fieldIDS);
            foreach ($fieldItems as $fieldItem) {
                $fieldOption = json_decode($fieldItem->field_options);
                $fieldItem->field_options = $fieldOption;
                $sortedFieldItems[$fieldItem->fk_field_id][] = $fieldItem;
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

    /**
     * @return OnFieldMetaBox
     */
    public function getFieldMetaBox(): OnFieldMetaBox
    {
        return $this->fieldMetaBox;
    }
}