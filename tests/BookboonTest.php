<?php

namespace Bookboon\Api;

use PHPUnit\Framework\TestCase;

include_once(__DIR__ . '/Helpers.php');

/**
 * Class BookboonTest
 * @package Bookboon\Api
 * @group main
 */
class BookboonTest extends TestCase
{
    /** @var Bookboon */
    private static $bookboon;

    public static function setUpBeforeClass()
    {
        self::$bookboon = \Helpers::getBookboon();
    }
    /**
     * @expectedException \Bookboon\Api\Exception\UsageException
     */
    public function testBadUrl()
    {
        self::$bookboon->rawRequest('bah');
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiSyntaxException
     */
    public function testBadRequest()
    {
        self::$bookboon->rawRequest('/search', ['get' => ['q' => '']]);
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiNotFoundException
     */
    public function testNotFound()
    {
        self::$bookboon->rawRequest('/bah');
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiAuthenticationException
     */
    public function testBadAuthentication()
    {
        $bookboon = Bookboon::create("bad", "auth", ['basic']);
        $bookboon->rawRequest('/categories/062adfac-844b-4e8c-9242-a1620108325e');
    }

    /**
     * @expectedException \Bookboon\Api\Exception\UsageException
     */
    public function testEmpty()
    {
        $bookboon = Bookboon::create("", "", ['basic']);
        $bookboon->rawRequest('/categories/062adfac-844b-4e8c-9242-a1620108325e');
    }

    public function testGetClient()
    {
        $bookboon = Bookboon::create("bad", "auth", ['basic']);
        $this->assertInstanceOf('\Bookboon\Api\Client\ClientInterface', $bookboon->getClient());
    }

    public function testCreateHeaders()
    {
        $bookboon = Bookboon::create("bad", "auth", ['basic'], ["X-Test-Header" => "Test Value"]);
        $result = $bookboon->getClient()->getHeaders()->get("X-Test-Header");

        $this->assertEquals("Test Value", $result);
    }
}
