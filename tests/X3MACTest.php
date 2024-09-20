<?php
declare(strict_types=1);
namespace Soatok\X3MAC\Tests;

use Soatok\X3MAC\SecretKey;
use Soatok\X3MAC\X3MAC;
use PHPUnit\Framework\TestCase;

/**
 * @covers X3MAC
 */
class X3MACTest extends TestCase
{
    public function testHappyPath(): void
    {
        $x3 = new X3MAC();
        $alice = SecretKey::generate();
        $bob = SecretKey::generate();

        $alicePublic = $alice->getPublicKey();
        $bobPublic = $bob->getPublicKey();

        $message = "This is a test message!";
        $tag = $x3->sign($alice, $bobPublic, $message);

        $this->assertTrue($x3->verify($tag, $alicePublic, $bob, $message));
        $this->assertFalse($x3->verify($tag, $alicePublic, $bob, $message . '!'));
    }
}
