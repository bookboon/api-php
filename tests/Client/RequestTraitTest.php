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
    private $returnValues = [];

    public function callingBack() {
        $this->returnValues = func_get_args();
        return new BookboonResponse([], []);
    }

    public function testPlainGet()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\RequestTrait');
        $mock->method("getBaseApiUri")->willReturn(ClientInterface::API_HOST . ClientInterface::API_PATH);

        $mock->method('executeQuery')
             ->will($this->returnCallback([$this, 'callingBack']));
        $mock->method('getApiId')->willReturn("test-app-id");

        $mock->makeRequest('/plain_get');

        $this->assertEquals(ClientInterface::API_HOST . ClientInterface::API_PATH . '/plain_get', $this->returnValues[0]);
    }

    public function testGetWithQueryString()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\RequestTrait');
        $mock->method("getBaseApiUri")->willReturn(ClientInterface::API_HOST . ClientInterface::API_PATH);

        $mock->method('executeQuery')
            ->will($this->returnCallback([$this, 'callingBack']));
        $mock->method('getApiId')->willReturn("test-app-id");

        $mock->makeRequest('/get_query_string', ["query2" => "test1"]);

        $this->assertEquals(ClientInterface::API_HOST . ClientInterface::API_PATH . '/get_query_string?query2=test1', $this->returnValues[0]);
    }

    public function testPlainPost()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\RequestTrait');
        $mock->method("getBaseApiUri")->willReturn(ClientInterface::API_HOST . ClientInterface::API_PATH);

        $mock->method('executeQuery')
            ->will($this->returnCallback([$this, 'callingBack']));
        $mock->method('getApiId')->willReturn("test-app-id");

        $mock->makeRequest('/plain_post', [], ClientInterface::HTTP_POST);

        $this->assertEquals(ClientInterface::API_HOST . ClientInterface::API_PATH . '/plain_post', $this->returnValues[0]);
        $this->assertEquals(ClientInterface::HTTP_POST, $this->returnValues[1]);
    }

    public function testPlainPostWithValues()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\RequestTrait');
        $mock->method('getApiId')->willReturn("test-app-id");
        $mock->method("getBaseApiUri")->willReturn(ClientInterface::API_HOST . ClientInterface::API_PATH);

        $mock->method('executeQuery')
            ->will($this->returnCallback([$this, 'callingBack']));

        $mock->makeRequest('/post_with_values', ["postval1" => "ptest1"], ClientInterface::HTTP_POST);

        $this->assertEquals(["postval1" => "ptest1"], $this->returnValues[2]);
    }

    private function getCacheMock()
    {
        return $this->getMockBuilder('\Bookboon\Api\Cache\CacheInterface')->getMock();
    }

    public function testMakeRequestNotCached()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\RequestTrait');
        $mock->method("getBaseApiUri")->willReturn(ClientInterface::API_HOST . ClientInterface::API_PATH);

        $cacheMock = $this->getCacheMock();
        $cacheMock->method("get")->willReturn(false);
        $cacheMock->method("isCachable")->willReturn(true);

        $mock->method('getCache')
            ->willReturn($cacheMock);

        $mock->method('getHeaders')->willReturn(new Headers());
        $mock->method('executeQuery')
            ->will($this->returnCallback([$this, 'callingBack']));
        $mock->method('getApiId')->willReturn("test-app-id");

        $result = $mock->makeRequest('/plain_get');

        $this->assertEquals(new BookboonResponse([], []), $result);
        $this->assertEquals(ClientInterface::API_HOST . ClientInterface::API_PATH . '/plain_get', $this->returnValues[0]);
    }

    public function testMakeRequestCached()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\RequestTrait');

        $cacheMock = $this->getCacheMock();
        $cacheMock->method("get")->willReturn(new BookboonResponse(['test'], []));
        $cacheMock->method("isCachable")->willReturn(true);

        $mock->method('getCache')->willReturn($cacheMock);
        $mock->method('getHeaders')->willReturn(new Headers());
        $mock->method('getApiId')->willReturn("test-app-id");

        $result = $mock->makeRequest('/plain_get');

        $this->assertEquals(new BookboonResponse(['test'], []), $result);
    }


    public function testGetCacheNotFound()
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\RequestTrait');

        $cacheMock = $this->getCacheMock();
        $cacheMock->method("get")->willReturn(false);
        $cacheMock->method("isCachable")->willReturn(true);

        $mock->method('getCache')->willReturn($cacheMock);
        $mock->method('getHeaders')->willReturn(new Headers());
        $mock->method('getApiId')->willReturn("test-app-id");

        $result = \Helpers::invokeMethod($mock, 'getFromCache', ['/random/' . uniqid('cache', true)]);
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
        $mock->method('getApiId')->willReturn("test-app-id");

        $result = \Helpers::invokeMethod($mock, 'getFromCache', ['/random/' . uniqid('cache', true)]);
        $this->assertEquals("test", $result);
    }
}

