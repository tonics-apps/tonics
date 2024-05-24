<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Apps\TonicsCloud\Interfaces;

interface CloudAutomationInterface
{
    /**
     * This should be the field slug name if you are using field
     * @return string
     */
    public function name ();

    /**
     * User-friendly display name
     * @return string
     */
    public function displayName (): string;

    /**
     * The `$data` param contains `input`, the `containerService`, etc, please inspect the data
     *
     * It should return the jobQueuePath
     *
     * @param array $data
     *
     * @return void
     */
    public function automate (array $data = []): void;
}