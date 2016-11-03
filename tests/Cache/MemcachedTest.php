<?php

use Bookboon\Api\Cache\MemcachedCache;

class MemcachedTest extends PHPUnit_Framework_TestCase
{
    public function testSuccessGet()
    {
        $cache = new MemcachedCache();
        $cache->save('test', 'testingValue');
        $value = $cache->get('test');
        $this->assertEquals('testingValue', $value);
    }

    public function testUnsuccessGet()
    {
        $cache = new MemcachedCache();
        $value = $cache->get('test2');
        $this->assertFalse($value);
    }

    public function testRemove()
    {
        $cache = new MemcachedCache();
        $cache->save('test3', 'testingValue');
        $cache->delete('test3');
        $value = $cache->get('test3');
        $this->assertFalse($value);
    }
}
