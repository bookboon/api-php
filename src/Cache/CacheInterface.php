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

use Bookboon\Api\Client\BookboonResponse;

interface CacheInterface
{
    /**
     * Get data from cache
     *
     * @param string $key
     * @return mixed if not found is false
     */
    public function get(string $key);

    /**
     * Save in cache
     *
     * @param string $key
     * @param mixed $data
     * @param int|null $ttl
     * @return bool if successful
     */
    public function save(string $key, $data, ?int $ttl = null) : bool;

    /**
     * Delete from cache by key
     *
     * @param string $key
     * @return bool if successful
     */
    public function delete(string $key): bool;

    /**
     * @param string $url
     * @param string $httpMethod
     * @return bool
     */
    public function isCachable(string $url, string $httpMethod) : bool;

    /**
     * @param string $url
     * @param string $id
     * @param array $headers
     * @return string
     */
    public function hash(string $url, string $id, array $headers) : string;

    /**
     * @return bool
     */
    public function isInitialized() : bool;
}
