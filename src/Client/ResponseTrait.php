<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Exception\ApiAuthenticationException;
use Bookboon\Api\Exception\ApiGeneralException;
use Bookboon\Api\Exception\ApiNotFoundException;
use Bookboon\Api\Exception\ApiSyntaxException;

trait ResponseTrait
{
    /**
     * @param string $body
     * @param array $headers
     * @param int $status
     * @param string $url
     *
     * @return array
     *
     * @throws ApiSyntaxException
     * @throws ApiAuthenticationException
     * @throws ApiGeneralException
     * @throws ApiNotFoundException
     */
    protected function handleResponse(string $body, array $headers, int $status, string $url)
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
                    $message = 'Invalid credentials';
                    if (isset($returnArray['message'])) {
                        $message = $returnArray['message'];
                    }
                    if (isset($returnArray['hint'])) {
                        $message .= ': ' . $returnArray['hint'];
                    }

                    throw new ApiAuthenticationException($message);
                case 410:
                case 404:
                    throw new ApiNotFoundException($url);
                    break;
                default:
                    throw new ApiGeneralException($this->generalExceptionMessage($returnArray ?? [], $headers));
            }
        }

        return $returnArray;
    }

    /**
     * @param array $responseArray
     * @param array $headers
     * @return string
     */
    protected function generalExceptionMessage(array $responseArray, array $headers)
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
     * @param array $headers
     * @param string $name
     *
     * @return string result
     */
    abstract protected function getResponseHeader(array $headers, string $name);
}