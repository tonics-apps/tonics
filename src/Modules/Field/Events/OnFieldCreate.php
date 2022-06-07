<?php

namespace App\Modules\Field\Events;

use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;
use stdClass;

class OnFieldCreate implements EventInterface
{
    private stdClass $field;
    private FieldData $fieldData;

    /**
     * @param stdClass $widget
     * @param fieldData|null $fieldData
     */
    public function __construct(stdClass $widget, FieldData $fieldData = null)
    {
        $this->field = $widget;
        if (property_exists($widget, 'created_at')){
            $this->field->created_at = $this->getCatCreatedAt();
        }
        if (property_exists($widget, 'updated_at')){
            $this->field->updated_at = $this->getCatUpdatedAt();
        }

        if ($fieldData){
            $this->fieldData = $fieldData;
        }
    }

    public function getAll(): stdClass
    {
        return $this->field;
    }

    public function getAllToArray(): array
    {
        return (array)$this->field;
    }

    public function getFieldID(): string|int
    {
        return (property_exists($this->field, 'field_id')) ? $this->field->field_id : '';
    }

    public function getFieldTitle(): string
    {
        return (property_exists($this->field, 'field_name')) ? $this->field->field_name : '';
    }

    public function getFieldSlug(): string
    {
        return (property_exists($this->field, 'field_slug')) ? $this->field->field_slug : '';
    }

    public function getCatCreatedAt(): string
    {
        return (property_exists($this->field, 'created_at')) ? str_replace(' ', 'T', $this->field->created_at) : '';
    }

    public function getCatUpdatedAt(): string
    {
        return (property_exists($this->field, 'updated_at')) ? str_replace(' ', 'T', $this->field->updated_at) : '';
    }

    public function event(): static
    {
        return $this;
    }

    /**
     * @return fieldData
     */
    public function getFieldData(): fieldData
    {
        return $this->fieldData;
    }

    /**
     * @param fieldData $fieldData
     */
    public function setFieldData(fieldData $fieldData): void
    {
        $this->fieldData = $fieldData;
    }
}