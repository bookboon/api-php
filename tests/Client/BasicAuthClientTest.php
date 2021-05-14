<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Exception\ApiAuthenticationException;
use Bookboon\Api\Exception\UsageException;
use PHPUnit\Framework\TestCase;
use Helpers\Helpers;

/**
 * Class BasicAuthClientTest
 * @package Bookboon\Api\Client
 * @group basicauth
 */
class BasicAuthClientTest extends TestCase
{
    private static $client;

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();
        self::$client = new BasicAuthClient(
            Helpers::getApiId(),
            Helpers::getApiSecret(),
            new Headers(),
            null
        );
    }

    public function testMissingAuth() : void
    {
        $this->expectException(UsageException::class);
        $client = new BasicAuthClient("", "", new Headers(), null);
    }

    public function testBadAuthentication() : void
    {
        $this->expectException(ApiAuthenticationException::class);
        $client = new BasicAuthClient("bad", "auth", new Headers(), null);
        $bookboon = new Bookboon($client);

        $bookboon->rawRequest('/v1/categories');
    }

    public function testNonExistingHeader() : void
    {
        $headers = "HTTP/1.1 200 OK\n Content-Type: application/json; charset=utf-8\nServer: Microsoft-IIS/8.0";
        $result = Helpers::invokeMethod(self::$client, 'getResponseHeader', [[$headers], 'Location']);

        self::assertEmpty($result);
    }

    public function testValidHeader() : void
    {
        $headers = "HTTP/1.1 200 OK\n Content-Type: application/json; charset=utf-8\nServer: Microsoft-IIS/8.0\nLocation: http://bookboon.com";
        $headerArray = Helpers::invokeMethod(self::$client, 'decodeHeaders', [$headers]);
        $result = $headerArray['Location'];

        self::assertEquals('http://bookboon.com', $result);
    }

    public function providerNotSupported() : array
    {
        return [
            ['setAct'],
            ['getAct'],
            ['getAccessToken'],
            ['isCorrectState'],
            ['generateState'],
            ['getAuthorizationUrl'],
            ['requestAccessToken']
        ];
    }

    /**
     * @dataProvider providerNotSupported
     */
    public function testNotSupportedMethods($method) : void
    {
        $this->expectException(UsageException::class);
        $param = ['a'];
        if (in_array($method, ['setAct', 'isCorrectState'])) {
            $param = 'a';
        }
        self::$client->$method($param, 'b');
    }
}
