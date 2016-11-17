<?php
/**
 * Created by PhpStorm.
 * User: ross
 * Date: 07/11/16
 * Time: 14:38
 */

namespace Bookboon\Api\Client;


use Bookboon\Api\Cache\Cache;
use Bookboon\Api\Exception\IdentityException;
use Bookboon\Api\Exception\InvalidStateException;
use Bookboon\Api\Exception\UsageException;
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
        $this->setApiId($apiId);
        $this->setApiSecret($apiSecret);
        $this->setHeaders($headers);
        $this->setCache($cache);
        $this->setRedirectUri($redirectUri);
        $this->setScopes($scopes);
        $this->setAppUserId($appUserId);
    }

    /**
     * @param $url
     * @param string $type
     * @param array $variables
     * @param string $contentType
     * @return mixed
     * @throws IdentityException
     */
    protected function executeQuery($url, $type = Client::HTTP_GET, $variables = array(), $contentType = 'application/x-www-form-urlencoded')
    {

        $this->accessToken = unserialize($_SESSION['access_token']);
        $options = [];
        $url = 'http://' . $url;

        if (count($variables) > 0) {

            $options['form_params'] = $variables;
        }

        try {
            $request = $this->getProvider()->getAuthenticatedRequest(
                $type,
                $url,
                $this->accessToken
            );

            if ($this->accessToken->hasExpired()) {
                $this->accessToken = $this->getProvider()->getAccessToken('refresh_token', [
                    'refresh_token' => $this->accessToken->getRefreshToken()
                ]);
            }

            /** @var ResponseInterface*/
            $response = $this->provider->getHttpClient()->send($request, $options);

            return $this->handleResponse($response->getBody()->getContents(), $response->getHeaders(), $response->getStatusCode(), $url);
        }

        catch (IdentityProviderException $e) {
            throw new IdentityException("Identity not found");
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

        $url = $provider->getAuthorizationUrl();

        $_SESSION['oauth2state'] = $provider->getState();

        return $url;
    }

    /**
     * @param $code
     * @param $state
     * @return AccessToken
     * @internal param $redirectUri
     * @internal param array $scopes
     * @internal param $appId
     */
    public function requestAccessToken($code, $state)
    {
        if (empty($state) || ($state !== $_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        }

        $provider = $this->getProvider();

        $this->accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);

        $_SESSION['access_token'] = serialize($this->accessToken);

        return $this->accessToken;
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
            'urlAuthorize'            => 'http://' . self::API_URL . self::AUTHORIZE,
            'scopes'                  => $this->scopes,
            'urlAccessToken'          => 'http://' . self::API_URL . self::ACCESS_TOKEN,
            'urlResourceOwnerDetails' => 'http://bookboon.com/api/_application'
        ]);

        return $this->provider;
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
}