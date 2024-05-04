<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
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