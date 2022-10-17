<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\NinetySeven\Controller;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Field\Data\FieldData;
use App\Modules\Post\Data\PostData;
use App\Modules\Post\RequestInterceptor\PostAccessView;
use JetBrains\PhpStorm\NoReturn;

class PostsController
{
    private PostData $postData;
    private PostAccessView $postAccessView;
    private FieldData $fieldData;

    /**
     * @param PostData $postData
     * @param PostAccessView $postAccessView
     * @param FieldData $fieldData
     * @throws \Exception
     */
    public function __construct(PostData $postData, PostAccessView $postAccessView, FieldData $fieldData)
    {
        $this->postData = $postData;
        $this->postAccessView = $postAccessView;
        $this->fieldData = $fieldData;
        addToGlobalVariable('Assets', ['css' => AppConfig::getAppAsset('NinetySeven', 'css/styles.min.css')]);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function singlePost(): void
    {
        $this->getPostAccessView()->handlePost();
        $this->getPostAccessView()->showPost('Apps::NinetySeven/Views/Post/single', NinetySevenController::getSettingData());
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function singleCategory(): void
    {
        $this->getPostAccessView()->handleCategory();
        $this->getPostAccessView()->showCategory('Apps::NinetySeven/Views/Post/Category/single', NinetySevenController::getSettingData());
    }

    /**
     * @return PostData
     */
    public function getPostData(): PostData
    {
        return $this->postData;
    }

    /**
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->fieldData;
    }

    /**
     * @return PostAccessView
     */
    public function getPostAccessView(): PostAccessView
    {
        return $this->postAccessView;
    }

    /**
     * @param PostAccessView $postAccessView
     */
    public function setPostAccessView(PostAccessView $postAccessView): void
    {
        $this->postAccessView = $postAccessView;
    }

}