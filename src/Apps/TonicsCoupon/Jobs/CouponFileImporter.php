<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCoupon\Jobs;

use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use JsonMachine\Items;

class CouponFileImporter extends AbstractJobInterface implements JobHandlerInterface
{

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $couponJsonFilePath = null;
        if (isset($this->getData()->fullFilePath) && helper()->fileExists($this->getData()->fullFilePath)){
            $couponJsonFilePath = $this->getData()->fullFilePath;
            $this->handleFileImporting($couponJsonFilePath);
        }
        dd($couponJsonFilePath, $this->getData());
    }

    protected function handleFileImporting(string $filePath)
    {
        $items = Items::fromFile($filePath);
        foreach ($items as $item) {
            dd($item);
        }
    }
}