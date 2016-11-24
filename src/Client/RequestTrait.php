<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Cache\Cache;
use Bookboon\Api\Exception\UsageException;

trait RequestTrait
{
    /**
     * @param $url
     * @param string $type
     * @param array $variables
     * @param string $contentType
     * @return mixed
     */
    abstract protected function executeQuery($url, $type = Client::HTTP_GET, $variables = array(), $contentType = 'application/x-www-form-urlencoded');

    /**
     * @return Cache|null
     */
    abstract public function getCache();

    /**
     * @return string
     */
    abstract public function getApiId();

    /**
     * @return Headers
     */
    abstract public function getHeaders();

    abstract protected function reportDeveloperInfo($request, $data);

    /**
     * Prepares the call to the api and if enabled tries cache provider first for GET calls.
     *
     * @param string $relativeUrl     The url relative to the address. Must begin with '/'
     * @param array  $variables       Array of variables
     * @param string $httpMethod      Override http method
     * @param bool   $shouldCache     manually disable object cache for query
     * @param string $contentType     Request Content type
     *
     * @return array results of call
     *
     * @throws UsageException
     */
    public function makeRequest($relativeUrl, array $variables = array(), $httpMethod = Client::HTTP_GET, $shouldCache = true, $contentType = Client::CONTENT_TYPE_JSON)
    {
        $queryUrl = Client::API_URL . $relativeUrl;
        $postVariables = array();

        if ($httpMethod == Client::HTTP_GET && count($variables) !== 0) {
            $queryUrl .= '?' . http_build_query($variables);
        }

        if ($httpMethod == Client::HTTP_POST) {
            $postVariables = $variables;
        }

        if (substr($relativeUrl, 0, 1) !== '/') {
            throw new UsageException('Location must begin with forward slash');
        }

        if ($this->getCache() != null && $this->getCache()->isCachable($queryUrl, $httpMethod) && $shouldCache) {
            $result = $this->getFromCache($queryUrl);

            if ($result === false) {
                $result = $this->executeQuery($queryUrl, $httpMethod, $postVariables);
                $this->saveInCache($queryUrl, $result);
            }

            return $result;
        }

        return $this->executeQuery($queryUrl, $httpMethod, $postVariables, $contentType);
    }

    /**
     * @param $queryUrl
     * @param $result
     * @return void
     */
    protected function saveInCache($queryUrl, $result)
    {
        $hash = $this->getCache()->hash($queryUrl, $this->getApiId(), $this->getHeaders()->getAll());
        $this->getCache()->save($hash, $result);
    }

    /**
     * @param $queryUrl
     * @return array|bool
     */
    protected function getFromCache($queryUrl)
    {
        $hash = $this->getCache()->hash($queryUrl, $this->getApiId(), $this->getHeaders()->getAll());
        $result = $this->getCache()->get($hash);

        if ($result !== false) {
            $this->reportDeveloperInfo(array(
                'total_time' => 0,
                'http_code' => 'cache',
                'size_download' => mb_strlen(json_encode($result)),
                'url' => Client::API_PROTOCOL . "://" . $queryUrl,
            ), array());
        }

        return $result;
    }

}