<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Client\Oauth\OauthGrants;
use Bookboon\Api\Exception\ApiAuthenticationException;
use Bookboon\Api\Exception\ApiInvalidStateException;
use Bookboon\Api\Exception\UsageException;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\SimpleCache\CacheInterface;

interface ClientInterface
{
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';
    const HTTP_DELETE = 'DELETE';
    const HTTP_PUT = 'PUT';

    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';

    const API_PROTOCOL = 'https';
    const API_HOST = 'bookboon.com';
    const API_PATH = '/api';

    const VERSION = 'Bookboon-PHP/3.3';

    /**
     * Prepares the call to the api and if enabled tries cache provider first for GET calls.
     *
     * @param string $relativeUrl     The url relative to the address. Must begin with '/'
     * @param array  $variables       Array of variables
     * @param string $httpMethod      Override http method
     * @param bool   $shouldCache     manually disable object cache for query
     * @param string $contentType     Request Content type
     *
     * @return BookboonResponse results of call
     *
     * @throws UsageException
     */
    public function makeRequest(
        string $relativeUrl,
        array $variables = [],
        string $httpMethod = self::HTTP_GET,
        bool $shouldCache = true,
        string $contentType = self::CONTENT_TYPE_FORM
    ) : BookboonResponse;

    /**
     * @param array $options
     * @param string $type
     * @return AccessTokenInterface
     * @throws ApiAuthenticationException
     * @throws UsageException
     */
    public function requestAccessToken(array $options = [], string $type = OauthGrants::AUTHORIZATION_CODE);

    /**
     * @return string
     */
    public function generateState();

    /**
     * @param AccessTokenInterface $accessToken
     * @return mixed
     */
    public function refreshAccessToken(AccessTokenInterface $accessToken);

    /**
     * @param array $options
     * @return string
     * @throws UsageException
     */
    public function getAuthorizationUrl(array $options = []) : string;

    public function setAct(string $appUserId) : void;

    public function getAct() : ?string;

    /**
     * @param AccessTokenInterface $accessToken
     * @return void
     */
    public function setAccessToken(AccessTokenInterface $accessToken) : void;

    public function getAccessToken() : ?AccessTokenInterface;

    public function getHeaders() : Headers;

    public function setHeaders(Headers $headers) : void;

    public function getCache() : ?CacheInterface;

    public function setCache(CacheInterface $cache) : void;

    /**
     * @param string $stateParameter
     * @param string $stateSession
     * @return bool
     * @throws ApiInvalidStateException
     * @throws UsageException
     */
    public function isCorrectState(string $stateParameter, string $stateSession) : bool;
}
