<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Library\Incus\Repositories;

use App\Apps\TonicsCloud\Library\Incus\Interface\AbstractRepository;

/**
 * The Instance Class
 *
 * Note: Some operations in incus once called, e.g the create function are performed in the background, so, whenever, you call it, it is gonna return the
 * background id, which you can query later or poll or event wait for it, until it's done, if you want to wait for an operation till it is completed, you can use the
 * following example:
 *
 * ```
 * $client = new Client(new URL("https://xx.xxx.xx.xxx:xxxx"), $certAndKey);
 * $create = $client->instances()->create([...]);
 *   if($create){
 *      // wait until the background operation is completed and return the operationResult
 *      $operationResult = $client->operations()->wait($result->operation);
 * }
 * ```
 */
class Instance extends AbstractRepository
{

    /**
     * Get instances
     * @return \stdClass|null
     * @throws \Exception
     */
    public function all(): ?\stdClass
    {
        return $this->client->sendRequest($this->getEndPoint() . '?recursion=1', $this->client->getURL()::REQUEST_GET);
    }

    /**
     * Create a new instance, here is an example (Note: Name can only contain alphanumeric and hyphen characters, e.g web-server or webServer and not web_server):
     *
     * ```
     * $client->instances()->create([
     *       "name" => "web" ,
     *       "description" => "My test instance",
     *       "source" => [
     *           "protocol" => "simplestreams",
     *           "alias" => "debian/bullseye/amd64",
     *           "server" => "https://images.linuxcontainers.org",
     *           "type" => "image"
     *       ],
     *   ]);
     * ```
     *
     * If you want to listen to port 80 and 443 on the host and proxy it to the container you are creating, here is an example of how to do that:
     *
     * ````
     * $client->instances()->create([
     *       "name" => "web" ,
     *       "description" => "My test instance",
     *       "source" => [
     *           "protocol" => "simplestreams",
     *           "alias" => "debian/bullseye/amd64",
     *           "server" => "https://images.linuxcontainers.org",
     *           "type" => "image"
     *       ],
     *      "devices" => [ // ensure all the value are string, else, you would get an error
     *          "proxyPort80" => [
     *              "type" => "proxy",
     *              "listen" => "tcp:0.0.0.0:80", // listen to port on the host
     *              "connect" => "tcp:127.0.0.1:80", // proxy it to the container or instance
     *              "proxy_protocol" => "true",
     *          ],
     *          "proxyPort443" => [
     *              "type" => "proxy",
     *              "listen" => "tcp:0.0.0.0:443", // listen to port on the host
     *              "connect" => "tcp:127.0.0.1:443", // proxy it to the container or instance
     *              "proxy_protocol" => "true",
     *          ],
     *      ]
     *   ]);
     * ```
     *
     * @throws \Exception
     */
    public function create(array $parameters)
    {
        return $this->client->sendRequest($this->getEndPoint(), $this->client->getURL()::REQUEST_POST, $parameters);
    }

    /**
     * Gets a specific instance
     * @param string $name Name of container
     * @return \stdClass|null
     * @throws \Exception
     */
    public function info(string $name): ?\stdClass
    {
        return $this->client->sendRequest($this->getEndPoint(). "/$name", $this->client->getURL()::REQUEST_GET);
    }

    /**
     * Returns a list of instance backups (URLs).
     * @param string $name Name of container
     * @return \stdClass|null
     * @throws \Exception
     */
    public function backups(string $name): ?\stdClass
    {
        return $this->client->sendRequest($this->getEndPoint(). "/$name/backups?recursion=1", $this->client->getURL()::REQUEST_GET);
    }

    /**
     * Gets a specific instance backup.
     * @param string $name Name of container
     * @param string $backup
     * @return \stdClass|null
     * @throws \Exception
     */
    public function backupInfo(string $name, string $backup): ?\stdClass
    {
        return $this->client->sendRequest($this->getEndPoint(). "/$name/backups/$backup", $this->client->getURL()::REQUEST_GET);
    }

