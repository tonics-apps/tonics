<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library\JobSystem;

interface JobTransporterInterface
{
    /**
     * Transporters Name
     * @return string
     */
    public function name(): string;

    /**
     * Enqueue an event that is readily dispatch when called
     * @param AbstractJobInterface $jobEvent
     * @param callable|null $beforeEnqueue
     * Callback before job is enqueued, this might also return possible `toInsert`
     * @param callable|null $afterEnqueue
     * Callback after job is enqueued, this might return what is enqueued
     */
    public function enqueue(AbstractJobInterface $jobEvent, callable $beforeEnqueue = null, callable $afterEnqueue = null): void;

    /**
     * If true, the transporter would dispatch the event immediately
     * @return bool
     */
    public function isStatic(): bool;

    /**
     * @return void
     */
    public function runJob(): void;

}