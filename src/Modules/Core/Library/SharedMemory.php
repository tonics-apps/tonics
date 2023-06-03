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

use SysvSemaphore;
use SysvSharedMemory;

class SharedMemory
{
    private false|SysvSharedMemory $shm;
    private false|SysvSemaphore $sem;
    private string $masterKey = '';
    private string $size = '100kb';
    private ?string $semaphoreID = null;
    private bool|null $naked = false;

    public bool $booleanHelper;

    /**
     * If you are in a child process, please create a new instance of the `ShareMemory()` class, this way, you won't get variable or
     * memory corruption, don't forget to pass the same masterKey if you want to get access to the same shared variable.
     *
     * Remember to use the cleanSharedMemory() method when you are certain that all processes no longer require semaphore or the shared memory
     * @param string $masterKey
     * Typically, when using shared memory,
     * the key is used to reference the same shared memory segment across different processes or instances of the program.
     * By using the same key value, multiple processes can access and interact with the same shared memory segment.
     *
     * If you have a different set of processes that you don't want them to use this key, please add a different master key
     * @param string|null $semaphoreID
     * The semaphoreID
     * @param string $size
     * The shared memory size. If not provided, default to 100kb, if you would store something large please increase it,
     * e.g 1mb, 5mb, 1gb, 1tb, note, ensure you have enough memory on your system before allocating a large $size
     * @param bool $naked
     * No class property is set if true
     * @throws \Exception
     */
    public function __construct(string $masterKey, string $semaphoreID = null, string $size = '100kb', bool $naked = false)
    {
        if ($naked === false){
            $this->size = $size;
            $this->masterKey = $masterKey;
            if ($semaphoreID === null){
                $this->semaphoreID = ftok(__FILE__, 't');
            } else {
                $this->semaphoreID = $semaphoreID;
            }
            $this->attachSharedMemory();
        }

        $this->naked = $naked;
    }

    /**
     * Remove and reattach can help you clean corrupted shared memory, please use this on the first ever instance of the class,
     * do not try to use in a forked process as you would lose the shared memory and cause havoc across all other processes trying to access
     * something that does not exist
     * @throws \Exception
     */
    public function removeAndReAttach(): void
    {
        $this->removeSharedMemory();
        $this->removeSemaphore();
        $this->attachSharedMemory();
    }

    /**
     * @throws \Exception
     */
    protected function attachSharedMemory(): void
    {
        // get a handle to the semaphore associated with the shared memory
        // segment we want
        $this->sem = sem_get($this->semaphoreID,1,0600);
        if (sem_acquire($this->sem) === false){
            throw new \Exception("Can't acquire semaphore");
        }

        $this->shm = shm_attach(crc32($this->masterKey), self::getBytes($this->size));
    }

    /**
     * You should never do this, but if you want to do things in a manual way, go ahead,
     * keep in mind that detaching only Disconnects from shared memory segment, meaning other process
     * can get access to it if only if they reattach, otherwise, it throws a warning
     * @return bool
     */
    public function detachSharedMemory(): bool
    {
        return shm_detach($this->shm);
    }


    /**
     * Release the semaphore so other processes can acquire it
     * @return bool
     */
    public function detachSemaphore(): bool
    {
        return sem_release($this->sem);
    }

    /**
     * This ensures atomic operation, meaning, as long as the process has gotten access to the semaphore, other process would wait
     * until it completes it operation.
     *
     * Note that, multiple process can get access to a semaphore, but I have restricted it to maximum of 1.
     *
     * To make things easier for you, this function returns whatever you return in the callable
     * @param callable $callable |null $callable
     * @return mixed
     * @throws \Exception
     */
    public function ensureAtomicity(callable $callable): mixed
    {
        $this->attachSharedMemory(); # Try to get access to the shared memory and acquire semaphore or wait till you can acquire
        return $callable($this);
    }


    /**
     * Note: Create a new instance of this class so the shared memory can be removed cleanly.
     *
     * Should be done at the end of all processes, it calls both the removeSemaphore and removeSharedMemory method.
     * @return bool
     */
    public function cleanSharedMemory(): bool
    {
        return $this->removeSemaphore() && $this->removeSharedMemory();
    }

    /**
     * Note: Create a new instance of this class so the shared memory can be removed cleanly
     *
     * Use this at the end of all processes, if you use this at the end of a single process, and
     * another process tries to get access to the sharedMemory, it would have been gone, so, only remove it
     * once you are sure other processes do not need the sharedMemory anymore.
     * @return bool
     */
    public function removeSharedMemory(): bool
    {
        return shm_remove($this->shm);
    }

    /**
     * Same as the `removeSharedMemory()` method, except it is for semaphore, the same caution applies, you should
     * only use this at the end of all processes, if you use this at the end of a single process, and
     * another process tries to get access to the semaphore, it would throw an error since there won't be any semaphore to acquire,
     * hope you get that, however, if you know what you are doing, or you are doing things manually, please, be my guest and semaphore away ;)
     * @return bool
     */
    public function removeSemaphore(): bool
    {
        return sem_remove($this->sem);
    }

    /**
     * Inserts or updates a variable in shared memory atomically.
     * If you don't want atomic operation, you can use the `add()` method
     * @param string|int $key
     * The variable key.
     * @param $var
     * The variable. All variable types that serialize supports may be used: generally this means all types except for resources and some internal objects that cannot be serialized.
     * @return bool|null
     * @throws \Exception
     */
    public function atomAdd(string|int $key, $var): ?bool
    {
        $result = null;
        $this->ensureAtomicity(function (SharedMemory $sharedMemory) use ($var, $key, &$result){
            $result = $sharedMemory->add($key, $var);
        });
        return $result;
    }

