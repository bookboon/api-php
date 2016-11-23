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

    /** @var AccessToken */
    private $accessToken;

    /** @var array */
    protected $scopes;

    /** @var string */
    protected $redirect;

    /** @var string */
    protected $appUserId;

    /** @var BookboonProvider */
    protected $provider;

    /**
     * ClientCommon constructor.
     * @param string $apiId
     * @param string $apiSecret
     * @param Headers $headers
     * @param $redirectUri
     * @param array $scopes
     * @param $appUserId
     * @param Cache $cache
     * @throws UsageException
     */
    public function __construct($apiId, $apiSecret, Headers $headers, $redirectUri, array $scopes, $appUserId, $cache = null)
    {
        if (empty($apiId)) {
            throw new UsageException("Client id is required");
        }

        $this->apiId = $apiId;
        $this->apiSecret = $apiSecret;
        $this->headers = $headers;
        $this->cache = $cache;
        $this->redirect = $redirectUri;
        $this->scopes = $scopes;
        $this->appUserId = $appUserId;
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
    protected function executeQuery($url, $type = Client::HTTP_GET, $variables = array(), $contentType = Client::CONTENT_TYPE_FORM)
    {
        if (!($this->getAccessToken() instanceof AccessToken)) {
            throw new ApiAuthenticationException("Not authenticated");
        }

        $options = [
            'allow_redirects' => false,
            'headers' => $this->headers->getHeadersArray()
        ];
        $url = Client::API_PROTOCOL . '://' . $url;

        if (count($variables) > 0 && $type == Client::HTTP_POST) {
            $postType = $contentType == Client::CONTENT_TYPE_JSON ? 'json' : 'form_params';
            $options[$postType] = $variables;
        }

        try {
            $request = $this->getProvider()->getAuthenticatedRequest(
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

        return $this->handleResponse($response->getBody()->getContents(), $response->getHeaders(), $response->getStatusCode(), $url);
    }

    /**
     * @param string|null $state
     * @return string
     */
    public function getAuthorizationUrl($state = null)
    {
        $provider = $this->getProvider();

        $options = $state != null ? [ 'state' => $state ]: [];

        if (false === is_null($this->appUserId)) {
            $options['app_user_id'] = $this->appUserId;
        }

        $url = $provider->getAuthorizationUrl($options);

        return $url;
    }

    /**
     * @param $code
     * @param null|string $type
     * @return AccessToken
     * @throws ApiAuthenticationException
     * @throws UsageException
     */
    public function requestAccessToken($code = null, $type = OauthGrants::AUTHORIZATION_CODE)
    {
        $provider = $this->getProvider();
        $options = null === $code ? [] : ['code' => $code];

        if ($type == OauthGrants::AUTHORIZATION_CODE && empty($code)) {
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
        $this->accessToken = $this->getProvider()->getAccessToken('refresh_token', [
            'refresh_token' => $this->accessToken->getRefreshToken()
        ]);

        return $accessToken;
    }


    public function generateState()
    {
        return $this->provider->getState();
    }


    /**
     * @return BookboonProvider
     */
    public function getProvider()
    {
        if ($this->provider instanceof BookboonProvider) {
            return $this->provider;
        }

        $this->provider =  new BookboonProvider([
            'clientId'                => $this->getApiId(),
            'clientSecret'            => $this->getApiSecret(),
            'scopes'                  => $this->scopes
        ]);

        return $this->provider;
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
     * @param $apiId
     * @return void
     */
    public function setApiId($apiId)
    {
        $this->apiId = $apiId;
    }

    /**
     * @param $apiSecret
     * @return string
     */
    public function setApiSecret($apiSecret)
    {
        $this->apiSecret = $apiSecret;
    }

    /**
     * @param array $scopes
     * @return void
     */
    public function setScopes(array $scopes)
    {
        $this->scopes = $scopes;
    }

    /**
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @param $redirectUri
     * @return void
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirect = $redirectUri;
    }

    /**
     * @return string
     */
    public function getRedirectUri()
    {
        $this->redirect;
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

}