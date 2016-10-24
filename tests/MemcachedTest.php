<?php

use Bookboon\Api\Memcached;

class MemcachedTest extends PHPUnit_Framework_TestCase
{
    public function testSuccessGet()
    {
        $cache = new Memcached();
        $cache->save('test', 'testingValue');
        $value = $cache->get('test');
        $this->assertEquals('testingValue', $value);
    }

    public function testUnsuccessGet()
    {
        $cache = new Memcached();
        $value = $cache->get('test2');
        $this->assertFalse($value);
    }

    public function testRemove()
    {
        $cache = new Memcached();
        $cache->save('test3', 'testingValue');
        $cache->delete('test3');
        $value = $cache->get('test3');
        $this->assertFalse($value);
    }
}
