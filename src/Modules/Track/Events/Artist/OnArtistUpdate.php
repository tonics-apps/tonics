<?php
/*
 * Copyright (c) 2022-2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\Events\Artist;

use App\Modules\Track\Events\AbstractClasses\ArtistDataAccessor;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnArtistUpdate extends ArtistDataAccessor implements EventInterface
{

}