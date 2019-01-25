<?php

namespace Bookboon\Api\Cache;

use Bookboon\Api\Client\ClientInterface;
use Bookboon\Api\Client\Headers;


trait HashTrait
{
    /**
     * Hashes url with unique values: app id and headers.
     *
     * @param string $url
     *
     * @param array $headers
     * @return string the hashed key
     */
    public function hash(string $url, string $id, array $headers) : string
    {
        $headerString = "";
        foreach ($headers as $key => $value) {
            if ($key != Headers::HEADER_XFF) {
                $headerString .= $key.$value;
            }
        }

        return sha1($id . $headerString . $url);
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
        return $httpMethod === ClientInterface::HTTP_GET && $this->isInitialized();
    }

    /**
     * @return bool
     */
    abstract function isInitialized() : bool;
}