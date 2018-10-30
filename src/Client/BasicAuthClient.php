<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Cache\Cache;
use Bookboon\Api\Client\Oauth\OauthGrants;
use Bookboon\Api\Exception\ApiAuthenticationException;
use Bookboon\Api\Exception\ApiGeneralException;
use Bookboon\Api\Exception\ApiInvalidStateException;
use Bookboon\Api\Exception\ApiTimeoutException;
use Bookboon\Api\Exception\UsageException;
use League\OAuth2\Client\Token\AccessToken;

class BasicAuthClient implements Client
{
    use ClientTrait, ResponseTrait, RequestTrait;

    const C_VERSION = '2.1';

    protected static $CURL_REQUESTS;

    public static $CURL_OPTS = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    );

    protected $_apiUri;

    /**
     * BasicAuthClient constructor.
     * @param string $apiId
     * @param string $apiSecret
     * @param Headers $headers
     * @param Cache|null $cache
     * @param string|null $apiUri
     * @throws UsageException
     */
    public function __construct($apiId, $apiSecret, Headers $headers, Cache $cache = null, $apiUri = null)
    {
        if (empty($apiId) || empty($apiSecret)) {
            throw new UsageException("Key and secret are required");
        }

        $this->setApiId($apiId);
        $this->setApiSecret($apiSecret);
        $this->setCache($cache);
        $this->setHeaders($headers);

        $this->_apiUri = $this->parseUriOrDefault($apiUri);
    }

    /**
     * Makes the actual query call to the remote api.
     *
     * @param string $uri The url relative to the address
     * @param string $type Bookboon::HTTP_GET or  Bookboon::HTTP_POST
     * @param array $variables array of post variables (key => value)
     * @param string $contentType
     * @return BookboonResponse
     * @throws ApiAuthenticationException
     * @throws ApiGeneralException
     * @throws ApiTimeoutException
     * @throws \Bookboon\Api\Exception\ApiNotFoundException
     * @throws \Bookboon\Api\Exception\ApiSyntaxException
     * @oaram string $contentType
     *
     */
    protected function executeQuery(
        $uri,
        $type = self::HTTP_GET,
        $variables = array(),
        $contentType = self::CONTENT_TYPE_FORM
    ) {
        $http = curl_init();
        $headers = $this->getHeaders()->getAll();


        if ($type == self::HTTP_POST) {
            $encodedVariables = $this->encodeByContentType($variables, $contentType);

            $headers[] = "Content-Type: $contentType";
            $headers[] = 'Content-Length: ' . strlen($encodedVariables);

            curl_setopt($http, CURLOPT_POSTFIELDS, $encodedVariables);
            curl_setopt($http, CURLOPT_CUSTOMREQUEST, $type);
        }

        curl_setopt($http, CURLOPT_USERAGENT, $this->getUserAgentString());
        curl_setopt($http, CURLOPT_URL, $uri);
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

        $responseArray = $this->handleResponse(
            substr($response, $headersSize),
            substr($response, 0, $headersSize),
            $httpStatus,
            $uri
        );

        return new BookboonResponse($responseArray, $headers);
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

    /**
     * @param array $options
     * @return string
     * @throws UsageException
     */
    public function getAuthorizationUrl(array $options = array())
    {
        throw new UsageException("Not Supported");
    }

    /**
     * @param array $options
     * @param null|string $type
     * @return AccessToken
     * @throws ApiAuthenticationException
     * @throws UsageException
     */
    public function requestAccessToken(array $options = array(), $type = OauthGrants::AUTHORIZATION_CODE)
    {
        throw new UsageException("Not Supported");
    }

    /**
     * @param array $variables
     * @param string $contentType
     * @return string
     */
    protected function encodeByContentType(array $variables, $contentType)
    {
        return strpos($contentType, 'json') !== false ? json_encode($variables) : http_build_query($variables);
    }


    /**
     * @param string $appUserId
     * @throws UsageException
     */
    public function setAct($appUserId)
    {
        throw new UsageException("Not Supported");
    }

    /**
     * @return string
     * @throws UsageException
     */
    public function getAct()
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

    /**
     * @param string $stateParameter
     * @param string  $stateSession
     * @return boolean
     * @throws UsageException
     */
    public function isCorrectState($stateParameter, $stateSession)
    {
        throw new UsageException("Not Supported");
    }

    /**
     * @return string
     */
    protected function getComponentVersion()
    {
        return self::C_VERSION;
    }

    /**
     * @return string
     */
    protected function getBaseApiUri()
    {
        return $this->_apiUri;
    }
}