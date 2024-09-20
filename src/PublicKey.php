<?php
declare(strict_types=1);
namespace Soatok\X3MAC;

class PublicKey
{
    public function __construct(
        #[\SensitiveParameter]
        private string $bytes
    ) {}

    public function getBytes(): string
    {
        return $this->bytes;
    }
}
