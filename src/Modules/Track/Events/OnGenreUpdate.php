<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\Events;

use App\Modules\Track\Events\AbstractClasses\GenreDataAccessor;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnGenreUpdate extends GenreDataAccessor implements EventInterface
{

}