<?php

namespace App\Modules\Post\Events;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnPostCategoryCreate implements EventInterface
{

    private \stdClass $category;

    public function __construct(\stdClass $category)
    {
        $this->category = $category;
        if (property_exists($category, 'created_at')){
            $this->category->created_at = $this->getCatCreatedAt();
        }
        if (property_exists($category, 'updated_at')){
            $this->category->updated_at = $this->getCatUpdatedAt();
        }
    }

    public function getAll(): \stdClass
    {
        return $this->category;
    }

    public function getAllToArray(): array
    {
        return (array)$this->category;
    }

    public function getCatID(): string|int
    {
        return (property_exists($this->category, 'cat_id')) ? $this->category->cat_id : '';
    }

    public function getCatStatus(): string|int
    {
        return (property_exists($this->category, 'cat_status')) ? $this->category->cat_status : '';
    }

    public function getCatParentID(): mixed
    {
        return (property_exists($this->category, 'cat_parent_id')) ? $this->category->cat_parent_id : '';
    }

    public function getCatName(): string
    {
        return (property_exists($this->category, 'cat_name')) ? $this->category->cat_name : '';
    }

    public function getCatSlug(): string
    {
        return (property_exists($this->category, 'cat_slug')) ? $this->category->cat_slug : '';
    }

    public function getCatURLSlug(): string
    {
        return (property_exists($this->category, 'cat_url_slug')) ? $this->category->cat_url_slug : '';
    }

    public function getCatContent(): string
    {
        return (property_exists($this->category, 'cat_content')) ? $this->category->cat_content : '';
    }

    public function getCatCreatedAt(): mixed
    {
        return (property_exists($this->category, 'created_at')) ? str_replace(' ', 'T', $this->category->created_at) : '';
    }

    public function getCatUpdatedAt(): mixed
    {
        return (property_exists($this->category, 'updated_at')) ? str_replace(' ', 'T', $this->category->updated_at) : '';
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }
}