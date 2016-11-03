<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Cache\Cache;

trait ClientTrait
{
    protected $apiId;
    protected $apiSecret;
    protected $headers;
    protected $cache;

    /**
     * ClientCommon constructor.
     * @param string $apiId
     * @param string $apiSecret
     * @param Headers $headers
     * @param Cache $cache
     */
    public function __construct($apiId, $apiSecret, Headers $headers, $cache = null)
    {
        $this->apiId = $apiId;
        $this->apiSecret = $apiSecret;
        $this->headers = $headers;
        $this->cache = $cache;
    }

    /**
     * @return Cache|null
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return Headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}