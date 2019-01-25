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

use Bookboon\Api\Cache\CacheInterface;
use Bookboon\Api\Client\BookboonResponse;
use Bookboon\Api\Client\ClientInterface;
use Bookboon\Api\Client\Headers;
use Bookboon\Api\Client\OauthClient;
use Psr\Http\Message\ResponseInterface;

class Bookboon
{
    private $client;

    /**
     * Bookboon constructor.
     *
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $appId
     * @param string $appSecret
     * @param array $scopes
     * @param array $headers
     * @param string|null $appUserId
     * @param string|null $redirectUri
     * @param CacheInterface|null $cache
     * @return Bookboon
     * @throws Exception\UsageException
     */
    public static function create(
        string $appId,
        string $appSecret,
        array $scopes,
        array $headers = [],
        ?string $appUserId = null,
        ?string $redirectUri = null,
        CacheInterface $cache = null
    ) : Bookboon {
        $headersObject = new Headers();
        foreach ($headers as $key => $value) {
            $headersObject->set($key, $value);
        }

        return new Bookboon(
            new OauthClient($appId, $appSecret, $headersObject, $scopes, $cache, $redirectUri, $appUserId)
        );
    }

    /**
     * @param string $url
     * @param array $variables
     * @param string $httpMethod
     * @param boolean $shouldCache
     * @return BookboonResponse
     * @throws Exception\UsageException
     */
    public function rawRequest(
        string $url,
        array $variables = [],
        string $httpMethod = ClientInterface::HTTP_GET,
        bool $shouldCache = true
    ) : BookboonResponse {
        return $this->client->makeRequest($url, $variables, $httpMethod, $shouldCache);
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }
}
