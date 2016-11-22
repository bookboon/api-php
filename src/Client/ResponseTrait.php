<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Exception\ApiAuthenticationException;
use Bookboon\Api\Exception\ApiGeneralException;
use Bookboon\Api\Exception\ApiNotFoundException;
use Bookboon\Api\Exception\ApiSyntaxException;

trait ResponseTrait
{
    /**
     * @param $body
     * @param $headers
     * @param $status
     * @param $url
     *
     * @return array
     *
     * @throws ApiSyntaxException
     * @throws ApiAuthenticationException
     * @throws ApiGeneralException
     * @throws ApiNotFoundException
     */
    protected function handleResponse($body, $headers, $status, $url)
    {
        $returnArray = json_decode($body, true);

        if ($status >= 300 || $status < 200) {
            switch ($status) {
                case 301:
                case 302:
                case 303:
                    $returnArray['url'] = $this->getResponseHeader($headers, 'Location');
                    break;
                case 400:
                case 405:
                    throw new ApiSyntaxException($returnArray['message']);
                case 401:
                case 403:
                    throw new ApiAuthenticationException('Invalid credentials');
                case 404:
                    throw new ApiNotFoundException($url);
                    break;
                default:
                    throw new ApiGeneralException($this->generalExceptionMessage($returnArray, $headers));
            }
        }

        return $returnArray;
    }

    private function generalExceptionMessage($responseArray, $headers)
    {
        $message = '';
        if (isset($responseArray['message'], $responseArray['code'])) {
            $message .= 'Code: ' . $responseArray['code'] . '    Message: ' . $responseArray['message'];
        }

        $xVarnish = $this->getResponseHeader($headers, 'X-Varnish');
        if (!empty($xVarnish)) {
            $message .= "    X-Varnish: $xVarnish";
        }

        $apiVersion = $this->getResponseHeader($headers, 'X-API-Version');
        if (!empty($apiVersion)) {
            $message .= "    X-API-Version: $apiVersion";
        }
        return $message;
    }

    /**
     * Return specific header value from string of headers.
     *
     * @param string $headers
     * @param string $name
     *
     * @return string result
     */
    abstract protected function getResponseHeader($headers, $name);
}