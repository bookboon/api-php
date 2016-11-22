<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Cache\RedisCache;

/**
 * Class RedisCacheTest
 * @package Cache
 * @group cache
 */
class RedisCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testInvalidServer()
    {
        $cache = new RedisCache('127.0.0.2', 62, 100, 1);
        $this->assertFalse($cache->isInitialized());
    }

    public function testSuccessGet()
    {
        $cache = new RedisCache();
        $cache->save('test', 'testingValue');
        $value = $cache->get('test');
        $this->assertEquals('testingValue', $value);
    }

    public function testUnsuccessGet()
    {
        $cache = new RedisCache();
        $value = $cache->get('test2');
        $this->assertFalse($value);
    }

    public function testRemove()
    {
        $cache = new RedisCache();
        $cache->save('test3', 'testingValue');
        $cache->delete('test3');
        $value = $cache->get('test3');
        $this->assertFalse($value);
    }
}