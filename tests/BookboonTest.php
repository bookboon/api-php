<?php

namespace Bookboon\Api;

use Bookboon\Api\Exception\ApiAuthenticationException;
use Bookboon\Api\Exception\ApiNotFoundException;
use Bookboon\Api\Exception\ApiSyntaxException;
use Bookboon\Api\Exception\UsageException;
use PHPUnit\Framework\TestCase;
use Helpers\Helpers;

/**
 * Class BookboonTest
 * @package Bookboon\Api
 * @group main
 */
class BookboonTest extends TestCase
{
    /** @var Bookboon */
    private static $bookboon;

    public static function setUpBeforeClass() : void
    {
        self::$bookboon = Helpers::getBookboon();
    }

    public function testBadUrl() : void
    {
        $this->expectException(UsageException::class);
        self::$bookboon->rawRequest('bah');
    }

    public function testBadRequest() : void
    {
        $this->expectException(ApiSyntaxException::class);
        self::$bookboon->rawRequest('/v1/search', ['get' => ['q' => '']]);
    }

    public function testNotFound() : void
    {
        $this->expectException(ApiNotFoundException::class);
        self::$bookboon->rawRequest('/v1/bah');
    }

    public function testBadAuthentication() : void
    {
        $this->expectException(ApiAuthenticationException::class);
        $bookboon = Bookboon::create("bad", "auth", ['basic']);
        $bookboon->rawRequest('/v1/categories/062adfac-844b-4e8c-9242-a1620108325e');
    }

    public function testEmpty() : void
    {
        $this->expectException(UsageException::class);
        $bookboon = Bookboon::create("", "", ['basic']);
        $bookboon->rawRequest('/v1/categories/062adfac-844b-4e8c-9242-a1620108325e');
    }

    public function testGetClient() : void
    {
        $bookboon = Bookboon::create("bad", "auth", ['basic']);
        self::assertInstanceOf('\Bookboon\Api\Client\ClientInterface', $bookboon->getClient());
    }

    public function testCreateHeaders() : void
    {
        $bookboon = Bookboon::create("bad", "auth", ['basic'], ["X-Test-Header" => "Test Value"]);
        $result = $bookboon->getClient()->getHeaders()->get("X-Test-Header");

        self::assertEquals("Test Value", $result);
    }
}
