<?php

namespace Bookboon\Api\Client;

/**
 * Class ClientCommonTest
 * @package Client
 * @group client
 */
class ResponseTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Bookboon\Api\Exception\ApiSyntaxException
     */
    public function testParseCurlSyntaxError()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\ResponseTrait');
        \Helpers::invokeMethod($mock, 'handleResponse', array('', '', 400, 'http://bookboon.com/api/categories'));
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiAuthenticationException
     */
    public function testParseCurlAuthenticationError()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\ResponseTrait');
        \Helpers::invokeMethod($mock, 'handleResponse', array('', '', 403, 'http://bookboon.com/api/categories'));
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiNotFoundException
     */
    public function testParseCurlNotFoundError()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\ResponseTrait');
        \Helpers::invokeMethod($mock, 'handleResponse', array('', '', 404, 'http://bookboon.com/api/categories'));
    }

    public function testParseCurlRedirect()
    {
        $expectedUrl = 'http://yes.we.can';
        $headers = "HTTP/1.1 200 OK\n Content-Type: application/json; charset=utf-8\nServer: Microsoft-IIS/8.0\nLocation: $expectedUrl";

        $mock = $this->getMockForTrait('\Bookboon\Api\Client\ResponseTrait');

        $mock->method('getResponseHeader')
             ->will($this->returnValue($expectedUrl));

        $result = \Helpers::invokeMethod($mock, 'handleResponse', array('', $headers, 302, 'http://bookboon.com/api/books/xx/download'));
        $this->assertEquals(array('url' => $expectedUrl), $result);
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiGeneralException
     */
    public function testParseCurlServerError()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\ResponseTrait');

        $headers = "HTTP/1.1 200 OK\n Content-Type: application/json; charset=utf-8\nServer: Microsoft-IIS/8.0\nX-Varnish: 444";
        \Helpers::invokeMethod($mock, 'handleResponse', array('', $headers, 500, 'http://bookboon.com/api/categories'));
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiGeneralException
     */
    public function testParseCurlUnknownError()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\ResponseTrait');

        $headers = "HTTP/1.1 200 OK\n Content-Type: application/json; charset=utf-8\nServer: Microsoft-IIS/8.0\nX-Varnish: 444";
        \Helpers::invokeMethod($mock, 'handleResponse', array('', $headers, 0, 'http://bookboon.com/api/categories'));
    }
}