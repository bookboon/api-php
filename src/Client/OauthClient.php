<?php
/**
 * Created by PhpStorm.
 * User: ross
 * Date: 07/11/16
 * Time: 14:38
 */

namespace Bookboon\Api\Client;


use Bookboon\Api\Cache\Cache;
use Bookboon\Api\Exception\ApiAccessTokenExpired;
use Bookboon\Api\Exception\ApiAuthenticationException;
use Bookboon\Api\Exception\ApiInvalidStateException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BookboonOauthClient
 * @package Bookboon\Api\Client
 */
class OauthClient implements Client
{
    use ClientTrait, ResponseTrait, RequestTrait;

    const AUTHORIZE = '/authorize';
    const ACCESS_TOKEN = '/access_token';

    /** @var  AccessToken */
    private $accessToken;

    /** @var array */
    protected $scopes;

    /** @var  string */
    protected $redirect;

    /** @var  string */
    protected $appUserId;

    /** @var  GenericProvider */
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
     * @internal param array $scope
     */
    public function __construct($apiId, $apiSecret, Headers $headers, $redirectUri, array $scopes, $appUserId, $cache = null)
    {
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
    protected function executeQuery($url, $type = Client::HTTP_GET, $variables = array(), $contentType = 'application/x-www-form-urlencoded')
    {

        $accessToken = $this->getAccessToken();
        $options = [];
        $url = 'https://' . $url;

        if (count($variables) > 0) {
            $options['form_params'] = $variables;
        }

        $headers = $this->headers->getHeadersArray();

        if (count($headers) > 0 ) {
            $options['headers']  = $headers;

        }

        try {
            $request = $this->getProvider()->getAuthenticatedRequest(
                $type,
                $url,
                $accessToken
            );

            if ($accessToken->hasExpired()) {
                throw new ApiAccessTokenExpired("Bookboon API Access Token Has Now Expired");

            }

            /** @var ResponseInterface*/
            $response = $this->provider->getHttpClient()->send($request, $options);

            return $this->handleResponse($response->getBody()->getContents(), $response->getHeaders(), $response->getStatusCode(), $url);
        }

        catch (IdentityProviderException $e) {
            throw new ApiAuthenticationException("Identity not found");
        }
    }

    /**
     * @return string
     * @internal param $redirectUri
     * @internal param array $scopes
     * @internal param null $appUserId
     */
    public function getAuthorizationUrl()
    {
        $provider = $this->getProvider();

        $options = [];

        if (false === is_null($this->appUserId)) {
            $options['app_user_id'] = $this->appUserId;
        }

        $url = $provider->getAuthorizationUrl($options);

        return $url;
    }

    /**
     * @param $code
     * @param $stateParameter
     * @param $stateSession
     * @return AccessToken
     * @throws ApiAuthenticationException
     * @throws ApiInvalidStateException
     * @internal param $state
     * @internal param $redirectUri
     * @internal param array $scopes
     * @internal param $appId
     */
    public function requestAccessToken($code, $stateParameter, $stateSession)
    {
        $provider = $this->getProvider();

        $options = ['code' => $code];

        if (false === is_null($this->appUserId)) {
            $options['app_user_id'] = $this->appUserId;
        }

        try {
            $this->accessToken = $provider->getAccessToken('authorization_code', $options);
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
     * @return GenericProvider
     * @internal param $redirectUri
     * @internal param $scopes
     * @internal param null $appId
     */
    public function getProvider()
    {
        if ($this->provider instanceof GenericProvider) {
            return $this->provider;
        }

        $this->provider =  new GenericProvider([
            'clientId'                => $this->getApiId(),
            'clientSecret'            => $this->getApiSecret(),
            'redirectUri'             => $this->redirect,
            'urlAuthorize'            => 'https://' . self::API_URL . self::AUTHORIZE,
            'scopes'                  => $this->scopes,
            'urlAccessToken'          => 'https://' . self::API_URL . self::ACCESS_TOKEN,
            'urlResourceOwnerDetails' => 'https://bookboon.com/api/_application'
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
        // TODO: Implement getResponseHeader() method.
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