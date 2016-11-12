<?php

namespace Bookboon\Api\Client;


use Bookboon\Api\Cache\Cache;
use Bookboon\Api\Exception\ApiAuthenticationException;
use Bookboon\Api\Exception\ApiInvalidStateException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class BookboonOauthClient implements Client
{
    use ClientTrait, ResponseTrait, RequestTrait;

    const AUTHORIZE = '/authorize';
    const ACCESS_TOKEN = '/access_token';

    /** @var  GenericProvider */
    private $provider;

    /** @var  AccessToken */
    private $accessToken;


    /**
     * @param $url
     * @param string $type
     * @param array $variables
     * @param string $contentType
     * @return mixed
     * @throws ApiAuthenticationException
     */
    protected function executeQuery($url, $type = Client::HTTP_GET, $variables = array(), $contentType = 'application/x-www-form-urlencoded')
    {
        try {
            $request = $this->getOauthProvider()->getAuthenticatedRequest(
                $type,
                $url,
                $this->accessToken,
                $variables
            );

            if ($this->accessToken->hasExpired()) {
                $this->provider = $this->provider->getAccessToken('refresh_token', [
                    'refresh_token' => $this->provider->getRefreshToken()
                ]);
            }


            /** @var ResponseInterface*/
            $response = $this->provider->getHttpClient()->send($request);

            return $this->handleResponse($response->getBody()->getContents(), $response->getHeaders(), $response->getStatusCode(), $url);
        }

        catch (IdentityProviderException $e) {
            throw new ApiAuthenticationException("Identity not found");
        }
    }


    /**
     *
     *
     * @param $redirectUri
     * @param array $scopes
     * @param null $appUserId
     * @return string
     */
    public function getAuthorizationUrl($redirectUri, $scopes = [], $appUserId = null)
    {
        $this->provider = new GenericProvider([
            'clientId'                => $this->apiId,
            'clientSecret'            => $this->apiSecret,
            'redirectUri'             => $redirectUri,
            'urlAuthorize'            => self::API_URL . self::AUTHORIZE,
            'scopes'                  => $scopes,
            'urlAccessToken'          => self::API_URL . self::ACCESS_TOKEN,
            'urlResourceOwnerDetails' => 'https://' . self::API_URL . '/_application'
        ]);

        $url = $this->provider->getAuthorizationUrl();

        $_SESSION['oauth2state'] = $this->provider->getState();

        return $url;
    }

    /**
     * @param $code
     * @param $state
     * @return AccessToken
     * @throws ApiInvalidStateException
     */
    public function generateAccessToken($code, $state)
    {
        if (empty($state) || ($state !== $_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);

            throw new ApiInvalidStateException();
        }

        $this->accessToken = $this->provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);

        return $this->accessToken;
    }


    /**
     * @return GenericProvider
     */
    public function getOauthProvider()
    {
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
}