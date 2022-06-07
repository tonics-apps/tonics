<?php

namespace App\Modules\Page\Events;

use App\Modules\Page\Data\PageData;
use App\Modules\Post\Data\PostData;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnPageCreated implements EventInterface
{

    private \stdClass $page;
    private PageData $postData;

    public function __construct(\stdClass $page, PageData $pageData = null)
    {
        $this->page = $page;
        if (property_exists($page, 'created_at')){
            $this->page->created_at = $this->getCatCreatedAt();
        }
        if (property_exists($page, 'updated_at')){
            $this->page->updated_at = $this->getCatUpdatedAt();
        }

        if ($pageData){
            $this->postData = $pageData;
        }
    }

    public function getAll(): \stdClass
    {
        return $this->page;
    }

    public function getAllToArray(): array
    {
        return (array)$this->page;
    }

    public function getPageID(): string|int
    {
        return (property_exists($this->page, 'page_id')) ? $this->page->page_id : '';
    }

    public function getFieldIDs(): string|array
    {
        return (property_exists($this->page, 'field_ids')) ? json_decode($this->page->field_ids) : '';
    }

    public function getPageTitle(): string
    {
        return (property_exists($this->page, 'page_title')) ? $this->page->page_title : '';
    }

    public function getPageSlug(): string
    {
        return (property_exists($this->page, 'page_slug')) ? $this->page->page_slug : '';
    }


    public function getPageStatus(): string|int
    {
        return (property_exists($this->page, 'page_status')) ? $this->page->page_status : '';
    }

    public function getCatCreatedAt(): string
    {
        return (property_exists($this->page, 'created_at')) ? str_replace(' ', 'T', $this->page->created_at) : '';
    }

    public function getCatUpdatedAt(): string
    {
        return (property_exists($this->page, 'updated_at')) ? str_replace(' ', 'T', $this->page->updated_at) : '';
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    /**
     * @return PageData
     */
    public function getPostData(): PageData
    {
        return $this->postData;
    }

    /**
     * @param PageData $postData
     */
    public function setPostData(PageData $postData): void
    {
        $this->postData = $postData;
    }


}