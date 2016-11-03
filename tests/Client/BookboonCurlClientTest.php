<?php

namespace Bookboon\Api\Client;


class BookboonCurlClientTest
{
    public function testNonExistingHeader()
    {
        $bookboon = new Bookboon('id', 'key');

        $headers = "HTTP/1.1 200 OK\n Content-Type: application/json; charset=utf-8\nServer: Microsoft-IIS/8.0";
        $result = $this->invokeMethod($bookboon, 'getHeaderFromCurl', array($headers, 'Location'));

        $this->assertEmpty($result);
    }

    public function testValidHeader()
    {
        $bookboon = new Bookboon('id', 'key');

        $headers = "HTTP/1.1 200 OK\n Content-Type: application/json; charset=utf-8\nServer: Microsoft-IIS/8.0\nLocation: http://bookboon.com";
        $result = $this->invokeMethod($bookboon, 'getHeaderFromCurl', array($headers, 'Location'));

        $this->assertEquals('http://bookboon.com', $result);
    }
}