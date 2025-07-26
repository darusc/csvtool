<?php

namespace Csvtool\Services\Cryptography;

use Csvtool\Exceptions\CryptographyException;
use Csvtool\Exceptions\FileNotFoundException;
use Csvtool\Exceptions\FilePermissionException;
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
     * @throws InvalidKeyException
     * @throws FileNotFoundException
     * @throws FilePermissionException
     */
    public static function forEncryption(string $publicKeyPath): static
    {
        $key = Util::loadPublicKey($publicKeyPath);
        return new static(null, $key);
    }

    /**
     * @param string $privateKeyPath The path to a file containing the private key used for decryption
     * @return static|null
     * @throws InvalidKeyException
     * @throws FileNotFoundException
     * @throws FilePermissionException
     */
    public static function forDecryption(string $privateKeyPath): ?static
    {
        $key = Util::loadPrivateKey($privateKeyPath);
        return new static($key, null);
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
     * @throws CryptographyException
     */
    public function encrypt(string $data): string
    {
        if($this->publicKey === null) {
            throw new CryptographyException("Encryption error", openssl_error_string());
        }

        openssl_public_encrypt($data, $encrypted, $this->publicKey);
        return base64_encode($encrypted);
    }

    /**
     * @param string $data Data to decrypt
     * @return string The encrypted data in base64 decoded
     * @throws CryptographyException
     */
    public function decrypt(string $data): string
    {
        if($this->privateKey === null) {
            throw new CryptographyException("Decryption error", openssl_error_string());
        }

        openssl_private_decrypt(base64_decode($data), $decrypted, $this->privateKey);
        return $decrypted;
    }
}