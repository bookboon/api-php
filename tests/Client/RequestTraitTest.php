<?php
/**
 * Bookboon Api
 *
 * Creator: Lasse Mammen <lkm@bookboon.com>
 * Date: 03/11/2016
 */

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

    public function testGetCacheNotFound()
    {
        $this->markTestIncomplete();
    }

    public function testGetCacheFound()
    {
        $this->markTestIncomplete();
    }
}

