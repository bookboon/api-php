<?php

namespace Bookboon\Api\Client;


use Bookboon\Api\Cache\Cache;
use Bookboon\Api\Exception\ApiSyntaxException;
use Bookboon\Api\Exception\AuthenticationException;
use Bookboon\Api\Exception\GeneralApiException;
use Bookboon\Api\Exception\NotFoundException;
use Bookboon\Api\Exception\TimeoutException;

interface Client
{
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';

    const API_URL = 'bookboon.com/api';


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
     * @throws ApiSyntaxException
     * @throws AuthenticationException
     * @throws GeneralApiException
     * @throws NotFoundException
     * @throws TimeoutException
     */
    public function makeRequest($relativeUrl, array $variables = array(), $httpMethod = self::HTTP_GET, $shouldCache = true);
}