<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Exception\ApiAuthenticationException;
use Bookboon\Api\Exception\ApiGeneralException;
use Bookboon\Api\Exception\ApiNotFoundException;
use Bookboon\Api\Exception\ApiSyntaxException;
use Helpers\Helpers;
use PHPUnit\Framework\TestCase;

/**
 * Class ClientCommonTest
 * @package Client
 * @group client
 */
class ResponseTraitTest extends TestCase
{
    public function testParseCurlSyntaxError() : void
    {
        $this->expectException(ApiSyntaxException::class);
        $mock = $this->getMockForTrait(ResponseTrait::class);
        Helpers::invokeMethod(
            $mock,
            'handleErrorResponse',
            ['', [], 400, 'http://bookboon.com/api/categories']
        );
    }

    public function testParseCurlAuthenticationError() : void
    {
        $this->expectException(ApiAuthenticationException::class);
        $mock = $this->getMockForTrait(ResponseTrait::class);
        Helpers::invokeMethod(
            $mock,
            'handleErrorResponse',
            ['', [], 403, 'http://bookboon.com/api/categories']
        );
    }

    public function testParseCurlNotFoundError() : void
    {
        $this->expectException(ApiNotFoundException::class);
        $mock = $this->getMockForTrait(ResponseTrait::class);
        Helpers::invokeMethod(
            $mock,
            'handleErrorResponse',
            ['', [], 404, 'http://bookboon.com/api/categories']
        );
    }

//    public function testParseCurlRedirect() : void
//    {
//        $expectedUrl = 'http://yes.we.can';
//        $headers = "HTTP/1.1 200 OK\n Content-Type: application/json; charset=utf-8\nServer: Microsoft-IIS/8.0\nLocation: $expectedUrl";
//
//        $mock = $this->getMockForTrait(ResponseTrait::class);
//
//        $mock->method('getResponseHeader')
//             ->willReturn($expectedUrl);
//
//        $result = \Helpers::invokeMethod($mock, 'handleErrorResponse', ['', [$headers], 302, 'http://bookboon.com/api/books/xx/download']);
//        self::assertEquals(['url' => $expectedUrl], $result);
//    }

    public function testParseCurlServerError() : void
    {
        $this->expectException(ApiGeneralException::class);
        $mock = $this->getMockForTrait(ResponseTrait::class);

        $headers = "HTTP/1.1 200 OK\n Content-Type: application/json; charset=utf-8\nServer: Microsoft-IIS/8.0\nX-Varnish: 444";
        Helpers::invokeMethod(
            $mock,
            'handleErrorResponse',
            ['', [$headers], 500, 'http://bookboon.com/api/categories']
        );
    }

    public function testParseCurlUnknownError() : void
    {
        $this->expectException(ApiGeneralException::class);
        $mock = $this->getMockForTrait(ResponseTrait::class);

        $headers = "HTTP/1.1 200 OK\n Content-Type: application/json; charset=utf-8\nServer: Microsoft-IIS/8.0\nX-Varnish: 444";
        Helpers::invokeMethod(
            $mock,
            'handleErrorResponse',
            ['', [$headers], 0, 'http://bookboon.com/api/categories']
        );
    }
}