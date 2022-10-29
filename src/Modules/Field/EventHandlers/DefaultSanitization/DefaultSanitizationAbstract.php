<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\EventHandlers\DefaultSanitization;

use App\Modules\Field\Events\OnFieldMetaBox;

abstract class DefaultSanitizationAbstract
{
    private ?OnFieldMetaBox $event = null;
    private $data = null;

    /**
     * @return OnFieldMetaBox|null
     */
    public function getEvent(): ?OnFieldMetaBox
    {
        return $this->event;
    }

    /**
     * @param OnFieldMetaBox|null $event
     * @return DefaultSanitizationAbstract
     */
    public function setEvent(?OnFieldMetaBox $event): DefaultSanitizationAbstract
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param null $data
     */
    public function setData($data): DefaultSanitizationAbstract
    {
        $this->data = $data;
        return $this;
    }
}