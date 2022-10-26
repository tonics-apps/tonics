<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsAmazonAffiliate\EventHandler;

use App\Apps\TonicsToc\Controller\TonicsTocController;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\FieldTemplateFileInterface;

class TonicsAmazonAffiliateProductBoxFieldHandler implements FieldTemplateFileInterface
{

    /**
     * @throws \Exception
     */
    public function handleFieldLogic(OnFieldMetaBox $event = null, $data = null): string
    {
        dd($data, getPostData());
    }

    public function name(): string
    {
        return 'Tonics Amazon Affiliate Product Box';
    }

    public function canPreSaveFieldLogic(): bool
    {
        return true;
    }

}