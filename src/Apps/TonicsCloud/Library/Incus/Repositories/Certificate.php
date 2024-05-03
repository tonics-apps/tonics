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

class Certificate extends AbstractRepository
{

    /**
     * Adds a certificate to the trust store as an untrusted user.
     * In this mode, the trust_token property must be set to the correct value.
     *
     * The certificate field can be omitted in which case the TLS client
     * certificate in use for the connection will be retrieved and added to thetrust store.
     *
     * <br>
     *
     * Note: Sometimes, you would receive a 404 with the message: The provided certificate isn't valid yet, this is because of sync issue,
     * so, it is your responsibility to check for that error and retry adding it.
     *
     * <br>
     *
     * Here is an example
     * ```
     * $client->certificates()->add([
     *       "certificate" => "Put X509 PEM certificate here" , // optional, it would retrieve it from curl, not optional if you are adding a new cert
     *       "name" => "tonics_cloud",
     *       "trust_token" => "trust key password",
     *       "restricted" => true, // optional
     *       "token" => false, // optional
     *       "type" => 'client', // not optional, please always include the client type
     * ]);
     * ```
     * @param array $parameters
     * @return \stdClass
     * @throws \Exception
     */
    public function add(array $parameters): \stdClass
    {
        if (isset($parameters['certificate'])){
            $parameters['certificate'] =  trim(str_replace([
                "-----BEGIN CERTIFICATE-----",
                "-----END CERTIFICATE-----"
            ], null, $parameters['certificate']));
        }
        return $this->client->sendRequest($this->getEndPoint(), $this->client->getURL()::REQUEST_POST, $parameters);
    }

    /**
     * Updates the entire certificate configuration (This currently doesn't work, I get timeout, so, workaround is to delete and add a new certificate).
     *
     * Here is an example
     * ```
     * $client->certificates()->update('9dd0304d4202', [
     *       "certificate" => "Put X509 PEM certificate here" , // not optional since you want to update a cert
     *       "name" => "tonics_cloud",
     *       "type" => 'client', // not optional, please always include the client type
     * ]);
     * ```
     * @param string $fingerPrint
     * @param array $parameters
     * @return \stdClass
     * @throws \Exception
     */
    public function update(string $fingerPrint, array $parameters): \stdClass
    {
        if (isset($parameters['certificate'])){
            $parameters['certificate'] =  trim(str_replace([
                "-----BEGIN CERTIFICATE-----",
                "-----END CERTIFICATE-----"
            ], null, $parameters['certificate']));
        }

        return $this->client->sendRequest($this->getEndPoint($fingerPrint), $this->client->getURL()::REQUEST_PUT, $parameters);
    }

    /**
     * @param string $fingerPrint
     * @return \stdClass
     * @throws \Exception
     */
    public function delete(string $fingerPrint)
    {
        return $this->client->sendRequest($this->getEndPoint($fingerPrint), $this->client->getURL()::REQUEST_DELETE);

    }

    /**
     * @param string $fingerPrint
     * @return \stdClass
     * @throws \Exception
     */
    public function info(string $fingerPrint): \stdClass
    {
        return $this->client->sendRequest($this->getEndPoint($fingerPrint), $this->client->getURL()::REQUEST_GET);

    }

    /**
     * @throws \Exception
     */
    public function all()
    {
        return $this->client->sendRequest($this->getEndPoint() . '?recursion=1', $this->client->getURL()::REQUEST_GET);
    }

    protected function getEndPoint(string $fingerPrint = ''): string
    {
        $path = (!empty($fingerPrint)) ? "/certificates/$fingerPrint" : '/certificates';
        return $this->client->getURL()::getBaseURL() . $path;
    }
}