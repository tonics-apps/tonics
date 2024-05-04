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

namespace App\Apps\TonicsCloud\Library\Incus;

use App\Apps\TonicsCloud\Library\Incus\Repositories\Certificate;
use App\Apps\TonicsCloud\Library\Incus\Repositories\Image;
use App\Apps\TonicsCloud\Library\Incus\Repositories\Instance;
use App\Apps\TonicsCloud\Library\Incus\Repositories\Operation;
use App\Apps\TonicsCloud\Library\Incus\Repositories\Server;


class Client
{
    # Response Operation Codes.
    public const OPERATION_CREATED = 100;
    public const OPERATION_STARTED = 101;
    public const OPERATION_STOPPED = 102;
    public const OPERATION_RUNNING = 103;
    public const OPERATION_CANCELLING = 104;
    public const OPERATION_PENDING = 105;
    public const OPERATION_STARTING = 106;
    public const OPERATION_STOPPING = 107;
    public const OPERATION_ABORTING = 108;
    public const OPERATION_FREEZING = 109;
    public const OPERATION_FROZEN = 110;
    public const OPERATION_THAWED = 111;
    public const OPERATION_ERROR = 112;
    public const OPERATION_READY = 113;

    # Response Success Codes
    public const SUCCESS_OK = 200;

    # Response Error Codes
    public const ERROR_FAILURE = 400;
    public const ERROR_CANCELED = 401;

    private int $timeout = 300;
    private bool $debug = false;
    private string $certificateString = '';
    private string $privateKeyString = '';
    private array $curlInfo = [];
    private array $moreCurlOptions = [];
    private URL $URL;
    protected array $repositories = [];
    protected mixed $response = null;

    /**
     * Here is an example:
     *
     * ```
     * $client = new Client(new URL('https://50.116.34.235:7597'), $certAndKey);
     * ```
     * @param URL $URL
     * @param array|\stdClass $certAndKey
     */
    public function __construct(URL $URL, array|\stdClass $certAndKey = []) {
        $certAndKey = (array)$certAndKey;
        $this->URL = $URL;
        $this->setCertificateString($certAndKey['cert'] ?? '');
        $this->setPrivateKeyString($certAndKey['key'] ?? '');
        $this->initializeRepositories();
    }

    protected function initializeRepositories(): void
    {
        $this->repositories = [
            Certificate::class => new Certificate($this),
            Instance::class => new Instance($this),
            Operation::class => new Operation($this),
            Image::class => new Image($this),
            Server::class => new Server($this),
        ];
    }

    /**
     * @return Certificate
     */
    public function certificates(): Certificate
    {
        return $this->getRepository(Certificate::class);
    }

    /**
     * Aliases of `instances()` method
     * @return Instance
     */
    public function containers(): Instance
    {
        return $this->instances();
    }

    /**
     * @return Instance
     */
    public function instances(): Instance
    {
        return $this->getRepository(Instance::class);
    }

    /**
     * @return Image
     */
    public function images(): Image
    {
        return $this->getRepository(Image::class);
    }

    /**
     * @return Server
     */
    public function server(): Server
    {
        return $this->getRepository(Server::class);
    }

    /**
     * @return Operation
     */
    public function operations(): Operation
    {
        return $this->getRepository(Operation::class);
    }

    public function addRepository(object $repository): void
    {
        $this->repositories[$repository::class] = $repository;
    }

