<?php

namespace Bookboon\Api;

include_once(__DIR__ . '/Authentication.php');

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
        self::$bookboon = \Authentication::getBookboon();
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
}
