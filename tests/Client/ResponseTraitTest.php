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
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method
     *
     * @return mixed Method return
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiSyntaxException
     */
    public function testParseCurlSyntaxError()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\ResponseTrait');
        $this->invokeMethod($mock, 'handleResponse', array('', '', 400, 'http://bookboon.com/api/categories'));
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiAuthenticationException
     */
    public function testParseCurlAuthenticationError()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\ResponseTrait');
        $this->invokeMethod($mock, 'handleResponse', array('', '', 403, 'http://bookboon.com/api/categories'));
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiNotFoundException
     */
    public function testParseCurlNotFoundError()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\ResponseTrait');
        $this->invokeMethod($mock, 'handleResponse', array('', '', 404, 'http://bookboon.com/api/categories'));
    }

    public function testParseCurlRedirect()
    {
        $expectedUrl = 'http://yes.we.can';
        $headers = "HTTP/1.1 200 OK\n Content-Type: application/json; charset=utf-8\nServer: Microsoft-IIS/8.0\nLocation: $expectedUrl";

        $mock = $this->getMockForTrait('\Bookboon\Api\Client\ResponseTrait');

        $mock->method('getResponseHeader')
             ->will($this->returnValue($expectedUrl));

        $result = $this->invokeMethod($mock, 'handleResponse', array('', $headers, 302, 'http://bookboon.com/api/books/xx/download'));
        $this->assertEquals(array('url' => $expectedUrl), $result);
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiGeneralException
     */
    public function testParseCurlServerError()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\ResponseTrait');

        $headers = "HTTP/1.1 200 OK\n Content-Type: application/json; charset=utf-8\nServer: Microsoft-IIS/8.0\nX-Varnish: 444";
        $this->invokeMethod($mock, 'handleResponse', array('', $headers, 500, 'http://bookboon.com/api/categories'));
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiGeneralException
     */
    public function testParseCurlUnknownError()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\ResponseTrait');

        $headers = "HTTP/1.1 200 OK\n Content-Type: application/json; charset=utf-8\nServer: Microsoft-IIS/8.0\nX-Varnish: 444";
        $this->invokeMethod($mock, 'handleResponse', array('', $headers, 0, 'http://bookboon.com/api/categories'));
    }
}