    /**
     * Creates a new backup.
     * @param string $name Name of container
     * @param array $parameters
     * @return \stdClass|null
     * @throws \Exception
     */
    public function createBackup(string $name, array $parameters = []): ?\stdClass
    {
        if (empty($parameters)){
            $parameters = [
                "compression_algorithm" => "gzip",
                "optimized_storage" => true
            ];
        }

        return $this->client->sendRequest($this->getEndPoint(). "/$name/backups", $this->client->getURL()::REQUEST_POST, $parameters);
    }

    /**
     * Download the raw backup file(s) from the server.
     * @param string $name Name of container
     * @param string $backup
     * @param string $ext
     * @return \stdClass|null
     * @throws \Exception
     */
    public function downloadBackup(string $name, string $backup, string $ext = '.tar.gzip'): ?\stdClass
    {
        // Set the headers for file download
        set_time_limit(0);
         $filename = str_replace('.tar.gzip', '', $backup) . $ext;
         header('Content-Type: application/octet-stream');
         header('Content-Disposition: attachment; filename="' . $filename . '"');

        $this->client->setMoreCurlOptions([
            CURLOPT_TIMEOUT => 0,
            CURLOPT_WRITEFUNCTION => function ($resource, $data) {
                // Output the downloaded data directly to the browser
                echo $data;
                return strlen($data);
            }
        ]);
        return $this->client->sendRequest($this->getEndPoint(). "/$name/backups/$backup/export", $this->client->getURL()::REQUEST_GET);
    }

    /**
     * Returns a list of instance snapshots
     * @param string $name Name of container
     * @return \stdClass|null
     * @throws \Exception
     */
    public function snapshots (string $name): ?\stdClass
    {
        return $this->client->sendRequest($this->getEndPoint(). "/$name/snapshots?recursion=1", $this->client->getURL()::REQUEST_GET);
    }

    /**
     * Gets a specific instance snapshot.
     *
     * @param string $name Name of container
     * @param string $snapShotName
     * @return \stdClass|null
     * @throws \Exception
     */
    public function snapshotInfo(string $name, string $snapShotName): ?\stdClass
    {
        return $this->client->sendRequest($this->getEndPoint(). "/$name/snapshots/$snapShotName", $this->client->getURL()::REQUEST_GET);
    }

    /**
     * Creates a new snapshot.
     *
     * Here is an example:
     * ```
     * $client->instances()->createSnapshot([
     *  "name" => "snapshot_name", // not optional
     *  "expires_at" => "2021-03-23T17:38:37.753398689-04:00", // optional, time you want the snapshot to expire
     * ]);
     * ```
     * @param string $name Name of container
     * @param array $parameters
     * @return \stdClass|null
     * @throws \Exception
     */
    public function createSnapshot(string $name, array $parameters = []): ?\stdClass
    {
        return $this->client->sendRequest($this->getEndPoint(). "/$name/snapshots", $this->client->getURL()::REQUEST_DELETE, $parameters);
    }

    /**
     * Deletes the instance snapshot.
     *
     * @param string $name
     * @param string $snapShotName
     * @return \stdClass|null
     * @throws \Exception
     */
    public function deleteSnapshot(string $name, string $snapShotName): ?\stdClass
    {
        return $this->client->sendRequest($this->getEndPoint(). "/$name/snapshots/$snapShotName", $this->client->getURL()::REQUEST_DELETE);
    }


    /**
     * Gets the runtime state of the instance.
     *
     * This is a reasonably expensive call as it causes code to be run
     * inside the instance to retrieve the resource usage and network
     * information.
     * @param string $name Name of container
     * @return \stdClass|null
     * @throws \Exception
     */
    public function instanceMetrics(string $name): ?\stdClass
    {
        return $this->client->sendRequest($this->getEndPoint(). "/$name/state", $this->client->getURL()::REQUEST_GET);
    }

