<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Cache\Cache;
use Bookboon\Api\Exception\ApiGeneralException;
use Bookboon\Api\Exception\ApiTimeoutException;
use Bookboon\Api\Exception\UsageException;
use League\OAuth2\Client\Token\AccessToken;

class BasicAuthClient implements Client
{
    use ClientTrait, ResponseTrait, RequestTrait;

    protected static $CURL_REQUESTS;

    public static $CURL_OPTS = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => 'bookboon-php-3',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    );

    public function __construct($apiId, $apiSecret, Headers $headers,  Cache $cache)
    {
        $this->apiId = $apiId;
        $this->apiSecret = $apiSecret;
        $this->headers = $headers;
        $this->cache = $cache;
    }

    /**
     * Makes the actual query call to the remote api.
     *
     * @param string $url The url relative to the address
     * @param string $type Bookboon::HTTP_GET or  Bookboon::HTTP_POST
     * @param array $variables array of post variables (key => value)
     * @oaram string $contentType
     *
     * @param string $contentType
     * @return array
     * @throws ApiGeneralException
     * @throws ApiTimeoutException
     */
    protected function executeQuery($url, $type = self::HTTP_GET, $variables = array(), $contentType = self::CONTENT_TYPE_FORM)
    {
        $http = curl_init();
        $headers = $this->getHeaders()->getAll();

        if ($type == self::HTTP_POST) {
            $encodedVariables = $this->encodeByContentType($variables, $contentType);
            $headers[] = "Content-Type: $contentType";
            $headers[] = 'Content-Length: ' . sizeof($encodedVariables);

            curl_setopt($http, CURLOPT_POST, true);
            curl_setopt($http, CURLOPT_POSTFIELDS, $encodedVariables);
        }

        curl_setopt($http, CURLOPT_URL, "https://$url");
        curl_setopt($http, CURLOPT_USERPWD, $this->getApiId() . ':' . $this->getApiSecret());
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);

        foreach (self::$CURL_OPTS as $key => $val) {
            curl_setopt($http, $key, $val);
        }

        $response = curl_exec($http);
        $headersSize = curl_getinfo($http, CURLINFO_HEADER_SIZE);
        $httpStatus = curl_getinfo($http, CURLINFO_HTTP_CODE);

        $this->reportDeveloperInfo(curl_getinfo($http), $variables);

        if (curl_errno($http)) {
            if (curl_errno($http) == 28) {
                throw new ApiTimeoutException();
            }
            throw new ApiGeneralException('Curl error number ' . curl_errno($http));
        }

        curl_close($http);

        return $this->handleResponse(substr($response, $headersSize), substr($response, 0, $headersSize), $httpStatus, $url);
    }

    /**
     * Return specific header value from string of headers.
     *
     * @param string $headers
     * @param string $name
     *
     * @return string result
     */
    protected function getResponseHeader($headers, $name)
    {
        foreach (explode("\n", $headers) as $header) {
            if (strpos($header, $name) === 0) {
                return trim(str_replace("$name: ", '', $header));
            }
        }

        return '';
    }

    protected function reportDeveloperInfo($request, $data)
    {
        self::$CURL_REQUESTS[] = array(
            'curl' => $request,
            'data' => $data,
        );
    }

    public function getAuthorizationUrl()
    {
        throw new UsageException("Not Supported");
    }

    /**
     * @param $code
     * @param $stateParameter
     * @param $stateSession
     * @return mixed
     * @throws UsageException
     * @internal param $state
     */
    public function requestAccessToken($code)
    {
        throw new UsageException("Not Supported");
    }

    /**
     * @param $apiId
     * @return void
     */
    public function setApiId($apiId)
    {
        $this->setApiId($apiId);
    }

    /**
     * @param $apiSecret
     * @return string
     */
    public function setApiSecret($apiSecret)
    {
        $this->setApiSecret($apiSecret);
    }

    /**
     * @param array $scopes
     * @throws UsageException
     */
    public function setScopes(array $scopes)
    {
        throw new UsageException("Not Supported");
    }

    /**
     * @return array
     * @throws UsageException
     */
    public function getScopes()
    {
        throw new UsageException("Not Supported");
    }

    /**
     * @param $redirectUri
     * @throws UsageException
     */
    public function setRedirectUri($redirectUri)
    {
        throw new UsageException("Not Supported");
    }

    /**
     * @return string
     * @throws UsageException
     */
    public function getRedirectUri()
    {
        throw new UsageException("Not Supported");
    }

    /**
     * @param $appUserId
     * @throws UsageException
     */
    public function setAppUserId($appUserId)
    {
        throw new UsageException("Not Supported");
    }

    /**
     * @return string
     * @throws UsageException
     */
    public function getAppUserId()
    {
        throw new UsageException("Not Supported");
    }

    public function setAccessToken(AccessToken $accessToken)
    {
        throw new UsageException("Not Supported");
    }

    public function getAccessToken()
    {
        throw new UsageException("Not Supported");
    }

    public function refreshAccessToken(AccessToken $accessToken)
    {
        throw new UsageException("Not Supported");
    }

    public function generateState()
    {
        throw new UsageException("Not Supported");
    }

    public function isCorrectState($stateParameter, $stateSession)
    {
        throw new UsageException("Not Supported");
    }
}