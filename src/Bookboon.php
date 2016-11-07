<?php

namespace Bookboon\Api;

/*
 *  Copyright 2016 Bookboon.com Ltd.
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

use Bookboon\Api\Cache\Cache;
use Bookboon\Api\Client\Client;
use Bookboon\Api\Client\Headers;
use Bookboon\Api\Exception\UsageException;

class Bookboon
{
    private $client;
    private $headers;

    /**
     * Bookboon constructor.
     *
     * @param $appId
     * @param $appSecret
     * @param array $headers in format array("headername" => "value")
     * @param Cache $cache
     * @param string $clientClass must implement Bookboon\Api\Client\Client
     * @throws UsageException
     */
    public function __construct($appId, $appSecret, array $headers = array(), $cache = null, $clientClass = 'Bookboon\Api\Client\BookboonCurlClient')
    {
        if (empty($appId) || empty($appSecret)) {
            throw new UsageException('Empty app id or app secret');
        }

        if (false === class_exists($clientClass) || false === in_array('Bookboon\Api\Client\Client', class_implements($clientClass))) {
            throw new UsageException('Invalid client class specified');
        }

        if (null !== $cache && false === in_array('Bookboon\Api\Cache\Cache', class_implements($cache))) {
            throw new UsageException('Invalid cache class specified');
        }

        $this->headers = new Headers();
        foreach ($headers as $key => $value) {
            $this->headers->set($key, $value);
        }

        $this->client = new $clientClass($appId, $appSecret, $this->headers, $cache);
    }

    /**
     * @param $url
     * @param array $variables
     * @param $httpMethod
     * @param bool $shouldCache
     * @return array
     */
    public function rawRequest($url, array $variables = array(),$httpMethod = Client::HTTP_GET, $shouldCache = true)
    {
        return $this->client->makeRequest($url, $variables, $httpMethod, $shouldCache);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return Headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
