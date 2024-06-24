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

namespace App\Modules\Core\Library;

use RuntimeException;

class MessageQueue
{
    private int                     $queueKey;
    private \SysvMessageQueue|false $queueId;
    private array                   $options;

    /**
     * MessageQueue constructor.
     *
     *
     *  Usage Example:
     *
     *  ```
     *  try {
     *  // Generate a unique key for the message queue
     *  $queueKey = MessageQueue::GenerateKey(__FILE__, 'A');
     *
     *  // Create a new message queue instance
     *  $queue = new MessageQueue($queueKey);
     *
     *  // Send a few messages
     *  $queue->sendMessage('Hello, World 1!');
     *  $queue->sendMessage('Hello, World 2!');
     *  $queue->sendMessage('Hello, World 3!');
     *
     *  // Receive and process messages with a callback
     *  $queue->receiveMessage(function ($msgType, $message) {
     *       echo "Received message of type $msgType: $message\n";
     *  });
     *
     *  // Get queue status
     *  $status = $queue->getQueueStatus();
     *  echo "Queue Status:\n";
     *  print_r($status);
     *
     *  // Remove the queue (optional)
     *  $queue->removeQueue();
     *  } catch (RuntimeException $e) {
     *  echo 'Error: ' . $e->getMessage();
     *  }
     *  ```
     *
     * @param int $queueKey
     * @param array $options
     */
    public function __construct (int $queueKey, array $options = [])
    {
        $this->queueKey = $queueKey;
        $this->queueId = msg_get_queue($this->queueKey);

        if ($this->queueId === false) {
            throw new RuntimeException('Failed to create or get message queue');
        }

        $this->options = array_merge([
            'blocking' => false,
            'maxSize'  => self::DefaultSize(),
        ], $options);
    }

    /**
     * Send a message to the queue. The message would be json_encoded, so, don't do that yourself
     *
     * @param mixed $message
     * @param int $msgType
     *
     * @return bool
     */
    public function send (mixed $message, int $msgType = 1): bool
    {
        $message = json_encode($message);
        if (!msg_send($this->queueId, $msgType, $message, false, $this->options['blocking'], $errorCode)) {
            throw new RuntimeException('Failed to send message. Error code: ' . $errorCode);
        }

        return true;
    }

    /**
     * Receive messages from the queue and process them using a callback function.
     *
     * Example Usage:
     *
     * ```
     * $queue = new MessageQueue(JobManager::semaphoreID());
     * $queue->receive(MessageQueue::GetType(CoreActivator::class), function ($type, $message) {
     *      // handle message
     * });
     * ```
     *
     * @param int $msgType
     * @param callable|null $callback
     *
     * @return void
     */
    public function receive (int $msgType = 0, callable $callback = null): void
    {
        while (msg_receive($this->queueId, $msgType, $receivedMsgType, $this->options['maxSize'], $message, false, MSG_IPC_NOWAIT, $errorCode)) {
            if ($callback !== null) {
                $message = json_decode($message);
                $callback($receivedMsgType, $message);
            }
        }
    }

    /**
     * Remove the message queue.
     *
     * @return bool
     */
    public function removeQueue (): bool
    {
        if (!msg_remove_queue($this->queueId)) {
            throw new RuntimeException('Failed to remove message queue');
        }

        return true;
    }

    /**
     * Get the status of the message queue.
     *
     * @return array
     */
    public function getQueueStatus (): array
    {
        $status = msg_stat_queue($this->queueId);

        if ($status === false) {
            throw new RuntimeException('Failed to get message queue status');
        }

        return $status;
    }

    /**
     * Set the permissions of the message queue.
     *
     * @param int $permissions
     *
     * @return bool
     */
    public function setQueuePermissions (int $permissions): bool
    {
        if (!msg_set_queue($this->queueId, ['msg_perm.uid' => $permissions])) {
            throw new RuntimeException('Failed to set message queue permissions');
        }

        return true;
    }

    /**
     * This would generate a message type for you, ensure that the string is unique to whatever you are doing,
     * for example, in my case, I am using `TonicsCloudActivator::class` to identify all messages from TonicsCloud,
     * and `CoreActivator::class` to identify messages from Core, you get the idea.
     *
     * @param string $string
     *
     * @return int
     */
    public static function GetType (string $string): int
    {
        return intval(hash("crc32b", $string), 16);
    }

    /**
     * Generate a unique key for the message queue.
     *
     * @param string $path
     * @param string $projID
     *
     * @return int
     */
    public static function GenerateKey (string $path = __FILE__, string $projID = 't'): int
    {
        $key = ftok($path, $projID);

        if ($key === -1) {
            throw new RuntimeException('Failed to generate System V IPC key');
        }

        return $key;
    }

    /**
     * Default maximum message size.
     *
     * @return int
     */
    public static function DefaultSize (): int
    {
        return 4096;
    }
}