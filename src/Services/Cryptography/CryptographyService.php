<?php

namespace Csvtool\Services\Cryptography;

use Csvtool\Exceptions\InvalidActionException;
use Csvtool\Exceptions\InvalidFileException;
use Csvtool\Exceptions\InvalidKeyException;
use OpenSSLAsymmetricKey;

/**
 * Service used for encryption and decryption with asymmetric keys.
 * Can only encrypt or decrypt based on the factory method used.
 */
class CryptographyService
{
    /**
     * @param string $publicKeyPath The path to a file containing the public key used for encryption
     * @return static
     * @throws InvalidFileException
     * @throws InvalidKeyException
     */
    public static function forEncryption(string $publicKeyPath): static
    {
        if (!file_exists($publicKeyPath) || !is_readable($publicKeyPath)) {
            throw new InvalidFileException($publicKeyPath);
        }

        $key = openssl_pkey_get_public(file_get_contents($publicKeyPath));
        if ($key !== false) {
            return new static(null, $key);
        } else {
            throw new InvalidKeyException();
        }
    }

    /**
     * @param string $privateKeyPath The path to a file containing the private key used for decryption
     * @return static|null
     * @throws InvalidFileException
     * @throws InvalidKeyException
     */
    public static function forDecryption(string $privateKeyPath): ?static
    {
        if (!file_exists($privateKeyPath) || !is_readable($privateKeyPath)) {
            throw new InvalidFileException($privateKeyPath);
        }

        $key = openssl_pkey_get_private(file_get_contents($privateKeyPath));
        if ($key !== false) {
            return new static($key, null);
        } else {
            throw new InvalidKeyException();
        }
    }

    private function __construct(
        private readonly ?OpenSSLAsymmetricKey $privateKey,
        private readonly ?OpenSSLAsymmetricKey $publicKey
    )
    {
    }

    /**
     * @param string $data Data to encrypt
     * @return string The encrypted data in base64 format
     * @throws InvalidActionException
     */
    public function encrypt(string $data): string
    {
        if($this->publicKey === null) {
            throw new InvalidActionException("encrypting");
        }

        openssl_public_encrypt($data, $encrypted, $this->publicKey);
        return base64_encode($encrypted);
    }

    /**
     * @param string $data Data to decrypt
     * @return string The encrypted data in base64 decoded
     * @throws InvalidActionException
     */
    public function decrypt(string $data): string
    {
        if($this->privateKey === null) {
            throw new InvalidActionException("decrypting");
        }

        openssl_private_decrypt(base64_decode($data), $decrypted, $this->privateKey);
        return $decrypted;
    }
}