<?php

namespace Csvtool\Services\Cryptography;

use Csvtool\Exceptions\FileNotFoundException;
use Csvtool\Exceptions\FilePermissionException;
use Csvtool\Exceptions\InvalidKeyException;
use Csvtool\Validators\FileValidator;
use OpenSSLAsymmetricKey;

class Util
{
    /**
     * @throws FileNotFoundException
     * @throws FilePermissionException
     * @throws InvalidKeyException
     */
    public static function loadPublicKey(string $publicKeyPath): OpenSSLAsymmetricKey
    {
        FileValidator::validate($publicKeyPath);

        $key = openssl_pkey_get_public(file_get_contents($publicKeyPath));
        if($key === false){
            throw new InvalidKeyException();
        } else {
            return $key;
        }
    }

    /**
     * @throws FileNotFoundException
     * @throws FilePermissionException
     * @throws InvalidKeyException
     */
    public static function loadPrivateKey(string $privateKeyPath): OpenSSLAsymmetricKey
    {
        FileValidator::validate($privateKeyPath);

        $key = openssl_pkey_get_private(file_get_contents($privateKeyPath));
        if($key === false){
            throw new InvalidKeyException();
        } else {
            return $key;
        }
    }
}