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

namespace App\Modules\Core\Controllers;

use App\Modules\Core\Events\OnAddMessageType;
use JetBrains\PhpStorm\NoReturn;

readonly class MessageController
{
    /**
     * @throws \Exception
     */
    public function __construct (private OnAddMessageType $onAddMessageType)
    {
        helper()->addEventStreamHeader(forceEventStream: true);
    }

    /**
     * @param string $msgType
     *
     * @return void
     * @throws \Throwable
     */
    #[NoReturn] public function sendMessages (string $msgType): void
    {
        event()->dispatch($this->onAddMessageType);
        if ($this->onAddMessageType->exist($msgType)) {
            $this->onAddMessageType->processMessages(fn($type, $message) => helper()->sendEventStreamMsg($type, $message), $msgType);
        }
        helper()->sendMsg('Close', null, event: 'close');
        exit(0);
    }
}