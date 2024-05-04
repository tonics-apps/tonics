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