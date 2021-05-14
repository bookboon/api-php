<?php

namespace Bookboon\Api\Client;


use Helpers\Helpers;
use Psr\SimpleCache\CacheInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestTraitTest
 * @package Bookboon\Api\Client
 * @group client
 * @group request
 */
class RequestTraitTest extends TestCase
{
    private $returnValues = [];

    public function callingBack() {
        $this->returnValues = func_get_args();
        return new BookboonResponse('test', 200, []);
    }

    public function testPlainGet() : void
    {
        $mock = $this->getMockForTrait(RequestTrait::class);
        $mock->method("getBaseApiUri")->willReturn(ClientInterface::API_HOST . ClientInterface::API_PATH);

        $mock->method('executeQuery')
             ->will($this->returnCallback([$this, 'callingBack']));
        $mock->method('getApiId')->willReturn("test-app-id");

        $mock->makeRequest('/plain_get');

        self::assertEquals(ClientInterface::API_HOST . ClientInterface::API_PATH . '/plain_get', $this->returnValues[0]);
    }

    public function testGetWithQueryString() : void
    {
        $mock = $this->getMockForTrait(RequestTrait::class);
        $mock->method("getBaseApiUri")->willReturn(ClientInterface::API_HOST . ClientInterface::API_PATH);

        $mock->method('executeQuery')
            ->will($this->returnCallback([$this, 'callingBack']));
        $mock->method('getApiId')->willReturn("test-app-id");

        $mock->makeRequest('/get_query_string', ["query2" => "test1"]);

        self::assertEquals(ClientInterface::API_HOST . ClientInterface::API_PATH . '/get_query_string?query2=test1', $this->returnValues[0]);
    }

    public function testPlainPost() : void
    {
        $mock = $this->getMockForTrait(RequestTrait::class);
        $mock->method("getBaseApiUri")->willReturn(ClientInterface::API_HOST . ClientInterface::API_PATH);

        $mock->method('executeQuery')
            ->will($this->returnCallback([$this, 'callingBack']));
        $mock->method('getApiId')->willReturn("test-app-id");

        $mock->makeRequest('/plain_post', [], ClientInterface::HTTP_POST);

        self::assertEquals(ClientInterface::API_HOST . ClientInterface::API_PATH . '/plain_post', $this->returnValues[0]);
        self::assertEquals(ClientInterface::HTTP_POST, $this->returnValues[1]);
    }

    public function testPlainPostWithValues() : void
    {
        $mock = $this->getMockForTrait(RequestTrait::class);
        $mock->method('getApiId')->willReturn("test-app-id");
        $mock->method("getBaseApiUri")->willReturn(ClientInterface::API_HOST . ClientInterface::API_PATH);

        $mock->method('executeQuery')
            ->will($this->returnCallback([$this, 'callingBack']));

        $mock->makeRequest('/post_with_values', ["postval1" => "ptest1"], ClientInterface::HTTP_POST);

        self::assertEquals(["postval1" => "ptest1"], $this->returnValues[2]);
    }

    private function getCacheMock()
    {
        return $this->getMockBuilder(CacheInterface::class)->getMock();
    }

    public function testMakeRequestNotCached() : void
    {
        $mock = $this->getMockForTrait(RequestTrait::class);
        $mock->method("getBaseApiUri")->willReturn(ClientInterface::API_HOST . ClientInterface::API_PATH);
        $mock->method("isCachable")->willReturn(false);

        $cacheMock = $this->getCacheMock();
        $cacheMock->method("get")->willReturn(false);

        $mock->method('getCache')
            ->willReturn($cacheMock);

        $mock->method('getHeaders')->willReturn(new Headers());
        $mock->method('executeQuery')
            ->will($this->returnCallback([$this, 'callingBack']));
        $mock->method('getApiId')->willReturn("test-app-id");

        $result = $mock->makeRequest('/plain_get');

        self::assertEquals(new BookboonResponse('test', 200, []), $result);
        self::assertEquals(ClientInterface::API_HOST . ClientInterface::API_PATH . '/plain_get', $this->returnValues[0]);
    }

    /**
     * @group cached
     */
    public function testMakeRequestCached() : void
    {
        $mock = $this->getMockForTrait(RequestTrait::class);

        $cacheMock = $this->getCacheMock();
        $cacheMock->method("get")->willReturn(new BookboonResponse('["test"]', 200, []));

        $mock->method("isCachable")->willReturn(true);
        $mock->method('getCache')->willReturn($cacheMock);
        $mock->method('getHeaders')->willReturn(new Headers());
        $mock->method('getApiId')->willReturn("test-app-id");

        $result = $mock->makeRequest('/plain_get');

        self::assertEquals(new BookboonResponse('["test"]', 200, []), $result);
    }


    public function testGetCacheNotFound() : void
    {
        $mock = $this->getMockForTrait(RequestTrait::class);

        $cacheMock = $this->getCacheMock();
        $cacheMock->method("get")->willReturn(false);

        $mock->method("isCachable")->willReturn(true);
        $mock->method('getCache')->willReturn($cacheMock);
        $mock->method('getHeaders')->willReturn(new Headers());
        $mock->method('getApiId')->willReturn("test-app-id");

        $result = Helpers::invokeMethod(
            $mock,
            'getFromCache',
            ['/random/' . uniqid('cache', true)]
        );
        self::assertNull($result);
    }

    public function testGetCacheFound() : void
    {
        $mock = $this->getMockForTrait(RequestTrait::class);

        $cacheMock = $this->getCacheMock();
        $cacheMock->method("get")->willReturn(new BookboonResponse('["test"]', 200, []));

        $mock->method("isCachable")->willReturn(true);
        $mock->method('getCache')->willReturn($cacheMock);
        $mock->method('getHeaders')->willReturn(new Headers());
        $mock->method('getApiId')->willReturn("test-app-id");

        $result = Helpers::invokeMethod(
            $mock,
            'getFromCache',
            ['/random/' . uniqid('cache', true)]
        );
        self::assertEquals(new BookboonResponse('["test"]', 200, []), $result);
    }
}

