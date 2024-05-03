<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\Interfaces;

/**
 * Please extend DefaultSanitizationAbstract if you want to get access to the fields, or field data, otherwise, you good to go ;)
 */
interface FieldValueSanitizationInterface
{
    public function sanitizeName(): string;

    public function sanitize($value): mixed;
}