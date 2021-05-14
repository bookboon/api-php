<?php

namespace Bookboon\Api\Client;

use PHPUnit\Framework\TestCase;

/**
 * Class HeadersTest
 * @package Client
 * @group client
 * @group header
 */
class HeadersTest extends TestCase
{
    public function testInvalidXFFIP() : void
    {
        $_SERVER['REMOTE_ADDR'] = '127.';
        $headers = new Headers();
        self::assertEmpty($headers->get(Headers::HEADER_XFF));
    }

    public function testValidXFFIP() : void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $headers = new Headers();
        self::assertEquals('127.0.0.1', $headers->get(Headers::HEADER_XFF));
    }

    public function testOverrideXFF() : void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $headers = new Headers();
        $headers->set(Headers::HEADER_XFF, 'TEST');
        self::assertEquals('TEST', $headers->get(Headers::HEADER_XFF));
    }
}