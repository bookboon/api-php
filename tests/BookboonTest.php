<?php

namespace Bookboon\Api;

include_once(__DIR__ . '/Helpers.php');

/**
 * Class BookboonTest
 * @package Bookboon\Api
 * @group main
 */
class BookboonTest extends \PHPUnit_Framework_TestCase
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
        self::$bookboon->rawRequest('/search', array('get' => array('q' => '')));
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
        $bookboon = Bookboon::create("bad", "auth", array('basic'));
        $bookboon->rawRequest('/categories/062adfac-844b-4e8c-9242-a1620108325e');
    }

    /**
     * @expectedException \Bookboon\Api\Exception\UsageException
     */
    public function testEmpty()
    {
        $bookboon = Bookboon::create("", "", array('basic'));
        $bookboon->rawRequest('/categories/062adfac-844b-4e8c-9242-a1620108325e');
    }

    public function testGetClient()
    {
        $bookboon = Bookboon::create("bad", "auth", array('basic'));
        $this->assertInstanceOf('\Bookboon\Api\Client\Client', $bookboon->getClient());
    }

    public function testCreateHeaders()
    {
        $bookboon = Bookboon::create("bad", "auth", array('basic'), array("X-Test-Header" => "Test Value"));
        $result = $bookboon->getClient()->getHeaders()->get("X-Test-Header");

        $this->assertEquals("Test Value", $result);
    }
}
