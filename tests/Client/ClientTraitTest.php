<?php

namespace Bookboon\Api\Client;


/**
 * Class ClientTraitTest
 * @package Client
 * @group client
 */
class ClientTraitTest extends \PHPUnit_Framework_TestCase
{
    private static $mock;

    public function providerSetterGetter()
    {
        return [
            ["setApiId", "getApiId", "test value"],
            ["setApiSecret", "getApiSecret", "test secret"],
            ["setHeaders", "getHeaders", new Headers()],
            ["setCache", "getCache", $this->getMock('\Bookboon\Api\Cache\Cache')]
        ];
    }

    /**
     * @dataProvider providerSetterGetter
     * @param $setMethod
     * @param $getMethod
     * @group test
     */
    public function testSetterGetter($setMethod, $getMethod, $testValue)
    {
        $mock = $this->getMockForTrait('\Bookboon\Api\Client\ClientTrait');
        $mock->$setMethod($testValue);
        $this->assertEquals($testValue, $mock->$getMethod());
    }
}