<?php

namespace Bookboon\Api\Client;


use Bookboon\Api\Cache\Cache;
use Bookboon\Api\Client\Oauth\OauthGrants;
use Bookboon\Api\Exception\ApiAuthenticationException;
use Bookboon\Api\Exception\ApiInvalidStateException;
use Bookboon\Api\Exception\UsageException;
use League\OAuth2\Client\Token\AccessToken;

interface Client
{
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';

    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';

    const API_PROTOCOL = 'https';
    const API_URL = 'bookboon.com/api';

    const VERSION = 'Bookboon-PHP/3.0';

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
     * @param null|string $type
     * @return AccessToken
     * @throws ApiAuthenticationException
     * @throws UsageException
     */
    public function requestAccessToken($code = null, $type = OauthGrants::AUTHORIZATION_CODE);

    /**
     * @return string
     */
    public function generateState();

    /**
     * @param AccessToken $accessToken
     * @return mixed
     */
    public function refreshAccessToken(AccessToken $accessToken);

    /**
     * @param string|null $state
     * @return string
     * @throws UsageException
     */
    public function getAuthorizationUrl($state = null);

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
     * @return void
     */
    public function setAccessToken(AccessToken $accessToken);

    /**
     * @return AccessToken
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
     * @param $stateParameter
     * @param $stateSession
     * @return bool
     * @throws ApiInvalidStateException
     * @throws UsageException
     */
    public function isCorrectState($stateParameter, $stateSession);

}