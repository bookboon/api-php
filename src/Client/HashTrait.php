<?php

namespace Bookboon\Api\Client;

use Psr\SimpleCache\CacheInterface;

trait HashTrait
{
    /**
     * @return CacheInterface|null
     */
    abstract public function getCache() : ?CacheInterface;

    /**
     * Hashes url with unique values: app id and headers.
     *
     * @param string $url
     * @param string $apiId
     * @param array $headers
     * @return string the hashed key
     */
    protected function hash(string $url, string $apiId, array $headers) : string
    {
        $headerString = '';
        foreach ($headers as $key => $value) {
            if ($key != Headers::HEADER_XFF) {
                $headerString .= $key.$value;
            }
        }

        return $apiId . '.' . sha1($headerString . $url);
    }

    /**
     * Determine whether cache should be attempted.
     *
     * @param string $url
     * @param string $httpMethod
     * @return bool
     * @internal param $variables
     *
     */
    public function isCachable(string $url, string $httpMethod) : bool
    {
        return $httpMethod === ClientInterface::HTTP_GET && $this->getCache() !== null;
    }
}