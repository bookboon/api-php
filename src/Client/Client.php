<?php

namespace Bookboon\Api\Client;


use Bookboon\Api\Cache\Cache;
use Bookboon\Api\Exception\UsageException;

interface Client
{
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';

    const API_URL = 'bookboon.com/api';

    /**
     * Client constructor.
     * @param $apiId
     * @param $apiSecret
     * @param Headers $headers
     * @param Cache|null $cache
     */
    public function __construct($apiId, $apiSecret, Headers $headers, $cache = null);

    /**
     * Prepares the call to the api and if enabled tries cache provider first for GET calls.
     *
     * @param string $relativeUrl     The url relative to the address. Must begin with '/'
     * @param array  $variables       Array of variables
     * @param string $httpMethod      Override http method
     * @param bool   $shouldCache     manually disable object cache for query
     *
     * @return array results of call
     *
     * @throws UsageException
     */
    public function makeRequest($relativeUrl, array $variables = array(), $httpMethod = self::HTTP_GET, $shouldCache = true);

    /**
     * @return Headers
     */
    public function getHeaders();

    /**
     * @return Cache|null
     */
    public function getCache();
}