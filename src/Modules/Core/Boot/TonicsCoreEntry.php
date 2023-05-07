<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Boot;

use Ahc\Env\Loader;
use App\Modules\Core\Configs\AppConfig;

class TonicsCoreEntry
{
    public static function entry()
    {
/*        $message = 'The quick brown fox jumped over the lazy dog.';

        # Generate keypair
        $keyPair = sodium_crypto_sign_keypair();
        # The PubKey
        $pubKey = sodium_crypto_sign_publickey($keyPair);
        $pubKeyBaseEncoded = sodium_bin2base64($pubKey, SODIUM_BASE64_VARIANT_ORIGINAL_NO_PADDING);
        dd($pubKeyBaseEncoded, base64_decode($pubKeyBaseEncoded) === $pubKey);
        $privateKey = sodium_crypto_sign_secretkey($keyPair);


        # Sign a message By Using the Private or Secret Key
        $signature = sodium_crypto_sign_detached($message, $privateKey);

        # Verify a message
        $verifyResult = sodium_crypto_sign_verify_detached($signature, $message, base64_decode($pubKeyBaseEncoded));
        dd([$signature, $pubKeyBaseEncoded], $verifyResult);*/

                #-----------------------------
            # LOAD ENV FILE
        #---------------------------------
        (new Loader)->load(AppConfig::getEnvFilePath());

                #-----------------------------------
            # START BOOTING BOOTY ;)
        #-------------------------------------------
        try {
            if (AppConfig::isProduction() === false) {
                error_reporting(E_ALL);
                ini_set("display_errors", "On");
            }

            AppConfig::initLoaderMinimal()->init();
            AppConfig::initLoaderOthers()->BootDaBoot();
        } catch (\Exception $e) {
            die($e->getMessage());
            // Log..
        }
    }
}