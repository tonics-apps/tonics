<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Core\Events;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnAddMessageType implements EventInterface
{
    const EVENT_TYPE_UPDATE = 'UPDATE';
    const EVENT_TYPE_DELETE = 'DELETE';

    private array $messageTypes = [];

    public function event (): static
    {
        return $this;
    }

    /**
     * @param int $msgTypeKey
     * @param string $msgTypeName
     * @param callable $callBack
     * @param callable $typeKeyCallBack
     *
     * @return $this
     */
    public function addMessageType (int $msgTypeKey, string $msgTypeName, callable $callBack, callable $typeKeyCallBack): static
    {
        $this->messageTypes[$msgTypeKey] = [
            'fn'     => $callBack,
            'fn_key' => $typeKeyCallBack,
            'type'   => $msgTypeName,
        ];
        return $this;
    }

    /**
     * @return array
     */
    public function getMessageTypes (): array
    {
        return $this->messageTypes;
    }

    public function exist (string $name): bool
    {
        $name = strtolower($name);
        return isset($this->messageTypes[$name]);
    }

    /**
     * @param callable $callBack
     * @param int $msgType
     *
     * @return void
     */
    public function processMessages (callable $callBack, int $msgType = 0): void
    {
        message()->receive($msgType, function ($key, $message) use ($callBack) {

            if ($this->exist($key)) {
                $processedMessage = $this->messageTypes[$key]['fn']($message);
                if ($processedMessage) {
                    $type = $this->messageTypes[$key]['type'];
                    $callBack($type, $processedMessage);
                }
            }

        });
    }

    /**
     * @param string $type
     * @param mixed $data
     *
     * @return array
     */
    public function sendEvent (string $type, mixed $data): array
    {
        return [
            'type' => $type,
            'data' => $data,
        ];
    }
}