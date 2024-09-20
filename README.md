# X3MAC

X3MAC is a symmetric-key MAC that only the sender and receiver of a message can verify.
It's sort of like the inverse of a signature algorithm.

X3MAC was inspired by the [X3DH](https://signal.org/docs/specifications/x3dh/) key agreement protocol.

# Warning

**This is an experimental design.**

Do NOT use this in production.

## Usage

This is where you'd expect to have some usage instructions, but this is a proof-of-concept,
not something anyone should use in production.

## How it works

Alice wants to sign a message to Bob, such that only Alice and Bob can verify its contents. To do this,
Alice performs the `Sign` algorithm and Bob performs the `Verify` algorithm.

### Sign

Inputs:

1. Alice's Keypair (`A_sk`, `A_pk`)
2. Bob's Public Key (`B_pk`)
3. Message (`m`)

Steps:

1. Generate an ephemeral keypair (`E_sk`, `E_pk`).
2. Calculate the first shared secret: `ikm1 := ECDH(E_sk, B_pk)`.
   * Note this is between the one-time keypair and Bob's long-lived keypair.
3. Calculate the second shared secret: `ikm2 := ECDH(A_sk, B_pk)`.
   * Note that this is between Alice and Bob.
4. Derive a symmetric auth key, as `k := H(ikm1 || ikm2 || A_pk || B_pk || E_pk)`
5. Derive an authenticator, `t := MAC(k, m)`

Outputs:

1. Ephemeral public key (`E_pk`)
2. Authenticator (`t`)

### Verify

Inputs:

1. Bob's Keypair (`B_sk`, `B_pk`)
2. Alice's Public Key (`A_pk`)
3. Ephemeral public key (`E_pk`)
4. Message (`m`)
5. Authenticator (`t`)

Steps:

1. Calculate the first shared secret: `ikm1 := ECDH(B_sk, E_pk)`.
    * Note this is between the one-time keypair and Bob's long-lived keypair.
2. Calculate the second shared secret: `ikm2 := ECDH(B_sk, A_pk)`.
    * Note that this is between Alice and Bob.
3. Derive a symmetric auth key, as `k := H(ikm1 || ikm2 || A_pk || B_pk || E_pk)`
4. Re-derive the authenticator, `t2 := MAC(k, m)`
5. Compare `t2` with `t`, in constant-time.

# Frequently Asked Questions (FAQ)

## Why????

Neil Madden wrote a blog post about [avoiding signatures](https://neilmadden.blog/2024/09/18/digital-signatures-and-how-to-avoid-them/).

It's all Neil's fault!

## What guarantees does this actually provide?

If you actually use this, I can guarantee that your favorite cryptographer will give you a puzzled look.

Both Alice and Bob can calculate the MAC, which is necessary to verify it. With a signature, you'd expect
only Alice to be able to generate new signatures.

## Why not make an actual signature?

That would require writing a variant of [Schnorr's identification protocol](https://www.zkdocs.com/docs/zkdocs/zero-knowledge-protocols/schnorr/)
which would in turn require me to operate over [the Ristretto group](https://ristretto.group/) (due to how libsodium's
API is written).

Slapping some ECDH into a KDF then using BLAKE2's MAC mode is one thing, but if I actually wrote a full-blown siganture
algorithm (henceforth X3SIG), it would carry the risk of someone wanting to actually use it.

## Is this really insecure?

Not in any obvious way, no.

Still, I don't have a machine-verifiable security proof or an academic paper to point you to. Nor has it been audited
by a third party.

Also, I wrote it in PHP.

## Is the X3 thing because of furry speak?

[rawr](https://github.com/soatok/rawr-x3dh)