    /**
     * Inserts or updates a variable in shared memory.
     * If you want to insert or update atomically, please make use of the `atomAdd() `function
     * @param string|int $key
     * The variable key.
     * @param $var
     * The variable. All variable types that serialize supports may be used: generally this means all types except for resources and some internal objects that cannot be serialized.
     * @return bool
     * @throws \Exception
     */
    public function add(string|int $key, $var): bool
    {
        if (is_bool($var)){
            $sharedMemoryInstance = new SharedMemory($this->masterKey, naked: true);
            $sharedMemoryInstance->setBooleanHelper($var);
            return shm_put_var($this->shm, $this->shmKey($key), $sharedMemoryInstance);
        }
        return shm_put_var($this->shm, $this->shmKey($key), $var);
    }

    /**
     * Returns a variable from shared memory in an atomic way.
     * If you don't want atomic operation, you can use the `get()` method.
     *
     * Note: even with semaphore, PHP would throw the variable has been corrupted in the shared memory warning,
     * I have added a condition that keeps trying to get the variable, it times out after $timeoutSeconds, default is 30s
     * @param string|int $key
     * The variable key.
     * @param int $timeoutSeconds
     * Timeout seconds
     * @return false|mixed
     * @throws \Exception
     */
    public function atomGet(string|int $key, int $timeoutSeconds = 30): mixed
    {
        $result = null;
        $this->ensureAtomicity(function (SharedMemory $sharedMemory) use ($timeoutSeconds, $key, &$result){
            $startTime = time();
            while (true) {
                try {
                    // Perform some operations that may throw an exception
                    $result = $sharedMemory->get($key);
                    // If the operations succeed without throwing an exception, bail
                    break;
                } catch (\Exception $e) {
                    // Handle the exception

                    // Check if the timeout has been reached
                    if (time() - $startTime >= $timeoutSeconds) {
                        // Timeout reached, exit the loop
                        break;
                    }

                    // Sleep for a short duration before the next iteration
                    usleep(200000); // 0.2 seconds
                }
            }
        });
        return $result;
    }

    /**
     * Returns a variable from shared memory.
     * If you want to get the variable atomically, you can use the `atomGet()` method
     * @param string|int $key
     * The variable key.
     * @return false|mixed
     * @throws \Exception
     */
    public function get(string|int $key): mixed
    {
        if ($this->has($key)) {
            /**
             * It is kinda hard to handle a warning because, the warning itself returns false,
             * the way we handle this is whenever we are putting a boolean value, we create a naked SharedMemory class and
             * store the boolean value in the property helper, then whenever we wanna return an actual boolean, we check if the
             * instance is of the type SharedMemory, remember that is where we are storing the boolean property. And if we get FALSE,
             * we can be sure that is a warning, we can either throw an actual exception or handle it as we see fit
             */
            $var = @shm_get_var($this->shm, $this->shmKey($key));
            if ($var instanceof SharedMemory){
                return $var->getBooleanHelper();
            }
            // This is a warning, what should we do hia
            if ($var === false){
                throw new \Exception("An Error Occur Getting The Variable Value, Corrupted Memory?");
            }

            return $var;
        } else {
            throw new \Exception("The Variable Key $key Does Not Exist");
        }
    }

    /**
     * Removes a variable from shared memory in an atomic way
     * If you don't want atomic operation, you can use the `remove()` method
     * @param string|int $key
     * The variable key.
     * @return false|mixed
     * @throws \Exception
     */
    public function atomRemove(string|int $key): mixed
    {
        $result = null;
        $this->ensureAtomicity(function (SharedMemory $sharedMemory) use ($key, &$result){
            $result = $sharedMemory->remove($key);
        });
        return $result;
    }

    /**
     * Removes a variable from shared memory
     * If you want to remove the variable atomically, you can use the `atomRemove()` method
     * @param string|int $key
     * The variable key.
     * @return bool
     */
    public function remove(string|int $key): bool
    {
        if ($this->has($key)) {
            return shm_remove_var($this->shm, $this->shmKey($key));
        } else {
            return false;
        }
    }

    public function has(string|int $key,): bool
    {
        return shm_has_var($this->shm, $this->shmKey($key));
    }

    private function shmKey($value): int
    {
        $value = (string)$value;
        return crc32($value);
    }

    /**
     * Where $size can be 5kb, 2mb, 1gb, etc
     * @param string $size
     * @return int
     * @author DevsrealmGuy
     */
    public static function getBytes(string $size): int
    {
        $size = trim($size);
        #
        # Separate the value from the metric(i.e MB, GB, KB)
        #
        preg_match('/([0-9]+)[\s]*([a-zA-Z]+)/', $size, $matches);

        $value = (isset($matches[1])) ? $matches[1] : 0;
        $metric = (isset($matches[2])) ? strtolower($matches[2]) : 'b';

        #
        # Result of $value multiplied by the matched case
        # Note: (1024 ** 2) is same as (1024 * 1024) or pow(1024, 2)
        #
        $value *= match ($metric) {
            'k', 'kb' => 1024,
            'm', 'mb' => (1024 ** 2),
            'g', 'gb' => (1024 ** 3),
            't', 'tb' => (1024 ** 4),
            default => 0
        };

        return (int)$value;
    }

    /**
     * @return bool
     */
    public function getBooleanHelper(): bool
    {
        return $this->booleanHelper ;
    }

    /**
     * @param bool $booleanHelper
     * @return SharedMemory
     */
    public function setBooleanHelper(bool $booleanHelper): SharedMemory
    {
        $this->booleanHelper = $booleanHelper;
        return $this;
    }
}