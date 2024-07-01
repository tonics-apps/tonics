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

namespace App\Modules\Field\Events;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use JetBrains\PhpStorm\Pure;

class OnFieldFormHelper implements EventInterface
{
    private FieldData      $fieldData;
    private OnFieldMetaBox $fieldMetaBox;
    private string         $userForm = '';
    private array          $fieldIDS = [];

    /**
     * @param array $fieldIDS
     * @param FieldData $fieldData
     * @param array $postData
     * @param array $sortedFieldItems
     *
     * @throws \Exception
     */
    public function __construct (array $fieldIDS, FieldData $fieldData, array $postData = [], array $sortedFieldItems = [])
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
     *
     * @return string
     * @throws \Exception
     */
    public function generateHTMLFrags ($sortedFieldItems, array $postData = []): string
    {
        $oldPostData = getPostData();
        AppConfig::initLoaderMinimal()::addToGlobalVariable('Data', $postData);

        $htmlFrag = '';
        $sortedFieldItems = $this->generateTreeForSortedFieldItems($sortedFieldItems);
        foreach ($sortedFieldItems as $fieldBox) {
            $htmlFrag .= $this->getUsersFormFrag($fieldBox, $postData);
        }
        // restore oldPostData
        AppConfig::initLoaderMinimal()::addToGlobalVariable('Data', $oldPostData);
        return $htmlFrag;
    }

    /**
     * @param $sortedFieldItems
     *
     * @return mixed
     * @throws \Exception
     */
    public function generateTreeForSortedFieldItems ($sortedFieldItems): mixed
    {
        foreach ($sortedFieldItems as $k => $sortFieldItem) {
            $sortedFieldItems[$k] = helper()->generateTree(['parent_id' => 'field_parent_id', 'id' => 'field_id'], $sortFieldItem, onData: function ($field) {
                $field->field_options->{"_field"} = $field;
                $field->field_options->{"_field"}->canValidate = !empty($postData);
                return $field;
            });
        }

        return $sortedFieldItems;
    }

    /**
     * @throws \Exception
     */
    public function getUsersFormFrag ($fields, array $postData = []): string
    {
        $htmlFrag = '';

        # re-dispatch so we can get the form values
        $onFieldMetaBox = new OnFieldMetaBox();
        $onFieldMetaBox->setSettingsType(OnFieldMetaBox::OnUserSettingsType)->dispatchEvent();
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
    public function getViewFrag ($fields): string
    {
        $htmlFrag = '';
        # re-dispatch so we can get the form values
        $onFieldMetaBox = new OnFieldMetaBox();
        $onFieldMetaBox->setSettingsType(OnFieldMetaBox::OnViewSettingsType)->dispatchEvent();
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
     * @param bool $cachedData
     *
     * @return void
     * @throws \Exception
     */
    public function handleFrontEnd (array $fieldSlugs, array $postData = [], bool $cachedData = true): void
    {
        if (empty($fieldSlugs)) {
            return;
        }

        $fieldItemsSorted = $this->getFieldData()->getFieldSortedItems($fieldSlugs);

        $cachedKey = '';
        foreach ($fieldSlugs as $k => $fieldSlug) {
            $fieldSlugs[$k] = 'sortedField_' . $fieldSlug;
            $cachedKey .= 'sortedField_' . $fieldSlug;
        }
        AppConfig::initLoaderMinimal()::addToGlobalVariable('Data', $postData);
        $cachedKey = AppConfig::getCachePrefix() . $cachedKey . '_GlobalVariableData';
        if ($cachedData && apcu_exists($cachedKey)) {
            $data = [...apcu_fetch($cachedKey), ...getGlobalVariableData()];
            AppConfig::initLoaderMinimal()::setGlobalVariable($data);
        } else {
            try {
                $fieldItemsSorted = $this->generateTreeForSortedFieldItems($fieldItemsSorted);
                foreach ($fieldItemsSorted as $fields) {
                    $this->getViewFrag($fields);
                }
                apcu_store($cachedKey, getGlobalVariableData());
            } catch (\Exception $exception) {
                // log...
            }
        }
    }

    /**
     * @param $fieldIDS
     *
     * @return array
     * @throws \Exception
     */
    public function getFieldSortedItems ($fieldIDS): array
    {
        $sortedFieldItems = [];
        $fieldData = $this->fieldData;
        if (empty($sortedFieldItems)) {
            if (empty($fieldIDS)) {
                return $sortedFieldItems;
            }


            $fieldItems = null;
            db(onGetDB: function (TonicsQuery $db) use ($fieldIDS, $fieldData, &$fieldItems) {

                $fieldTable = $fieldData->getFieldTable();
                $fieldItemsTable = $fieldData->getFieldItemsTable();
                $cols = $fieldData->getFieldAndFieldItemsCols();

                $fieldItems = $db->Select($cols)->From($fieldItemsTable)
                    ->Join($fieldTable, "$fieldTable.field_id", "$fieldItemsTable.fk_field_id")
                    ->WhereIn('fk_field_id', $fieldIDS)->OrderBy('id')->FetchResult();
            });

            foreach ($fieldItems as $fieldItem) {
                $fieldOption = json_decode($fieldItem->field_options);
                $fieldItem->field_options = $fieldOption;
                $sortedFieldItems[$fieldItem->fk_field_id][] = $fieldItem;
            }

            $sortedFieldItemsOnPriority = [];
            foreach ($fieldIDS as $fieldID) {
                if (isset($sortedFieldItems[$fieldID])) {
                    $sortedFieldItemsOnPriority[$fieldID] = $sortedFieldItems[$fieldID];
                }
            }

            $sortedFieldItems = $sortedFieldItemsOnPriority;
        }

        return $sortedFieldItems;
    }

    /**
     * @inheritDoc
     */
    public function event (): static
    {
        return $this;
    }

    /**
     * @return FieldData
     */
    public function getFieldData (): FieldData
    {
        return $this->fieldData;
    }

    /**
     * @return string
     */
    public function getHTMLFrag (): string
    {
        return $this->userForm;
    }

    /**
     * @param string $userForm
     */
    public function setUserForm (string $userForm): void
    {
        $this->userForm = $userForm;
    }

    #[Pure] public function hasError (): bool
    {
        return $this->fieldMetaBox->isErrorEmitted();
    }

    /**
     * @return array
     */
    public function getFieldIDS (): array
    {
        return $this->fieldIDS;
    }

    /**
     * @param array $fieldIDS
     */
    public function setFieldIDS (array $fieldIDS): void
    {
        $this->fieldIDS = $fieldIDS;
    }

    /**
     * @return OnFieldMetaBox
     */
    public function getFieldMetaBox (): OnFieldMetaBox
    {
        return $this->fieldMetaBox;
    }
}