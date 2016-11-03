<?php

namespace Bookboon\Api\Client;


use Bookboon\Api\Cache\Cache;
use Bookboon\Api\Exception\ApiSyntaxException;
use Bookboon\Api\Exception\AuthenticationException;
use Bookboon\Api\Exception\GeneralApiException;
use Bookboon\Api\Exception\NotFoundException;

abstract class ClientCommon implements Client
{
    protected $apiId;
    protected $apiSecret;
    protected $headers;
    protected $cache;

    /**
     * ClientCommon constructor.
     * @param string $apiId
     * @param string $apiSecret
     * @param Headers $headers
     * @param Cache $cache
     */
    public function __construct($apiId, $apiSecret, Headers $headers, $cache = null)
    {
        $this->apiId = $apiId;
        $this->apiSecret = $apiSecret;
        $this->headers = $headers;
        $this->cache = $cache;
    }

    /**
     * @param $body
     * @param $headers
     * @param $status
     * @param $url
     *
     * @return array
     *
     * @throws ApiSyntaxException
     * @throws AuthenticationException
     * @throws GeneralApiException
     * @throws NotFoundException
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
                    throw new AuthenticationException('Invalid credentials');
                case 404:
                    throw new NotFoundException($url);
                    break;
                default:
                    $errorDetail = isset($returnArray['message']) ? 'Message: '.$returnArray['message'] : '';
                    $xVarnish = $this->getResponseHeader($headers, 'X-Varnish');
                    $errorDetail .= !empty($xVarnish) ? "\nX-Varnish: ".$xVarnish : '';
                    throw new GeneralApiException($errorDetail);
            }
        }

        return $returnArray;
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