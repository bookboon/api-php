<?php

namespace Bookboon\Api\Client;


use Bookboon\Api\Cache\Cache;
use Bookboon\Api\Exception\ApiAuthenticationException;
use Bookboon\Api\Exception\ApiGeneralException;
use Bookboon\Api\Exception\ApiNotFoundException;
use Bookboon\Api\Exception\ApiSyntaxException;
use Bookboon\Api\Exception\ApiTimeoutException;
use Bookboon\Api\Exception\UsageException;

trait RequestTrait
{
    abstract protected function executeQuery($url, $type = self::HTTP_GET, $variables = array());

    /**
     * @return Cache|null
     */
    abstract public function getCache();

    /**
     * @return Headers
     */
    abstract public function getHeaders();

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
    public function makeRequest($relativeUrl, array $variables = array(), $httpMethod = self::HTTP_GET, $shouldCache = true)
    {
        $queryUrl = static::API_URL . $relativeUrl;
        $postVariables = array();

        if ($httpMethod == static::HTTP_GET && count($variables) !== 0) {
            $queryUrl .= '?' . http_build_query($variables);
        }

        if ($httpMethod == static::HTTP_POST) {
            $postVariables = $variables;
        }

        if (substr($relativeUrl, 0, 1) !== '/') {
            throw new UsageException('Location must begin with forward slash');
        }

        if ($this->cache != null && $this->getCache()->isCachable($queryUrl, $httpMethod) && $shouldCache) {
            $hash = $this->getCache()->hash($queryUrl, $this->apiId, $this->getHeaders()->getAll());
            $result = $this->getCache()->get($hash);

            if ($result === false) {
                $result = $this->executeQuery($queryUrl, $httpMethod, $postVariables);
                $this->getCache()->save($hash, $result);
            } else {
                $this->reportDeveloperInfo(array(
                    'total_time' => 0,
                    'http_code' => 'memcache',
                    'size_download' => mb_strlen(json_encode($result)),
                    'url' => 'https://'.$queryUrl,
                ), array());
            }

            return $result;
        }

        return $this->executeQuery($queryUrl, $httpMethod, $postVariables);
    }
}