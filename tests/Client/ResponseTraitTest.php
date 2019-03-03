<?php

namespace Bookboon\Api\Client;

use PHPUnit\Framework\TestCase;

/**
 * Class ClientCommonTest
 * @package Client
 * @group client
 */
class ResponseTraitTest extends TestCase
{
    /**
     * @expectedException \Bookboon\Api\Exception\ApiSyntaxException
     */
    public function testParseCurlSyntaxError()
    {
        $mock = $this->getMockForTrait(ResponseTrait::class);
        \Helpers::invokeMethod($mock, 'handleErrorResponse', ['', [], 400, 'http://bookboon.com/api/categories']);
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiAuthenticationException
     */
    public function testParseCurlAuthenticationError()
    {
        $mock = $this->getMockForTrait(ResponseTrait::class);
        \Helpers::invokeMethod($mock, 'handleErrorResponse', ['', [], 403, 'http://bookboon.com/api/categories']);
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiNotFoundException
     */
    public function testParseCurlNotFoundError()
    {
        $mock = $this->getMockForTrait(ResponseTrait::class);
        \Helpers::invokeMethod($mock, 'handleErrorResponse', ['', [], 404, 'http://bookboon.com/api/categories']);
    }

//    public function testParseCurlRedirect()
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
//        $this->assertEquals(['url' => $expectedUrl], $result);
//    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiGeneralException
     */
    public function testParseCurlServerError()
    {
        $mock = $this->getMockForTrait(ResponseTrait::class);

        $headers = "HTTP/1.1 200 OK\n Content-Type: application/json; charset=utf-8\nServer: Microsoft-IIS/8.0\nX-Varnish: 444";
        \Helpers::invokeMethod($mock, 'handleErrorResponse', ['', [$headers], 500, 'http://bookboon.com/api/categories']);
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiGeneralException
     */
    public function testParseCurlUnknownError()
    {
        $mock = $this->getMockForTrait(ResponseTrait::class);

        $headers = "HTTP/1.1 200 OK\n Content-Type: application/json; charset=utf-8\nServer: Microsoft-IIS/8.0\nX-Varnish: 444";
        \Helpers::invokeMethod($mock, 'handleErrorResponse', ['', [$headers], 0, 'http://bookboon.com/api/categories']);
    }
}