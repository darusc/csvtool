<?php

namespace Csvtool\Services\Cryptography;

use Csvtool\Exceptions\InvalidActionException;
use Csvtool\Exceptions\InvalidFileException;
use Csvtool\Exceptions\InvalidKeyException;
use Exception;
use http\Exception\InvalidArgumentException;
use OpenSSLAsymmetricKey;

/**
 * Service used for signing and verifying using asymmetric keys.
 * Can only sign or verify based on the factory method used.
 */
class SignatureService
{
    /**
     * @param string $privateKeyPath The path to a file containing the private key used for encryption
     * @return static
     * @throws InvalidFileException
     * @throws InvalidKeyException
     */
    public static function forSigning(string $privateKeyPath): static
    {
        $key = Util::loadPrivateKey($privateKeyPath);
        return new static($key, null);
    }

    /**
     * @param string $publicKeyPath The path to a file containing the public key used for verification
     * @return static
     * @throws InvalidFileException
     * @throws InvalidKeyException
     */
    public static function forVerification(string $publicKeyPath): static
    {
        $key = Util::loadPublicKey($publicKeyPath);
        return new static(null, $key);
    }

    private function __construct(
        private readonly ?OpenSSLAsymmetricKey $privateKey,
        private readonly ?OpenSSLAsymmetricKey $publicKey
    )
    {
    }

    /**
     * @param string $data Data to sign
     * @return string The resulted Base64 encoded signature
     * @throws InvalidActionException
     * @throws Exception
     */
    public function sign(string $data): string
    {
        if ($this->privateKey === null) {
            throw new InvalidActionException("signing");
        }

        $ok = openssl_sign($data, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);
        if (!$ok) {
            throw new Exception('Signing failed ' . openssl_error_string());
        }

        return base64_encode($signature);
    }

    /**
     * @param string $data
     * @param string $signature Base64 encoded signature to verify
     * @return bool
     * @throws InvalidActionException
     * @throws Exception
     */
    public function verify(string $data, string $signature): bool
    {
        if ($this->publicKey === null) {
            throw new InvalidActionException("decrypting");
        }

        $decoded = base64_decode($signature);
        if ($decoded === false) {
            throw new InvalidArgumentException('Invalid base64 signature');
        }

        $ok = openssl_verify($data, $decoded, $this->publicKey, OPENSSL_ALGO_SHA256);
        if ($ok == -1) {
            throw new Exception('Verification failed: ' . openssl_error_string());
        }

        return $ok === 1;
    }
}