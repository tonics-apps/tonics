<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Comment\Data;

use App\Modules\Core\Library\AbstractDataLayer;

class CommentData extends AbstractDataLayer
{
    const ADMIN_NAME = 'admin';
    const CUSTOMER_NAME = 'customer';
}