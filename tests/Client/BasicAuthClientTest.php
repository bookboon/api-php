<?php

namespace Bookboon\Api\Client;


use Bookboon\Api\Bookboon;

/**
 * Class BasicAuthClientTest
 * @package Bookboon\Api\Client
 * @group basicauth
 */
class BasicAuthClientTest extends \PHPUnit_Framework_TestCase
{
    private static $client;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$client = new BasicAuthClient(\Helpers::getApiId(), \Helpers::getApiSecret(), new Headers(), null);
    }

    /**
     * @expectedException \Bookboon\Api\Exception\UsageException
     */
    public function testMissingAuth()
    {
        $client = new BasicAuthClient("", "", new Headers(), null);
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiAuthenticationException
     */
    public function testBadAuthentication()
    {
        $client = new BasicAuthClient("bad", "auth", new Headers(), null);
        $bookboon = new Bookboon($client);

        $bookboon->rawRequest('/categories');
    }

    public function testNonExistingHeader()
    {
        $headers = "HTTP/1.1 200 OK\n Content-Type: application/json; charset=utf-8\nServer: Microsoft-IIS/8.0";
        $result = \Helpers::invokeMethod(self::$client, 'getResponseHeader', array($headers, 'Location'));

        $this->assertEmpty($result);
    }

    public function testValidHeader()
    {
        $headers = "HTTP/1.1 200 OK\n Content-Type: application/json; charset=utf-8\nServer: Microsoft-IIS/8.0\nLocation: http://bookboon.com";
        $result = \Helpers::invokeMethod(self::$client, 'getResponseHeader', array($headers, 'Location'));

        $this->assertEquals('http://bookboon.com', $result);
    }

    public function providerNotSupported()
    {
        return [
            ["setAct"],
            ["getAct"],
            ["getAccessToken"],
            ["isCorrectState"],
            ["generateState"],
            ["getAuthorizationUrl"],
            ["requestAccessToken"]
        ];
    }

    /**
     * @dataProvider providerNotSupported
     * @expectedException \Bookboon\Api\Exception\UsageException
     */
    public function testNotSupportedMethods($method)
    {
        self::$client->$method(array("a"), "b");
    }
}