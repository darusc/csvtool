<?php

namespace Csvtool\Services\Cryptography;

use Csvtool\Exceptions\InvalidFileException;
use Csvtool\Exceptions\InvalidKeyException;
use OpenSSLAsymmetricKey;

class Util
{
    /**
     * @throws InvalidFileException
     * @throws InvalidKeyException
     */
    public static function loadPublicKey(string $publicKeyPath): OpenSSLAsymmetricKey
    {
        if (!file_exists($publicKeyPath) || !is_readable($publicKeyPath)) {
            throw new InvalidFileException($publicKeyPath);
        }

        $key = openssl_pkey_get_public(file_get_contents($publicKeyPath));
        if($key === false){
            throw new InvalidKeyException();
        } else {
            return $key;
        }
    }

    /**
     * @throws InvalidFileException
     * @throws InvalidKeyException
     */
    public static function loadPrivateKey(string $privateKeyPath): OpenSSLAsymmetricKey
    {
        if (!file_exists($privateKeyPath) || !is_readable($privateKeyPath)) {
            throw new InvalidFileException($privateKeyPath);
        }

        $key = openssl_pkey_get_private(file_get_contents($privateKeyPath));
        if($key === false){
            throw new InvalidKeyException();
        } else {
            return $key;
        }
    }
}