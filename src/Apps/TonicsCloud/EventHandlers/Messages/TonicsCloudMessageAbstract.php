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

namespace App\Apps\TonicsCloud\EventHandlers\Messages;

use App\Modules\Core\Events\OnAddMessageType;
use App\Modules\Core\Library\MessageQueue;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

abstract class TonicsCloudMessageAbstract implements HandlerInterface
{
    /**
     * @param string $uniqueID
     *
     * @return int
     */
    public static function MessageTypeKey (string $uniqueID): int
    {
        return MessageQueue::GetType(static::class . $uniqueID);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnAddMessageType */
        $event->addMessageType(
            self::MessageTypeKey(session()::getUserID()),
            helper()->getObjectShortClassName($this),
            fn($message) => $this->sendEvent($event, $message),
            fn() => self::MessageTypeKey(session()::getUserID()),
        );
    }

    /**
     * @param $tdName
     * @param $uniqueKey
     * @param string $frag
     *
     * @return string[]
     */
    public function dataTableData ($tdName, $uniqueKey, string $frag): array
    {
        return [
            'td'   => "{$tdName}_{$uniqueKey}",
            'frag' => "$frag",
        ];
    }

}