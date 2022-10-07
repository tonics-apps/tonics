<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Post\Events;

use App\Modules\Post\Data\PostData;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnPostCreate implements EventInterface
{

    private \stdClass $post;
    private PostData $postData;

    public function __construct(\stdClass $post, PostData $postData = null)
    {
        $this->post = $post;
        if (property_exists($post, 'created_at')){
            $this->post->created_at = $this->getCatCreatedAt();
        }
        if (property_exists($post, 'updated_at')){
            $this->post->updated_at = $this->getCatUpdatedAt();
        }

        if ($postData){
            $this->postData = $postData;
        }
    }

    public function getAll(): \stdClass
    {
        return $this->post;
    }

    public function getAllToArray(): array
    {
        return (array)$this->post;
    }

    public function getPostID(): string|int
    {
        return (property_exists($this->post, 'post_id')) ? $this->post->post_id : '';
    }

    public function getFieldIDs(): string|array
    {
        return (property_exists($this->post, 'field_ids')) ? json_decode($this->post->field_ids) : '';
    }

    public function getSlugID(): mixed
    {
        return (property_exists($this->post, 'slug_id')) ? $this->post->slug_id : '';
    }

    public function getPostUserID(): string|int
    {
        return (property_exists($this->post, 'user_id')) ? $this->post->user_id : '';
    }

    public function getPostTitle(): string
    {
        return (property_exists($this->post, 'post_title')) ? $this->post->post_title : '';
    }

    public function getPostSlug(): string
    {
        return (property_exists($this->post, 'post_slug')) ? $this->post->post_slug : '';
    }

    public function getImageURL(): string
    {
        return (property_exists($this->post, 'image_url')) ? $this->post->image_url : '';
    }

    public function getPostStatus(): string|int
    {
        return (property_exists($this->post, 'post_status')) ? $this->post->post_status : '';
    }

    public function getPostCatIDS(): array
    {
        $catIDS = (property_exists($this->post, 'fk_cat_id')) ? $this->post->fk_cat_id : [];
        if (!is_array($catIDS) && !empty($catIDS)){
            $catIDS = [$catIDS];
        }
        return $catIDS;
    }

    public function getCatCreatedAt(): string
    {
        return (property_exists($this->post, 'created_at')) ? str_replace(' ', 'T', $this->post->created_at) : '';
    }

    public function getCatUpdatedAt(): string
    {
        return (property_exists($this->post, 'updated_at')) ? str_replace(' ', 'T', $this->post->updated_at) : '';
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    /**
     * @return PostData
     */
    public function getPostData(): PostData
    {
        return $this->postData;
    }

    /**
     * @param PostData $postData
     * @return OnPostCreate
     */
    public function setPostData(PostData $postData): OnPostCreate
    {
        $this->postData = $postData;
        return $this;
    }

    /**
     * @return \stdClass
     */
    public function getPost(): \stdClass
    {
        return $this->post;
    }

    /**
     * @param \stdClass $post
     */
    public function setPost(\stdClass $post): void
    {
        $this->post = $post;
    }
}