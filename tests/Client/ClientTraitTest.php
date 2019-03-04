<?php

namespace Bookboon\Api\Client;


use Psr\SimpleCache\CacheInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class ClientTraitTest
 * @package Client
 * @group client
 */
class ClientTraitTest extends TestCase
{
    private static $mock;

    public function providerSetterGetter()
    {
        return [
            ["setHeaders", "getHeaders", new Headers()],
            ["setCache", "getCache", $this->getMockBuilder(CacheInterface::class)->getMock()]
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