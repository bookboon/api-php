<?php

namespace Bookboon\Api\Client;


/**
 * Class RequestTraitTest
 * @package Bookboon\Api\Client
 * @group client
 * @group request
 */
class RequestTraitTest extends \PHPUnit_Framework_TestCase
{
    private $returnValues = array();

    public function callingBack() {
        $this->returnValues = func_get_args();
    }

    public function testPlainGet()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\RequestTrait');

        $mock->method('executeQuery')
             ->will($this->returnCallback(array($this, 'callingBack')));

        $mock->makeRequest('/plain_get');

        $this->assertEquals(Client::API_URL . '/plain_get', $this->returnValues[0]);
    }

    public function testGetWithQueryString()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\RequestTrait');

        $mock->method('executeQuery')
            ->will($this->returnCallback(array($this, 'callingBack')));

        $mock->makeRequest('/get_query_string', array("query2" => "test1"));

        $this->assertEquals(Client::API_URL . '/get_query_string?query2=test1', $this->returnValues[0]);
    }

    public function testPlainPost()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\RequestTrait');

        $mock->method('executeQuery')
            ->will($this->returnCallback(array($this, 'callingBack')));

        $mock->makeRequest('/plain_post', array(), Client::HTTP_POST);

        $this->assertEquals(Client::API_URL . '/plain_post', $this->returnValues[0]);
        $this->assertEquals(Client::HTTP_POST, $this->returnValues[1]);
    }

    public function testPlainPostWithValues()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\RequestTrait');

        $mock->method('executeQuery')
            ->will($this->returnCallback(array($this, 'callingBack')));

        $mock->makeRequest('/post_with_values', array("postval1" => "ptest1"), Client::HTTP_POST);

        $this->assertEquals(array("postval1" => "ptest1"), $this->returnValues[2]);
    }

    private function getCacheMock()
    {
        return $this->getMock('\Bookboon\Api\Cache\Cache');
    }

    public function testMakeRequestNotCached()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\RequestTrait');

        $cacheMock = $this->getCacheMock();
        $cacheMock->method("get")->willReturn(false);
        $cacheMock->method("isCachable")->willReturn(true);

        $mock->method('getCache')
            ->willReturn($cacheMock);

        $mock->method('getHeaders')->willReturn(new Headers());
        $mock->method('executeQuery')
            ->will($this->returnCallback(array($this, 'callingBack')));

        $result = $mock->makeRequest('/plain_get');

        $this->assertNull($result);
        $this->assertEquals(Client::API_URL . '/plain_get', $this->returnValues[0]);
    }

    public function testMakeRequestCached()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\RequestTrait');

        $cacheMock = $this->getCacheMock();
        $cacheMock->method("get")->willReturn("cached return");
        $cacheMock->method("isCachable")->willReturn(true);

        $mock->method('getCache')->willReturn($cacheMock);
        $mock->method('getHeaders')->willReturn(new Headers());

        $result = $mock->makeRequest('/plain_get');

        $this->assertEquals("cached return", $result);
    }


    public function testGetCacheNotFound()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\RequestTrait');

        $cacheMock = $this->getCacheMock();
        $cacheMock->method("get")->willReturn(false);
        $cacheMock->method("isCachable")->willReturn(true);

        $mock->method('getCache')->willReturn($cacheMock);
        $mock->method('getHeaders')->willReturn(new Headers());

        $result = \Helpers::invokeMethod($mock, 'getFromCache', array('/random/' . uniqid('cache', true)));
        $this->assertFalse($result);
    }

    public function testGetCacheFound()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\RequestTrait');

        $cacheMock = $this->getCacheMock();
        $cacheMock->method("get")->willReturn("test");
        $cacheMock->method("isCachable")->willReturn(true);

        $mock->method('getCache')->willReturn($cacheMock);
        $mock->method('getHeaders')->willReturn(new Headers());

        $result = \Helpers::invokeMethod($mock, 'getFromCache', array('/random/' . uniqid('cache', true)));
        $this->assertEquals("test", $result);
    }
}

