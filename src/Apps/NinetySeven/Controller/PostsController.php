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