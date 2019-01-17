<?php

namespace Bookboon\Api\Cache;


use Exception;
use Redis;

class RedisCache implements Cache
{
    use HashTrait;

    /**
     * @var Redis
     */
    protected $redis;

    /**
     * @var int
     */
    protected $ttl;

    /**
     * RedisCache constructor.
     * @param string $server
     * @param int $port
     * @param int $ttl
     * @param int $timeout in seconds
     */
    public function __construct($server = 'localhost', $port = 6379, $ttl = 600, $timeout = 2)
    {
        $this->ttl = $ttl;
        $this->redis = new Redis();
        try {
            if ($this->redis->connect($server, $port, $timeout)) {
                $this->redis->setOption(Redis::OPT_SERIALIZER, $this->getSerializerValue());
            } else {
                $this->redis = null;
            }

        } catch (Exception $e) {
            $this->redis = null;
        }
    }

    public function get($key)
    {
        return $this->redis !== null ? $this->redis->get($key) : false;
    }

    public function save($key, $data, ?int $ttl = null)
    {
        $ttl = $ttl ?? $this->ttl;

        return $this->redis !== null ? $this->redis->setex($key, $ttl, $data) : false;
    }

    public function delete($key)
    {
        return $this->redis->delete($key) === 1;
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
        return $this->redis != null;
    }
}