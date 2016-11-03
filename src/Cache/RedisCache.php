<?php

namespace Bookboon\Api\Cache;


use Exception;
use Redis;

class RedisCache extends CacheCommon
{
    /**
     * @var Redis
     */
    protected $redis;

    /**
     * @var int
     */
    protected $ttl;

    public function __construct($server = 'localhost', $port = 6379, $ttl = 600)
    {
        $this->ttl = $ttl;
        $this->redis = new Redis();
        try {
            $this->redis->connect($server, $port);
            $this->redis->setOption(Redis::OPT_SERIALIZER, $this->getSerializerValue());
        } catch (Exception $e) {
            $this->redis = null;
        }
    }

    public function get($key)
    {
        return $this->redis !== null ? $this->redis->get($key) : false;
    }

    public function save($key, $data)
    {
        return $this->redis !== null ? $this->redis->setex($key, $this->ttl, $data) : false;
    }

    public function delete($key)
    {
        // TODO: Implement delete() method.
    }

    protected function getSerializerValue()
    {
        if (defined('HHVM_VERSION')) {
            return \Redis::SERIALIZER_PHP;
        }
        return defined('Redis::SERIALIZER_IGBINARY') ? \Redis::SERIALIZER_IGBINARY : \Redis::SERIALIZER_PHP;
    }

    /**
     * @return bool
     */
    public function isInitialized()
    {
        return $this->redis == null;
    }
}