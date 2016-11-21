<?php

namespace Bookboon\Api\Client;


use Bookboon\Api\Cache\Cache;
use Bookboon\Api\Exception\UsageException;
use League\OAuth2\Client\Token\AccessToken;

interface Client
{
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';

    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';

    const API_URL = 'bookboon.com/api';

    /**
     * Prepares the call to the api and if enabled tries cache provider first for GET calls.
     *
     * @param string $relativeUrl     The url relative to the address. Must begin with '/'
     * @param array  $variables       Array of variables
     * @param string $httpMethod      Override http method
     * @param bool   $shouldCache     manually disable object cache for query
     * @param string $contentType     Request Content type
     *
     * @return array results of call
     *
     * @throws UsageException
     */
    public function makeRequest($relativeUrl, array $variables = array(), $httpMethod = self::HTTP_GET, $shouldCache = true, $contentType = self::CONTENT_TYPE_FORM);

    /**
     * @param $code
     * @param $stateParameter
     * @param $stateSession
     * @return string
     * @internal param $state
     */
    public function requestAccessToken($code);

    /**
     * @return mixed
     */
    public function generateState();

    /**
     * @param AccessToken $accessToken
     * @return mixed
     */
    public function refreshAccessToken(AccessToken $accessToken);

    /**
     * @return string
     */
    public function getAuthorizationUrl();

    /**
     * @param $appUserId
     * @return void
     */
    public function setAppUserId($appUserId);

    /**
     * @return string
     */
    public function getAppUserId();

    /**
     * @param AccessToken $accessToken
     * @return mixed
     */
    public function setAccessToken(AccessToken $accessToken);

    /**
     * @return mixed
     */
    public function getAccessToken();


    /**
     * @return Headers
     */
    public function getHeaders();

    /**
     * @param Headers $headers
     * @return void
     */
    public function setHeaders(Headers $headers);

    /**
     * @return Cache|null
     */
    public function getCache();

    /**
     * @param $cache
     * @return void
     */
    public function setCache(Cache $cache);

    /**
     * @return string
     */
    public function getApiSecret();

    /**
     * @return string
     */
    public function getApiId();

    /**
     * @param $apiId
     * @return void
     */
    public function setApiId($apiId);

    /**
     * @param $apiSecret
     * @return string
     */
    public function setApiSecret($apiSecret);

    /**
     * @param array $scopes
     * @return void
     */
    public function setScopes(array $scopes);

    /**
     * @return array
     */
    public function getScopes();

    /**
     * @param $redirectUri
     * @return void
     */
    public function setRedirectUri($redirectUri);

    /**
     * @return string
     */
    public function getRedirectUri();

    public function isCorrectState($stateParameter, $stateSession);

}