    /**
     * Changes the running state of the instance.
     *
     *  Here is an example:
     * ```
     * $client->instances()->changeState([
     *  "action" => "start", // not optional, it can be one of: state, stop, restart
     *  "force" => false, // optional
     *  "stateful" => false, // optional
     *  "timeout" => 30, // optional
     * ]);
     * ```
     * @param string $name Name of container
     * @param array $parameters
     * @return \stdClass|null
     * @throws \Exception
     */
    public function changeState(string $name, array $parameters): ?\stdClass
    {
        return $this->client->sendRequest($this->getEndPoint(). "/$name/state", $this->client->getURL()::REQUEST_PUT, $parameters);
    }


    /**
     * Start an instance
     *
     * @param string $name Container or instance name
     * @return \stdClass|null
     * @throws \Exception
     */
    public function start(string $name): ?\stdClass
    {
        return $this->changeState($name, [
            "action" => "start", // not optional, it can be one of: state, stop, restart
            "force" => false, // optional
            "timeout" => 30, // optional
        ]);
    }

    /**
     * Stop an instance
     *
     * @param string $name Container or instance name
     * @return \stdClass|null
     * @throws \Exception
     */
    public function stop(string $name): ?\stdClass
    {
        return $this->changeState($name, [
            "action" => "stop", // not optional, it can be one of: state, stop, restart
            "force" => false, // optional
            "timeout" => 30, // optional
        ]);
    }

    /**
     * Shutdown an instance: Alias of `stop()`
     *
     * @param string $name Container or instance name
     * @return \stdClass|null
     * @throws \Exception
     */
    public function shutdown(string $name): ?\stdClass
    {
        return $this->stop($name);
    }


    /**
     * Restart or Reboot an instance
     *
     * @param string $name Container or instance name
     * @return \stdClass|null
     * @throws \Exception
     */
    public function restart(string $name): ?\stdClass
    {
        return $this->changeState($name, [
            "action" => "restart", // not optional, it can be one of: state, stop, restart
            "force" => false, // optional
            "timeout" => 30, // optional
        ]);
    }

    /**
     * Reboot an instance: Alias of `restart()`
     *
     * @param string $name Container or instance name
     * @return \stdClass|null
     * @throws \Exception
     */
    public function reboot(string $name): ?\stdClass
    {
        return $this->restart($name);
    }

    /**
     * Updates the instance configuration, to restore from a snapshot, please use the `update()` method.
     *
     * Here is an example that updates an instance:
     *
     * ```
        $client->instances()->update('instance-name', ["description" => "My test instance"]);
     * ```
     *
     * @param string $name Name of container
     * @param array $parameters
     * @return \stdClass
     * @throws \Exception
     */
    public function patch(string $name, array $parameters): \stdClass
    {
        return $this->client->sendRequest($this->getEndPoint() . "/$name", $this->client->getURL()::REQUEST_PATCH, $parameters);
    }

    /**
     * Trigger a snapshot restore, use the `rename()` method to rename an instance.
     *
     * <br>
     *
     * If you know, all you want to do is update the description, please use the `patch()` method instead
     *
     * <br>
     *
     * Here is an example that triggers a snapshot restore:
     *
     * ```
     * $client->instances()->update('instance-name', ["restore" => "snapshot_name"]);
     * ```
     *
     * <br>
     * To update a device config, you first need to get the info of the instance, then update it like so, without that, it won't work:
     *
     * ```
     * $client->instances()->update('instance-name', [ "architecture" => $instanceInfo->metadata->architecture, "profiles" => ['default'], "config" => (array)$instanceInfo->metadata->config, "devices" => [...]]);
     * ```
     *
     * @param string $name Name of container
     * @param array $parameters
     * @return \stdClass
     * @throws \Exception
     */
    public function update(string $name, array $parameters): \stdClass
    {
        return $this->client->sendRequest($this->getEndPoint() . "/$name", $this->client->getURL()::REQUEST_PUT, $parameters);
    }

    /**
     * Renames an instance (note: Name can only contain alphanumeric and hyphen characters).
     *
     * @param string $oldName Old container name
     * @param string $newName New container name
     * @return \stdClass|null
     * @throws \Exception
     */
    public function rename(string $oldName, string $newName): ?\stdClass
    {
        $parameters = [
            'name' => $newName
        ];
        return $this->client->sendRequest($this->getEndPoint() . "/$oldName", $this->client->getURL()::REQUEST_POST, $parameters);
    }

