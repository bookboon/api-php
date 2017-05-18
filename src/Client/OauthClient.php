<?php

namespace Bookboon\Api\Client;


use Bookboon\Api\Cache\Cache;
use Bookboon\Api\Client\Oauth\BookboonProvider;
use Bookboon\Api\Client\Oauth\OauthGrants;
use Bookboon\Api\Exception\ApiAccessTokenExpired;
use Bookboon\Api\Exception\ApiAuthenticationException;
use Bookboon\Api\Exception\ApiInvalidStateException;
use Bookboon\Api\Exception\UsageException;
use GuzzleHttp\Exception\RequestException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BookboonOauthClient
 * @package Bookboon\Api\Client
 */
class OauthClient implements Client
{
    use ClientTrait, ResponseTrait, RequestTrait;

    const C_VERSION = '1.0';

    /** @var AccessToken */
    private $accessToken;

    /** @var string */
    protected $appUserId;

    /** @var BookboonProvider */
    protected $provider;

    /**
     * ClientCommon constructor.
     * @param string $apiId
     * @param string $apiSecret
     * @param Headers $headers
     * @param array $scopes
     * @param Cache $cache
     * @param $redirectUri
     * @param $appUserId
     * @throws UsageException
     */
    public function __construct($apiId, $apiSecret, Headers $headers, array $scopes, Cache $cache = null, $redirectUri = null, $appUserId = null)
    {
        if (empty($apiId)) {
            throw new UsageException("Client id is required");
        }

        $this->provider = new BookboonProvider([
            'clientId'      => $apiId,
            'clientSecret'  => $apiSecret,
            'scopes'        => $scopes,
            'redirectUri'   => $redirectUri
        ]);

        $this->setCache($cache);
        $this->setHeaders($headers);
        $this->setAppUserId($appUserId);
    }

    /**
     * @param $url
     * @param string $type
     * @param array $variables
     * @param string $contentType
     * @return mixed
     * @throws ApiAccessTokenExpired
     * @throws ApiAuthenticationException
     */
    protected function executeQuery($url, $type = Client::HTTP_GET, $variables = array(), $contentType = Client::CONTENT_TYPE_JSON)
    {
        if (!($this->getAccessToken() instanceof AccessToken)) {
            throw new ApiAuthenticationException("Not authenticated");
        }

        $options = [
            'allow_redirects' => false,
            'headers' => $this->headers->getHeadersArray()
        ];
        $options['headers']['User-Agent'] = $this->getUserAgentString();
        
        $url = Client::API_PROTOCOL . '://' . $url;

        if (count($variables) > 0 && $type == Client::HTTP_POST) {
            $postType = $contentType == Client::CONTENT_TYPE_JSON ? 'json' : 'form_params';
            $options[$postType] = $variables;
        }

        try {
            $request = $this->provider->getAuthenticatedRequest(
                $type,
                $url,
                $this->getAccessToken()
            );

            if ($this->getAccessToken()->hasExpired()) {
                throw new ApiAccessTokenExpired("Bookboon API Access Token Has Now Expired");

            }
        }

        catch (IdentityProviderException $e) {
            throw new ApiAuthenticationException("Identity not found");
        }

        /** @var ResponseInterface*/
        try {
            $response = $this->provider->getHttpClient()->send($request, $options);
        }

        catch (RequestException $e) {
            $response = $e->getResponse();
        }

        $responseArray = $this->handleResponse(
            $response->getBody()->getContents(),
            $response->getHeaders(),
            $response->getStatusCode(),
            $url
        );

        return new BookboonResponse($responseArray, $response->getHeaders());
    }

    /**
     * @param array $options
     * @return string
     */
    public function getAuthorizationUrl(array $options = array())
    {
        $provider = $this->provider;

        if (null != $this->appUserId && false === isset($options['app_user_id'])) {
            $options['app_user_id'] = $this->appUserId;
        }

        $url = $provider->getAuthorizationUrl($options);

        return $url;
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
        $provider = $this->provider;

        if ($type == OauthGrants::AUTHORIZATION_CODE && !isset($options["code"])) {
            throw new UsageException("This oauth flow requires a code");
        }

        if (null === $this->appUserId) {
            $options['app_user_id'] = $this->appUserId;
        }

        try {
            $this->accessToken = $provider->getAccessToken($type, $options);
        }

        catch (IdentityProviderException $e) {
            throw new ApiAuthenticationException("Authorization Failed");
        }

        return $this->accessToken;
    }


    /**
     * @param AccessToken $accessToken
     * @return AccessToken
     */
    public function refreshAccessToken(AccessToken $accessToken)
    {
        $this->accessToken = $this->provider->getAccessToken('refresh_token', [
            'refresh_token' => $accessToken->getRefreshToken()
        ]);

        return $accessToken;
    }


    public function generateState()
    {
        return $this->provider->generateRandomState();
    }


    /**
     * @param AccessToken $accessToken
     * @return mixed|void
     * @throws ApiAccessTokenExpired
     */
    public function setAccessToken(AccessToken $accessToken)
    {
        if ($accessToken->hasExpired()) {
            throw new ApiAccessTokenExpired("The api access token has expired");
        }

        $this->accessToken = $accessToken;
    }

    /**
     * @param $stateParameter
     * @param $stateSession
     * @return bool
     * @throws ApiInvalidStateException
     * @throws UsageException
     */
    public function isCorrectState($stateParameter, $stateSession)
    {
        if (empty($stateParameter) || ($stateParameter !== $stateSession)) {
            throw new ApiInvalidStateException("State is invalid");
        }

        return true;
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
        if (isset($headers[$name][0])) {
            return $headers[$name][0];
        }

        if (isset($headers[$name])) {
            return $headers[$name];
        }

        return '';
    }

    /**
     * @param $appUserId
     */
    public function setAppUserId($appUserId)
    {
        $this->appUserId = $appUserId;
    }

    /**
     * @return string
     */
    public function getAppUserId()
    {
        return $this->appUserId;
    }

    /**
     * @return AccessToken
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    protected function reportDeveloperInfo($request, $data)
    {
        // TODO: Implement reportDeveloperInfo() method.
    }

    protected function getComponentVersion()
    {
        return self::C_VERSION;
    }
}