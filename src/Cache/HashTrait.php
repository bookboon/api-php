<?php

namespace Bookboon\Api\Cache;

use Bookboon\Api\Client\Client;
use Bookboon\Api\Client\Headers;


trait HashTrait
{
    /**
     * Hashes url with unique values: app id and headers.
     *
     * @param $url
     *
     * @param array $headers
     * @return string the hashed key
     */
    public function hash($url, $id, array $headers)
    {
        $headerString = "";
        foreach ($headers as $key => $value) {
            if ($key != Headers::HEADER_XFF) {
                $headerString = $key.$value;
            }
        }

        return sha1($id . $headerString . $url);
    }

    /**
     * Determine whether cache should be attempted.
     *
     * @param $url
     * @param $httpMethod
     * @return bool
     * @internal param $variables
     *
     */
    public function isCachable($url, $httpMethod)
    {
        if ($httpMethod === Client::HTTP_GET && $this->isInitialized()) {
            return true;
        }

        return false;
    }
}