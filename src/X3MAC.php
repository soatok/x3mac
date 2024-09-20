<?php
declare(strict_types=1);
namespace Soatok\X3MAC;

class X3MAC
{
    public function sign(
        SecretKey $senderKey,
        PublicKey $receiverKey,
        string $message
    ): Signature {
        $ephemeral = sodium_crypto_box_keypair();
        $ephSecret = sodium_crypto_box_secretkey($ephemeral);
        $ephPublic = sodium_crypto_box_publickey($ephemeral);

        // This is an ephemeral symmetric secret:
        // DH(EK_a, IK_B)
        $ikm1 = sodium_crypto_scalarmult($ephSecret, $receiverKey->getBytes());

        // This is a non-forward-secure symmetric secret:
        // DH(IK_a, IK_B)
        $ikm2 = sodium_crypto_scalarmult($senderKey->getBytes(), $receiverKey->getBytes());

        // Calculate the symmetric MAC key
        $k = $this->kdf(
            $ikm1,
            $ikm2,
            $senderKey->getPublicKey()->getBytes(),
            $receiverKey->getBytes(),
            $ephPublic
        );

        // Calculate a symmetric MAC
        $mac = sodium_crypto_generichash($message, $k);
        return new Signature($ephPublic . $mac);
    }

    public function verify(
        Signature $signature,
        PublicKey $senderKey,
        SecretKey $receiverKey,
        string $message
    ): bool {
        $ephPublic = $signature->getEphemeralPublicKey()->getBytes();

        // This is an ephemeral symmetric secret:
        // DH(EK_A, IK_B)
        $ikm1 = sodium_crypto_scalarmult($receiverKey->getBytes(), $ephPublic);

        // This is a non-forward-secure symmetric secret:
        // DH(IK_A, IK_B)
        $ikm2 = sodium_crypto_scalarmult($receiverKey->getBytes(), $senderKey->getBytes());

        // Calculate the symmetric MAC key
        $k = $this->kdf(
            $ikm1,
            $ikm2,
            $senderKey->getBytes(),
            $receiverKey->getPublicKey()->getBytes(),
            $ephPublic
        );

        // Calculate a symmetric MAC
        $mac = sodium_crypto_generichash($message, $k);

        // Verify in constant-time:
        return hash_equals(
            $signature->getBytes(),
            $ephPublic . $mac,
        );
    }

    protected function kdf(
        string $ikm1,
        string $ikm2,
        string $sendPublicKey,
        string $recvPublicKey,
        string $ephemeralPublicKey
    ): string {
        $state = sodium_crypto_generichash_init();
        sodium_crypto_generichash_update($state, 'X3MAC-v1-KDF');
        sodium_crypto_generichash_update($state, $ikm1);
        sodium_crypto_generichash_update($state, $ikm2);
        sodium_crypto_generichash_update($state, $sendPublicKey);
        sodium_crypto_generichash_update($state, $recvPublicKey);
        sodium_crypto_generichash_update($state, $ephemeralPublicKey);
        return sodium_crypto_generichash_final($state);
    }
}
