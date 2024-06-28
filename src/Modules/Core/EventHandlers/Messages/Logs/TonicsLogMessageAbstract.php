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

namespace App\Modules\Core\EventHandlers\Messages\Logs;

use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Controllers\LogController;
use App\Modules\Core\EventHandlers\Messages\MessageAbstract;
use App\Modules\Core\Events\OnAddMessageType;

abstract class TonicsLogMessageAbstract extends MessageAbstract
{
    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnAddMessageType */
        $event->addMessageType(
            static::MessageTypeKey(),
            helper()->getObjectShortClassName($this),
            fn($message) => $this->sendEvent($event, $message),
            fn() => $this->beforeProcessing(),
        );
    }

    /**
     * @throws \Exception
     */
    protected function beforeProcessing (): void
    {
        $settings = LogController::getSettingsData();

        if (helper()->fileExists(static::FilePath())) {

            clearstatcache();
            $currentSize = filesize(static::FilePath());
            $lastSize = $settings[static::FileSizeKey()] ?? 0;

            if ($currentSize > $lastSize && isset($settings[static::LastPositionKey()])) {
                message()->send($settings[static::LastPositionKey()], static::MessageTypeKey());
            }

        }
    }

    /**
     * @param OnAddMessageType $event
     * @param $lastPosition
     *
     * @return array
     * @throws \Exception
     */
    private function sendEvent (OnAddMessageType $event, $lastPosition): array
    {
        $settings = LogController::getSettingsData();
        $msg = helper()->getLinesFromPosition(static::FilePath(), $lastPosition, $settings);
        FieldConfig::savePluginFieldSettings(LogController::getCacheKey(), $settings);
        return [
            'type' => $event::EVENT_TYPE_LOGGER,
            'data' => LogController::ansiEscapesToHtml($msg),
        ];
    }

    /**
     * @return string
     */
    public static function LastPositionKey (): string
    {
        return 'file_last_position' . static::FilePath();
    }

    /**
     * @return string
     */
    public static function FileSizeKey (): string
    {
        return 'file_size' . static::FilePath();
    }


    /**
     * @return string
     */
    public static function FilePath (): string
    {
        return '/var/log/tonics.log';
    }
}