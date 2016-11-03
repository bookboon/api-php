<?php

namespace Bookboon\Api\Cache;

/*
 *  Copyright 2012 Bookboon.com Ltd.
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 */


if (!class_exists('Memcached')) {
    throw new \Exception('Bookboon_Memcached requires the memcached PHP extension');
}

class MemcachedCache implements Cache
{
    use HashTrait;

    private $ttl = 600;
    private $cache = null;

    /**
     * Memcached constructor.
     *
     * @param string $server IP or hostname
     * @param int    $port
     * @param int    $ttl    Time to live
     */
    public function __construct($server = 'localhost', $port = 11211, $ttl = 600)
    {
        $this->cache = new \Memcached();
        $this->ttl = $ttl;

        $this->cache->addServer($server, $port);
    }

    /**
     * Get a cached object.
     *
     * @param $key
     *
     * @return mixed False is not found
     */
    public function get($key)
    {
        return $this->cache->get($key);
    }

    /**
     * Save an object.
     *
     * @param $key
     * @param $data
     *
     * @return bool
     */
    public function save($key, $data)
    {
        return $this->cache->set($key, $data, $this->ttl);
    }

    /**
     * Delete a cached object.
     *
     * @param $key
     *
     * @return bool if successful true
     */
    public function delete($key)
    {
        return $this->cache->delete($key);
    }

    /**
     * @return bool
     */
    public function isInitialized()
    {
        return $this->cache == null;
    }
}
