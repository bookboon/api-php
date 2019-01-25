<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Cache\CacheInterface;
use Bookboon\Api\Exception\UsageException;

trait RequestTrait
{
    /**
     * @param string $uri
     * @param string $type
     * @param array $variables
     * @param string $contentType
     * @return BookboonResponse
     */
    abstract protected function executeQuery(
        string $uri,
        string $type = ClientInterface::HTTP_GET,
        array $variables = [],
        string $contentType = ClientInterface::CONTENT_TYPE_FORM
    ) : BookboonResponse;

    /**
     * @return CacheInterface|null
     */
    abstract public function getCache() : ?CacheInterface;

    /**
     * @return string
     */
    abstract public function getApiId() : string;

    /**
     * @return Headers
     */
    abstract public function getHeaders() : Headers;

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
     * @return BookboonResponse results of call
     *
     * @throws UsageException
     */
    public function makeRequest(
        string $relativeUrl,
        array $variables = [],
        string $httpMethod = ClientInterface::HTTP_GET,
        bool $shouldCache = true,
        string $contentType = ClientInterface::CONTENT_TYPE_JSON
    ) : BookboonResponse {
        if (strpos($relativeUrl, '/') !== 0) {
            throw new UsageException('Location must begin with forward slash');
        }

        $queryUrl = $this->getBaseApiUri() . $relativeUrl;
        $postVariables = [];

        if ($httpMethod === ClientInterface::HTTP_GET && count($variables) !== 0) {
            $queryUrl .= '?' . http_build_query($variables);
        }

        if ($httpMethod === ClientInterface::HTTP_POST) {
            $postVariables = $variables;
        }

        if ($this->getCache() !== null && $this->getCache()->isCachable($queryUrl, $httpMethod) && $shouldCache) {

            $result = $this->getFromCache($queryUrl);

            if ($result === null) {
                $result = $this->executeQuery($queryUrl, $httpMethod, $postVariables);
                $this->saveInCache($queryUrl, $result);
            }

            return $result;
        }

        return $this->executeQuery($queryUrl, $httpMethod, $postVariables, $contentType);
    }

    /**
     * @param string $queryUrl
     * @param BookboonResponse $result
     * @return void
     */
    protected function saveInCache(string $queryUrl, BookboonResponse $result)
    {
        $hash = $this->getCache()->hash($queryUrl, $this->getApiId(), $this->getHeaders()->getAll());
        $this->getCache()->save($hash, $result);
    }

    /**
     * @param string $queryUrl
     * @return BookboonResponse|null
     */
    protected function getFromCache(string $queryUrl) : ?BookboonResponse
    {
        $hash = $this->getCache()->hash($queryUrl, $this->getApiId(), $this->getHeaders()->getAll());
        $result = $this->getCache()->get($hash);

        if (!($result instanceof BookboonResponse)) {
            return null;
        }

        $this->reportDeveloperInfo([
            'total_time' => 0,
            'http_code' => 'cache',
            'size_download' => mb_strlen(json_encode($result->getReturnArray())),
            'url' => ClientInterface::API_PROTOCOL . '://' . $queryUrl,
        ], []);

        return $result;
    }

    /**
     * @param string $uri
     * @return string
     * @throws UsageException
     */
    protected function parseUriOrDefault(?string $uri) : string
    {
        $protocol = ClientInterface::API_PROTOCOL;
        $host = ClientInterface::API_HOST;
        $path = ClientInterface::API_PATH;

        if (!empty($uri)) {
            $parts = explode('://', $uri);
            $protocol = $parts[0];
            $host = $parts[1];
            if (strpos($host, '/') !== false) {
                throw new UsageException('URI must not contain forward slashes');
            }
        }

        if ($protocol != 'http' && $protocol != 'https') {
            throw new UsageException('Invalid protocol specified in URI');
        }

        return "${protocol}://${host}${path}";
    }

    /**
     * @return string
     */
    abstract protected function getBaseApiUri() : string;

}