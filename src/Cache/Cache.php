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
interface Cache
{
    /**
     * Get data from cache
     *
     * @param $key
     * @return string
     */
    public function get($key);

    /**
     * Save in cache
     *
     * @param string $key
     * @param $data
     * @return bool
     */
    public function save($key, $data);

    /**
     * Delete from cache by key
     *
     * @param $key
     * @return mixed
     */
    public function delete($key);

    /**
     * @param string $url
     * @param string $httpMethod
     * @return bool
     */
    public function isCachable($url, $httpMethod);

    /**
     * @param string $url
     * @param string $id
     * @param array $headers
     * @return string
     */
    public function hash($url, $id, array $headers);

    /**
     * @return bool
     */
    public function isInitialized();
}
