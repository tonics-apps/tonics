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

namespace App\Apps\TonicsCloud\Interfaces;

use App\Apps\TonicsCloud\Library\Incus\Client;
use App\Apps\TonicsCloud\Services\ContainerService;

abstract class CloudAppInterface
{
    private ?int $containerID = null;
    private mixed $postPrepareForFlight = null;
    private string $incusContainerName = '';
    private mixed $fields = null;
    private array $containerReplaceableVariables = [];

    const PREPARATION_TYPE_SETTINGS = 'PREPARATION_TYPE_SETTINGS'; # For The Manual Configuration of The Container App
    const PREPARATION_TYPE_AUTO = 'PREPARATION_TYPE_AUTO'; # For Auto Configuration of The Container App
    const PREPARATION_TYPE_INSTALL = 'PREPARATION_TYPE_INSTALL';
    const PREPARATION_TYPE_UNINSTALL = 'PREPARATION_TYPE_UNINSTALL';

    /**
     * @return mixed
     */
    public function getFields(): mixed
    {
        return $this->fields;
    }

    public function getFieldsToString(): false|string
    {
        return json_encode($this->fields);
    }

    /**
     * @param mixed $fields
     * @return CloudAppInterface
     */
    public function setFields(mixed $fields): CloudAppInterface
    {
        $this->fields = $fields;
        return $this;
    }

    public function getContainerReplaceableVariables(): array
    {
        return $this->containerReplaceableVariables;
    }

    public function setContainerReplaceableVariables(array|\stdClass $containerReplaceableVariables): void
    {
        if (is_object($containerReplaceableVariables)) {
            $containerReplaceableVariables = (array)$containerReplaceableVariables;
        }

        $replaceableVariables = [];
        foreach ($containerReplaceableVariables as $key => $value) {
            $key = strtoupper($key);
            $replaceableVariables["[[$key]]"] = function () use ($value) { return $value; };
        }

        $replaceableVariables["[[RAND_STRING]]"] = function () { return helper()->randString(); };

        $this->containerReplaceableVariables = $replaceableVariables;
    }

    /**
     * This replaces all variables in the $string content, the variables would be pulled from the
     * containerReplaceableVariables
     * @param string $content
     * @return string
     */
    public function replaceContainerGlobalVariables(string $content): string
    {
        return $this::replaceVariables($content, $this->getContainerReplaceableVariables());
    }

    /**
     * Gets the Incus Client
     * @throws \Exception
     * @throws \Throwable
     */
    public function client(int $containerID = null): Client
    {
        $container = ContainerService::getContainer($this->getContainerID(), false);
        if (empty($container)) {
            throw new \Exception("An Error Occurred While Trying To Get Container");
        }

        return ContainerService::getIncusClient(json_decode($container?->serviceInstanceOthers));
    }

    /**
     * This gives the app the opportunity to prepare for flight, for example, when user clicks the save changes in
     * the app settings, you get the fieldDetails which you can use to extract the relevant details you want.
     *
     * I would give you the flightType, this way, you know what you are preparing for. You are free to ignore the flight preparation.
     * @param array $data - Can be field or global post data when doing auto configuration
     * @param string $flightType
     * @return mixed
     */
    abstract public function prepareForFlight(array $data, string $flightType = self::PREPARATION_TYPE_SETTINGS): mixed;

    /**
     * Install The App Into The Container
     */
    abstract public function install();

    /**
     * UnInstall The App From The Container
     */
    abstract public function uninstall();

    /**
     * Update App Settings In The Container
     */
    abstract public function updateSettings();

    /**
     * @return int|null
     */
    public function getContainerID(): ?int
    {
        return $this->containerID;
    }

