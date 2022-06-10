<?php

namespace App\Modules\Post\Helper;

use App\Modules\Core\Library\SimpleState;
use App\Modules\Post\Data\PostData;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;

class PostRedirection extends SimpleState
{
    private OnRequestProcess $request;
    private string $intendedPostURL = '';
    private string $postID = '';
    private PostData $postData;

    /**
     * @throws \Exception
     */
    public function __construct(PostData $postData){
        $this->postData = $postData;
        $this->request = request();
    }

    # States For PostRedirection
    const OnPostInitialState = 'OnPostInitialState';
    const OnPostNumericIDState = 'OnPostNumericIDState';
    const OnPostStringIDState = 'OnPostStringIDState';

    public function OnPostInitialState(): string
    {
        $postID = $this->getRequest()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams()[0];
        $this->setPostID($postID);
        if (is_numeric($postID)){
            $this->switchState(self::OnPostNumericIDState);
            return self::NEXT;
        }

        $this->switchState(self::OnPostStringIDState);
        return self::NEXT;
    }

    /**
     * @throws \Exception
     */
    public function OnPostNumericIDState(): string
    {
        try {
            $post = $this->getPostData()
                ->selectWithConditionFromPost(['*'], "slug_id = ?", [$this->getPostID()]);
            if (isset($post->slug_id) && isset($post->post_slug)){
                $this->intendedPostURL = "/posts/$post->slug_id/$post->post_slug";
                return self::DONE;
            }
        } catch (\Exception){
            return self::ERROR;
        }

        return self::ERROR;
    }

    public function OnPostStringIDState(): string
    {
        try {
            $post = $this->getPostData()
                ->selectWithConditionFromPost(['*'], "post_slug = ?", [$this->getPostID()]);
            if (isset($post->slug_id) && isset($post->post_slug)){
                $this->intendedPostURL = "/posts/$post->slug_id/$post->post_slug";
                return self::DONE;
            }
        } catch (\Exception){
        }

        return self::ERROR;
    }

    /**
     * @return OnRequestProcess|null
     */
    public function getRequest(): ?OnRequestProcess
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getIntendedPostURL(): string
    {
        return $this->intendedPostURL;
    }

    /**
     * @return PostData
     */
    public function getPostData(): PostData
    {
        return $this->postData;
    }

    /**
     * @return string
     */
    public function getPostID(): string
    {
        return $this->postID;
    }

    /**
     * @param string $postID
     */
    public function setPostID(string $postID): void
    {
        $this->postID = $postID;
    }

    /**
     * @param OnRequestProcess|null $request
     * @return PostRedirection
     */
    public function setRequest(?OnRequestProcess $request): PostRedirection
    {
        $this->request = $request;
        return $this;
    }
}