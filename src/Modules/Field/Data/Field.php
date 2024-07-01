<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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

use App\Modules\Field\Events\OnFieldMetaBox;
use stdClass;

class Field
{
    private string    $field_slug             = '';
    private string    $field_slug_unique_hash = '';
    private string    $field_input_name       = '';
    private string    $fieldName              = '';
    private string    $fieldCell              = '';
    private string    $inputName              = '';
    private string    $defaultValue           = '';
    private ?string   $row                    = null;
    private ?string   $column                 = null;
    private ?string   $grid_template_col      = null;
    private ?string   $info                   = null;
    private ?string   $useTab                 = null;
    private ?string   $group                  = null;
    private ?stdClass $_field                 = null;
    private array     $field_validation       = [];
    private array     $field_sanitization     = [];


    /**
     * Field constructor.
     *
     * The Defaults should look something like:
     *
     * ```
     * $defaults = [
     *      'field_slug' => 'default_slug',
     *      'fieldName' => 'this is a field name',
     *      // and so on...
     *      '_field' => {} // object
     * ];
     * ```
     *
     * @param OnFieldMetaBox $onFieldMetaBox An object containing field data.
     * @param array $defaults                An associative array of default values for the fields.
     *
     * @throws \Exception
     */
    public function __construct (private OnFieldMetaBox $onFieldMetaBox, array $defaults = [])
    {
        $this->processData($onFieldMetaBox, $defaults);
    }

    /**
     * The Defaults should look something like:
     *
     *  ```
     *  $defaults = [
     *       'field_slug' => 'default_slug',
     *       'fieldName' => 'this is a field name',
     *        // and so on...
     *       '_field' => {} // object
     *  ];
     *  ```
     *
     * @param OnFieldMetaBox $onFieldMetaBox
     * @param array $defaults
     *
     * @return void
     * @throws \Exception
     */
    public function processData (OnFieldMetaBox $onFieldMetaBox, array $defaults = []): void
    {
        $this->onFieldMetaBox = $onFieldMetaBox;
        $data = $onFieldMetaBox->getCallBackData();
        $this->field_slug = $data->field_slug ?? $defaults['field_slug'] ?? '';
        $cellName = $this->field_slug . '_cell';
        $this->fieldCell = $data->{$cellName} ?? $defaults[$cellName] ?? '';
        $this->field_slug_unique_hash = $data->field_slug_unique_hash ?? $defaults['field_slug_unique_hash'] ?? 'CHANGEID';
        $this->field_input_name = $data->field_input_name ?? $defaults['field_input_name'] ?? '';
        $this->fieldName = $data->fieldName ?? $defaults['fieldName'] ?? '';

        if ($onFieldMetaBox::OnBackEndSettingsType) {
            $this->inputName = $data->inputName ?? $defaults['inputName'] ?? "";
        } else {
            $this->inputName = $data->inputName ?? $defaults['inputName'] ?? "{$this->field_slug}_$this->field_slug_unique_hash";
        }

        $this->row = $data->row ?? $defaults['row'] ?? null;
        $this->column = $data->column ?? $defaults['column'] ?? null;
        $this->grid_template_col = $data->grid_template_col ?? $defaults['grid_template_col'] ?? null;
        $this->info = $data->info ?? $defaults['info'] ?? null;
        $this->useTab = $data->useTab ?? $defaults['useTab'] ?? null;
        $this->group = $data->group ?? $defaults['group'] ?? null;

        $defaultValue = $data->defaultValue ?? '';
        $keyValue = $onFieldMetaBox->getKeyValueInData($data, $this->inputName);
        $this->defaultValue = $keyValue ?: $defaultValue;

        $this->_field = $data->_field ?? $defaults['_field'] ?? null;
    }

    /**
     * @return string
     * @throws \Throwable
     */
    public function getTopHTMLWrapper (): string
    {
        return $this->onFieldMetaBox->_topHTMLWrapper($this->getFieldName(), $this->onFieldMetaBox->getCallBackData());
    }

    public function getFieldSlug (): string
    {
        return $this->field_slug;
    }

    public function getFieldSlugUniqueHash (): string
    {
        return $this->field_slug_unique_hash;
    }

    /**
     * Alias of `getFieldSlugUniqueHash()`
     * @return string
     */
    public function getFieldChangeID (): string
    {
        return $this->field_slug_unique_hash;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getFieldChangeIDOnSettingsForm (): string
    {
        return isset($this->onFieldMetaBox->getCallBackData()->_field) ? helper()->randString(10) : 'CHANGEID';
    }

    public function getFieldInputName (): string
    {
        return $this->field_input_name;
    }

    public function getFieldName (): string
    {
        return $this->fieldName;
    }

    public function getInputName (): string
    {
        return $this->inputName;
    }

    public function getField (): ?stdClass
    {
        return $this->_field;
    }

    public function getDefaultValue (): string
    {
        return $this->defaultValue;
    }

    public function getFieldCell (): string
    {
        return $this->fieldCell;
    }

    public function setFieldCell (string $fieldCell): void
    {
        $this->fieldCell = $fieldCell;
    }

    public function getRow (): ?string
    {
        return $this->row;
    }

    public function setRow (?string $row): void
    {
        $this->row = $row;
    }

    public function getColumn (): ?string
    {
        return $this->column;
    }

    public function setColumn (?string $column): void
    {
        $this->column = $column;
    }

    public function getGridTemplateCol (): ?string
    {
        return $this->grid_template_col;
    }

    public function setGridTemplateCol (?string $grid_template_col): void
    {
        $this->grid_template_col = $grid_template_col;
    }

    public function getInfo (): ?string
    {
        return $this->info;
    }

    public function setInfo (?string $info): void
    {
        $this->info = $info;
    }

    public function getUseTab (): ?string
    {
        return $this->useTab;
    }

    public function setUseTab (?string $useTab): void
    {
        $this->useTab = $useTab;
    }

    public function getGroup (): ?string
    {
        return $this->group;
    }

    public function setGroup (?string $group): void
    {
        $this->group = $group;
    }

    public function getFieldValidation (): array
    {
        return $this->field_validation;
    }

    public function setFieldValidation (array $field_validation): void
    {
        $this->field_validation = $field_validation;
    }

    public function getFieldSanitization (): array
    {
        return $this->field_sanitization;
    }

    public function setFieldSanitization (array $field_sanitization): void
    {
        $this->field_sanitization = $field_sanitization;
    }
}