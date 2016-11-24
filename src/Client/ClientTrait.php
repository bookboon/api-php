<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Cache\Cache;

trait ClientTrait
{
    protected $apiId;
    protected $apiSecret;
    protected $headers;
    protected $cache;

    abstract protected function getComponentVersion();

    protected function getUserAgentString()
    {
        if (defined('HHVM_VERSION')) {
            $runtime = 'HHVM/' . HHVM_VERSION;
        } else {
            $runtime = 'PHP/' . phpversion();
        }

        $component = substr(get_class($this), strrpos(get_class($this), '\\') + 1);
        return Client::VERSION . ' ' . $runtime . ' ' . $component . '/' . $this->getComponentVersion();
    }

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
    protected function getApiId()
    {
        return $this->apiId;
    }

    /**
     * @param $apiId
     * @return void
     */
    protected function setApiId($apiId)
    {
        $this->apiId = $apiId;
    }

    /**
     * @return string
     */
    protected function getApiSecret()
    {
        return $this->apiSecret;
    }

    /**
     * @param $apiSecret
     * @return string
     */
    protected function setApiSecret($apiSecret)
    {
        $this->apiSecret = $apiSecret;
    }
}