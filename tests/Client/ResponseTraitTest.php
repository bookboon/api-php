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

    public function testParseCurlAuthenticationWithoutDetailError() : void
    {
        $mock = $this->getMockForTrait(ResponseTrait::class);

        $headers = "HTTP/1.1 200 OK\n Content-Type: application/json; charset=utf-8\nServer: Microsoft-IIS/8.0\nX-Varnish: 444";
        try {
            Helpers::invokeMethod(
                $mock,
                'handleErrorResponse',
                ['{"errors": [{"title": "Custom Forbidden"}]}', [$headers], 403, 'http://bookboon.com/api/categories']
            );
        } catch (ApiAuthenticationException $e) {
            self::assertEquals('Custom Forbidden', $e->getMessage());
        }
    }

    public function testParseCurlAuthenticationWithDetailError() : void
    {
        $mock = $this->getMockForTrait(ResponseTrait::class);

        $headers = "HTTP/1.1 200 OK\n Content-Type: application/json; charset=utf-8\nServer: Microsoft-IIS/8.0\nX-Varnish: 444";
        try {
            Helpers::invokeMethod(
                $mock,
                'handleErrorResponse',
                ['{"errors": [{"title": "Custom Forbidden", "detail": "Other relevant information"}]}', [$headers], 403, 'http://bookboon.com/api/categories']
            );
        } catch (ApiAuthenticationException $e) {
            self::assertEquals('Custom Forbidden: Other relevant information', $e->getMessage());
        }
    }
}