    public function getRepository(string $name)
    {
        if (!isset($this->repositories[$name])) {
            throw new \InvalidArgumentException("Repository '{$name}' does not exist.");
        }

        return $this->repositories[$name];
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        if (isset($this->getResponse()->status_code)){
            return $this->getResponse()->status_code === self::SUCCESS_OK;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function operationIsCreated(): bool
    {
        if (isset($this->getResponse()->status_code)){
            return $this->getResponse()->status_code === self::OPERATION_CREATED;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function operationHasStarted(): bool
    {
        if (isset($this->getResponse()->status_code)){
            return $this->getResponse()->status_code === self::OPERATION_STARTED;
        }

        return false;
    }


    /**
     * @return bool
     */
    public function operationIsStarting(): bool
    {
        if (isset($this->getResponse()->status_code)){
            return $this->getResponse()->status_code === self::OPERATION_STARTING;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function operationIsStopped(): bool
    {
        if (isset($this->getResponse()->status_code)){
            return $this->getResponse()->status_code === self::OPERATION_STOPPED;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function operationIsStopping(): bool
    {
        if (isset($this->getResponse()->status_code)){
            return $this->getResponse()->status_code === self::OPERATION_STOPPING;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        $code = null;
        if (isset($this->getResponse()->error_code)){
            $code = $this->getResponse()->error_code;
        }elseif (isset($this->getResponse()->status_code)){
            $code = $this->getResponse()->status_code;
        }

        return $code >= self::ERROR_FAILURE;
    }

    /**
     * @return string
     */
    public function errorMessage(): string
    {
        if (isset($this->getResponse()->error)){
            return $this->getResponse()->error;
        }

        return '';
    }

    /**
     * @return array
     * Remove this method from your code before deploying
     */
    public function getCURLInfo(): array
    {
        return $this->curlInfo;
    }

    /**
     * @return URL
     */
    public function getURL(): URL
    {
        return $this->URL;
    }

    /**
     * @param URL $URL
     */
    public function setURL(URL $URL): void
    {
        $this->URL = $URL;
    }

    /**
     * @return string
     */
    public function getCertificateString(): string
    {
        return $this->certificateString;
    }

    /**
     * @param string $certificateString
     * @return Client
     */
    public function setCertificateString(#[\SensitiveParameter] string $certificateString): Client
    {
        $this->certificateString = $certificateString;
        return $this;
    }


    /**
     * @param string $url
     * @param string $method
     * @param array|string $opts
     * @param array $headers
     * @return mixed
     * @throws \Exception
     */
    public function sendRequest(string $url, string $method, array|string $opts = [], array $headers = []): mixed
    {
        if (is_string($opts)){
            $post_fields = $opts;
        } else {
            $post_fields = json_encode($opts);
        }

        $curl_info = [
            CURLOPT_URL => $url,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POSTFIELDS => $post_fields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTPHEADER => [...['Content-Type: application/json'], ...$headers],
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_SSLCERT_BLOB => $this->getCertificateString(),
            CURLOPT_SSLKEY_BLOB => $this->getPrivateKeyString(),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];

        foreach ($this->moreCurlOptions as $key => $value){
            $curl_info[$key] = $value;
        }
        if ($this->debug){
            $curl_info[CURLOPT_VERBOSE] = true;
        }

        if (empty($opts)){
            unset($curl_info[CURLOPT_POSTFIELDS]);
        }

        $curl = curl_init();
        curl_setopt_array($curl, $curl_info);

        $response = curl_exec($curl);

        $info = curl_getinfo($curl);
        $this->curlInfo = $info;

        // Check for errors
        if (curl_errno($curl)) {
            throw new \Exception(curl_error($curl));
        }

        curl_close($curl);

        if ($this->isJSON($response)){
            $this->setResponse(json_decode($response));
        } else {
            $this->setResponse($response);
        }

        return $this->getResponse();
    }

    /**
     * Check if string is JSON
     * @credit: https://stackoverflow.com/a/49729033
     * @param $string
     * @return bool
     */
    private function isJSON($string): bool
    {
        // 1. Speed up the checking & prevent exception throw when non string is passed
        if (is_numeric($string) ||
            !is_string($string) ||
            !$string) {
            return false;
        }

        $cleaned_str = trim($string);
        if (!$cleaned_str || !in_array($cleaned_str[0], ['{', '['])) {
            return false;
        }

        // 2. Actual checking
        $str = json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE) && $str && $str != $string;
    }


    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     * @return Client
     */
    public function setDebug(bool $debug): Client
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrivateKeyString(): string
    {
        return $this->privateKeyString;
    }

    /**
     * @param string $privateKeyString
     * @return Client
     */
    public function setPrivateKeyString(#[\SensitiveParameter] string $privateKeyString): Client
    {
        $this->privateKeyString = $privateKeyString;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResponse(): mixed
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     * @return Client
     */
    public function setResponse(mixed $response): Client
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return array
     */
    public function getMoreCurlOptions(): array
    {
        return $this->moreCurlOptions;
    }

    /**
     * @param array $moreCurlOptions
     */
    public function setMoreCurlOptions(array $moreCurlOptions): void
    {
        $this->moreCurlOptions = $moreCurlOptions;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): Client
    {
        $this->timeout = $timeout;
        return $this;
    }
}