<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Library\Incus;

use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Exception;

class IncusHelper
{

    /**
     * Returns the cert and the key
     * @param array $config
     * @return array
     * @throws Exception
     */
    public static function generateCertificateEncrypted(array $config = []): array
    {
        // Check if OpenSSL functions are available
        if (!function_exists('openssl_pkey_new') || !function_exists('openssl_csr_new')
            || !function_exists('openssl_csr_sign') || !function_exists('openssl_x509_export')
            || !function_exists('openssl_pkey_export')) {
            throw new Exception('OpenSSL functions are not available on this system.');
        }

        $days = $config['days'] ?? 365;
        $dn = [
            "countryName"            => "NG",
            "stateOrProvinceName"    => "Isle Of Wight",
            "localityName"           => "Cowes",
            "organizationName"       => "Tonics",
            "organizationalUnitName" => "DevsrealmGuy",
            "commonName"             => "TonicsCloud",
            "emailAddress"           => "olayemi@tonics.app"
        ];

        // Generate certificate
        $privateKey = openssl_pkey_new();
        $cert = openssl_csr_new($dn, $privateKey);
        $cert = openssl_csr_sign($cert, null, $privateKey, $days);

        // Generate strings
        openssl_x509_export($cert, $certString);
        openssl_pkey_export($privateKey, $privateKeyString);

        // Concatenate the private key and certificate into a single PEM file
        return [
            'cert' => $certString,
            'key' => $privateKeyString,
        ];
    }

}