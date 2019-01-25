<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Cache\CacheInterface;

trait ClientTrait
{
    protected $apiId;
    protected $apiSecret;
    protected $headers;
    protected $cache;

    abstract protected function getComponentVersion();

    protected function getUserAgentString() : string
    {
        if (defined('HHVM_VERSION')) {
            $runtime = 'HHVM/' . HHVM_VERSION;
        } else {
            $runtime = 'PHP/' . phpversion();
        }

        $component = substr(get_class($this), strrpos(get_class($this), '\\') + 1);
        return ClientInterface::VERSION . ' ' . $runtime . ' ' . $component . '/' . $this->getComponentVersion();
    }

    /**
     * @return CacheInterface|null
     */
    public function getCache() : ?CacheInterface
    {
        return $this->cache;
    }

    /**
     * @param CacheInterface $cache
     * @return void
     */
    public function setCache(CacheInterface $cache = null) : void
    {
        $this->cache = $cache;
    }

    /**
     * @return Headers
     */
    public function getHeaders() : Headers
    {
        return $this->headers;
    }

    /**
     * @param Headers $headers
     */
    public function setHeaders(Headers $headers) : void
    {
        $this->headers = $headers;
    }

    /**
     * @return string
     */
    protected function getApiId() : string
    {
        return $this->apiId;
    }

    /**
     * @param string $apiId
     * @return void
     */
    protected function setApiId(string $apiId) : void
    {
        $this->apiId = $apiId;
    }

    /**
     * @return string
     */
    protected function getApiSecret() : string
    {
        return $this->apiSecret;
    }

    /**
     * @param string $apiSecret
     * @return void
     */
    protected function setApiSecret(string $apiSecret) : void
    {
        $this->apiSecret = $apiSecret;
    }
}