<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library;

interface SharedMemoryInterface
{

    /**
     * Typically, when using shared memory, the key is used to reference the same shared memory segment across different processes or instances of the program.
     * By using the same key value, multiple processes can access and interact with the same shared memory segment.
     * If you have a different set of processes that you don't want them to use this key, please add a different master key
     * @return string
     */
    public static function masterKey(): string;

    /**The semaphore ID allows processes or threads to refer to the same semaphore object across different parts of an application or
     * even different applications running on the same system.
     * 
     * The semaphore ID can be used to acquire, release, and perform other operations on the semaphore to synchronize access to shared resources or critical sections of code.
     * @return string
     */
    public static function semaphoreID(): int;

    /**
     * The shared memory size. If not provided, default to 100kb, if you would store something large please increase it,
     * e.g 1mb, 5mb, 1gb, 1tb, note, ensure you have enough memory on your system before allocating a large $size
     * @return string
     */
    public static function sharedMemorySize(): string;

}