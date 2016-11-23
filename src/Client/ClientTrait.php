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
     * @return Cache|null
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param Cache $cache
     * @return void
     */
    public function setCache(Cache $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * @return Headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param Headers $headers
     */
    public function setHeaders(Headers $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return string
     */
    public function getApiId()
    {
        return $this->apiId;
    }

    /**
     * @param $apiId
     * @return void
     */
    public function setApiId($apiId)
    {
        $this->apiId = $apiId;
    }

    /**
     * @return string
     */
    public function getApiSecret()
    {
        return $this->apiSecret;
    }

    /**
     * @param $apiSecret
     * @return string
     */
    public function setApiSecret($apiSecret)
    {
        $this->apiSecret = $apiSecret;
    }
}