    /**
     * @param int|null $containerID
     * @return CloudAppInterface
     */
    public function setContainerID(?int $containerID): CloudAppInterface
    {
        $this->containerID = $containerID;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostPrepareForFlight(): mixed
    {
        return $this->postPrepareForFlight;
    }

    /**
     * @param mixed $postPrepareForFlight
     * @return CloudAppInterface
     */
    public function setPostPrepareForFlight(mixed $postPrepareForFlight): CloudAppInterface
    {
        $this->postPrepareForFlight = $postPrepareForFlight;
        return $this;
    }

    /**
     * @return string
     */
    public function getIncusContainerName(): string
    {
        return $this->incusContainerName;
    }

    /**
     * @param string $incusContainerName
     * @return CloudAppInterface
     */
    public function setIncusContainerName(string $incusContainerName): CloudAppInterface
    {
        $this->incusContainerName = $incusContainerName;
        return $this;
    }

    /**
     * @param string $filePath
     * @param string $fileContent
     * @return bool
     * @throws \Exception
     */
    protected function createOrReplaceFile(string $filePath, string $fileContent): bool
    {
        $client = $this->client();
        $client->containers()->createOrReplaceFile($this->getIncusContainerName(), [
            "path" => $filePath ,
            "body" => $fileContent,
            "X-Incus-type" => 'file', // Type of file (file, symlink or directory) (optional)
            "X-Incus-write" => 'overwrite', // Write mode (overwrite or append)(optional)
        ]);

        if ($client->isSuccess()){
            return true;
        }

        throw new \Exception($client->errorMessage());
    }

    /**
     * Run commands, an example:
     *
     * - `runCommand(null, null, "/bin/systemctl", "start", "nginx")`;
     * - `runCommand(null, null, "/bin/systemctl", "restart", "mariadb")`;
     *
     * or event better use bash and call the commands and its option in one fell swoop, you also don't need /bin no more:
     *
     * - `runCommand(null, null, "bash", "-c", "systemctl restart nginx")`;
     * - `runCommand(null, null, "bash", "-c", "ls -lah /etc/nginx/conf.d")`;
     *
     * To run a command and get an output, you can do (the first callback is for the stdout and the second is for stderr):
     * - `runCommand(function ($out){}, function ($err){}, "ls", "lah")`;
     * - `runCommand(function ($out){}, function ($err){}, "cat", "/etc/nginx/conf.d/default.conf")`;
     * @param callable|null $outputOnSuccess
     * @param callable|null $outputOnError
     * @param ...$commands
     * @return bool
     * @throws \Exception
     */
    protected function runCommand(callable $outputOnSuccess = null, callable $outputOnError = null, ...$commands): bool
    {
        $post = [
            "command" => $commands,
            "interactive" => false,
            "record-output" => true,
        ];

        $client = $this->client();
        $exec = $client->instances()->execute($this->getIncusContainerName(), $post);
        $waitResponse = null;
        if($exec){
            // wait until the background operation is completed and return the operationResult
            $waitResponse = $client->operations()->wait($exec->operation);
            $apiVersion = $client->getURL()::API_VERSION;
            if (isset($waitResponse->metadata->metadata->output)){
                if ($outputOnSuccess){
                    $url = $client->getURL()::getBaseURL() . str_replace("/$apiVersion", '', $waitResponse->metadata->metadata->output->{1});
                    $out = $client->sendRequest($url, $client->getURL()::REQUEST_GET);
                    $outputOnSuccess($out);
                }

                if ($outputOnError){
                    $url = $client->getURL()::getBaseURL() . str_replace("/$apiVersion", '', $waitResponse->metadata->metadata->output->{2});
                    $out = $client->sendRequest($url, $client->getURL()::REQUEST_GET);
                    $outputOnError($out);
                }

                # If user doesn't handle the errorOutput, and the exit code is not 0, we throw an error:
                if ($outputOnError === null && isset($waitResponse->metadata->metadata->return)
                    && $waitResponse->metadata->metadata->return > 0){
                    $url = $client->getURL()::getBaseURL() . str_replace("/$apiVersion", '', $waitResponse->metadata->metadata->output->{2});
                    throw new \Exception($client->sendRequest($url, $client->getURL()::REQUEST_GET));
                }
            }
        }

        return isset($waitResponse->metadata->status) &&
            isset($waitResponse->metadata->metadata->return) &&
            $waitResponse->metadata->metadata->return === 0 &&
            strtoupper($waitResponse->metadata->status) === 'SUCCESS';
    }

    /**
     * @param string $serviceName
     * @param string $signal
     * @return bool
     * @throws \Exception
     */
    protected function signalSystemDService(string $serviceName, string $signal = CloudAppSignalInterface::SystemDSignalRestart): bool
    {
        return $this->runCommand( null, null, "bash", "-c", "systemctl $signal $serviceName");
    }

    /**
     * Replace multiple occurrences of target strings in the content with results of corresponding replacer functions.
     *
     * ```
     * $content = "This is a [[RAND]] test string. Test me! [[RAND]]";
     * $replacements = [
     *     "is" => function() { return "was"; },
     *     "test" => function() { return "exam"; },
     *     "[[RAND]]" => function() { return bin2hex(random_bytes(5)); }
     * ];
     *
     * $result = $this::replaceVariables($content, $replacements);
     * //  This was a dfff08 exam string. Test me! e549c
     * ```
     *
     * @param string $content The original content.
     * @param array $replacements An associative array where keys are target strings and values are replacer functions.
     *                            Each replacer function should return the replacement string.
     * @return string The content with replacements applied.
     */
    public static function replaceVariables(string $content, array $replacements): string
    {
        // Iterate through each target string and its corresponding replacer
        foreach ($replacements as $targetString => $replacer) {
            // Calculate the length of the target string
            $targetStringLength = strlen($targetString);

            // Replace all occurrences of the target string with the result of the corresponding replacer function
            while (($pos = stripos($content, $targetString)) !== false) {
                $replace = $replacer();
                // Replace the target string with the result of the replacer function
                $content = substr_replace($content, $replace, $pos, $targetStringLength);
            }
        }

        return $content;
    }


}