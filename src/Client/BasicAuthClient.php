<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Client\Oauth\OauthGrants;
use Bookboon\Api\Exception\ApiAuthenticationException;
use Bookboon\Api\Exception\ApiGeneralException;
use Bookboon\Api\Exception\ApiInvalidStateException;
use Bookboon\Api\Exception\ApiTimeoutException;
use Bookboon\Api\Exception\UsageException;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\SimpleCache\CacheInterface;

class BasicAuthClient implements ClientInterface
{
    use ClientTrait, ResponseTrait, RequestTrait, HashTrait;

    const C_VERSION = '2.1';

    protected static $CURL_REQUESTS;

    public static $CURL_OPTS = [
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ];

    protected $_apiUri;

    /**
     * BasicAuthClient constructor.
     * @param string $apiId
     * @param string $apiSecret
     * @param Headers $headers
     * @param CacheInterface|null $cache
     * @param string|null $apiUri
     * @throws UsageException
     */
    public function __construct($apiId, $apiSecret, Headers $headers, CacheInterface $cache = null, $apiUri = null)
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
        $variables = [],
        $contentType = self::CONTENT_TYPE_FORM
    ) : BookboonResponse {
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
            if (curl_errno($http) === 28) {
                throw new ApiTimeoutException();
            }
            throw new ApiGeneralException('Curl error number ' . curl_errno($http));
        }

        curl_close($http);

        if (is_bool($response)) {
            throw new ApiGeneralException('Empty response from API');
        }

        $decodedHeaders = $this->decodeHeaders(substr($response, 0, $headersSize));
        $body = substr($response, $headersSize);

        if ($httpStatus >= 400) {
            $this->handleErrorResponse(
                $body,
                $decodedHeaders,
                $httpStatus,
                $uri
            );
        }

        return new BookboonResponse(
            $body,
            $httpStatus,
            $decodedHeaders
        );
    }

    protected function reportDeveloperInfo($request, $data)
    {
        self::$CURL_REQUESTS[] = [
            'curl' => $request,
            'data' => $data,
        ];
    }

    /**
     * @param array $options
     * @return string
     * @throws UsageException
     */
    public function getAuthorizationUrl(array $options = [])
    {
        throw new UsageException("Not Supported");
    }

    /**
     * @param array $options
     * @param string $type
     * @return AccessTokenInterface
     * @throws ApiAuthenticationException
     * @throws UsageException
     */
    public function requestAccessToken(
        array $options = [],
        string $type = OauthGrants::AUTHORIZATION_CODE
    ) : AccessTokenInterface {
        throw new UsageException("Not Supported");
    }

    /**
     * @param array $variables
     * @param string $contentType
     * @return string
     * @throws ApiGeneralException
     */
    protected function encodeByContentType(array $variables, string $contentType) : string
    {
        if (strpos($contentType, 'json') !== false) {
            $output = json_encode($variables);

            if ($output === false) {
                throw new ApiGeneralException('Failed to encode request to JSON');
            }

            return $output;
        }

        return http_build_query($variables);
    }


    /**
     * @param string $appUserId
     * @throws UsageException
     */
    public function setAct(string $appUserId) : void
    {
        throw new UsageException("Not Supported");
    }

    /**
     * @return string
     * @throws UsageException
     */
    public function getAct() : string
    {
        throw new UsageException("Not Supported");
    }

    public function setAccessToken(AccessTokenInterface $accessToken) : void
    {
        throw new UsageException("Not Supported");
    }

    public function getAccessToken() : ?AccessTokenInterface
    {
        throw new UsageException("Not Supported");
    }

    public function refreshAccessToken(AccessTokenInterface $accessToken)
    {
        throw new UsageException("Not Supported");
    }

    public function generateState()
    {
        throw new UsageException("Not Supported");
    }

    /**
     * @param string $stateParameter
     * @param string $stateSession
     * @return boolean
     * @throws UsageException
     */
    public function isCorrectState(string $stateParameter, string $stateSession) : bool
    {
        throw new UsageException("Not Supported");
    }

    /**
     * @return string
     */
    protected function getComponentVersion() : string
    {
        return self::C_VERSION;
    }

    /**
     * @return string
     */
    protected function getBaseApiUri() : string
    {
        return $this->_apiUri;
    }

    private function decodeHeaders(string $headers) : array
    {
        $headerArray = [];
        foreach (explode("\n", $headers) as $header) {
            $separator = strpos($header, ':');

            // Invalid header
            if ($separator === false) {
                continue;
            }

            $headerArray[substr($header, 0, $separator)] = trim(substr($header, $separator + 1));
        }

        return $headerArray;
    }
}