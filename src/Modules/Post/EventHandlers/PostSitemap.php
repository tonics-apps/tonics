<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Post\EventHandlers;

use App\Modules\Core\Events\Tools\Sitemap\AbstractSitemapInterface;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class PostSitemap extends AbstractSitemapInterface implements HandlerInterface
{

}