    /**
     * Execute a command in a container
     *
     * @param string $name Name of container
     * @param array $parameters
     * @return \stdClass|null
     * @throws \Exception
     */
    public function execute(string $name, array $parameters): ?\stdClass
    {
        return $this->client->sendRequest($this->getEndPoint() . "/$name/exec", $this->client->getURL()::REQUEST_POST, $parameters);
    }

    /**
     * Creates or replace file in the instance (You can also create a directory).
     *
     * <br>
     *
     * Here is an example:
     *
     * ```
    * $client->instances()->createOrReplaceFile([
    *   "path" => "/root/new_file.txt" ,
    *   "body" => "The file content goes here",
    *   "X-Incus-uid" => 1000, // File owner UID (optional)
    *   "X-Incus-gid" => 1000, // File owner GID (optional)
    *   "X-Incus-mode" => 420, // File mode (optional)
    *   "X-Incus-type" => 'file', // Type of file (file, symlink or directory) (optional)
    *   "X-Incus-write" => 'overwrite', // Write mode (overwrite or append)(optional)
    * ]);
     * ```
     *
     *
     * @param string $name Name of container
     * @param array $parameters
     * @return \stdClass|null
     * @throws \Exception
     */
    public function createOrReplaceFile(string $name, array $parameters): ?\stdClass
    {
        $path = $parameters['path'] ?? '';
        $body = $parameters['body'] ?? '';
        $headers = $this->convertArrayToHttpHeader($parameters, ['X-Incus-uid', 'X-Incus-gid', 'X-Incus-mode', 'X-Incus-type', 'X-Incus-write']);
        return $this->client->sendRequest($this->getEndPoint() . "/$name/files?path=$path", $this->client->getURL()::REQUEST_POST, $body, $headers);
    }

    /**
     * Removes a file inside the instance
     *
     * @param string $name Name of container
     * @param string $filePath
     * @return \stdClass|null
     * @throws \Exception
     */
    public function deleteFile(string $name, string $filePath): ?\stdClass
    {
        return $this->client->sendRequest($this->getEndPoint() . "/$name/files?path=$filePath", $this->client->getURL()::REQUEST_DELETE);
    }

    /**
     * Gets the file content (raw). If it's a directory, a json list of files will be returned instead.
     *
     * @param string $name Name of container
     * @param string $filePath
     * @return mixed
     * @throws \Exception
     */
    public function getFile(string $name, string $filePath): mixed
    {
        return $this->client->sendRequest($this->getEndPoint() . "/$name/files?path=$filePath", $this->client->getURL()::REQUEST_GET);
    }

    /**
     * Gets the file or directory metadata (Note: For some reason, this returns null, so, useless).
     *
     * @param string $name Name of container
     * @param string $filePath
     * @return mixed
     * @throws \Exception
     */
    public function getFileMeta(string $name, string $filePath): mixed
    {
        return $this->client->sendRequest($this->getEndPoint() . "/$name/files?path=$filePath", $this->client->getURL()::REQUEST_HEAD);
    }

    /**
     * Deletes a specific instance.: This also deletes anything owned by the instance such as snapshots and backups.
     * @param string $name Name of container
     * @return \stdClass
     * @throws \Exception
     */
    public function delete(string $name)
    {
        return $this->client->sendRequest($this->getEndPoint() . "/$name", $this->client->getURL()::REQUEST_DELETE);

    }

    /**
     * Gets metrics of instances. (Doesn't work, it currently returns null)
     *
     * @return mixed
     * @throws \Exception
     */
    public function metrics(): mixed
    {
        return $this->client->sendRequest($this->client->getURL()::getBaseURL()  . "/metrics", $this->client->getURL()::REQUEST_GET);
    }


    /**
     * @inheritDoc
     */
    protected function getEndPoint(): string
    {
        return $this->client->getURL()::getBaseURL() . '/instances';
    }
}