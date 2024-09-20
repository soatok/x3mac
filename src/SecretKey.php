<?php
declare(strict_types=1);
namespace Soatok\X3MAC;
class SecretKey
{
    private PublicKey $publicKey;

    public function __construct(
        #[\SensitiveParameter]
        private readonly string $bytes,
        ?PublicKey $publicKey = null
    ) {
        if (is_null($publicKey)) {
            $publicKey = new PublicKey(sodium_crypto_box_publickey_from_secretkey($this->bytes));
        }
        $this->publicKey = $publicKey;
    }

    public static function generate(): static
    {
        $bytes = sodium_crypto_box_keypair();
        $sk = sodium_crypto_box_secretkey($bytes);
        $pk = new PublicKey(sodium_crypto_box_publickey($bytes));
        return new static($sk, $pk);
    }

    public function getPublicKey(): PublicKey
    {
        return $this->publicKey;
    }

    public function getBytes(): string
    {
        return $this->bytes;
    }
}
