<?php
declare(strict_types=1);
namespace Soatok\X3MAC;
class Signature
{
    public function __construct(
        #[\SensitiveParameter]
        private string $bytes
    ) {}

    public function getEphemeralPublicKey(): PublicKey
    {
        return new PublicKey(substr($this->bytes, 0, 32));
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getMAC(): string
    {
        return substr($this->bytes, 32);
    }

    public function getBytes(): string
    {
        return $this->bytes;
    }
}
