<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Client\Oauth\BookboonProvider;
use Bookboon\Api\Client\Oauth\OauthGrants;
use Bookboon\Api\Exception\ApiAccessTokenExpired;
use Bookboon\Api\Exception\ApiAuthenticationException;
use Bookboon\Api\Exception\ApiGeneralException;
use Bookboon\Api\Exception\ApiInvalidStateException;
use Bookboon\Api\Exception\UsageException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use GuzzleHttp\TransferStats;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Class BookboonOauthClient
 * @package Bookboon\Api\Client
 */
class OauthClient implements ClientInterface
{
    use ClientTrait, ResponseTrait, RequestTrait, HashTrait;

    const C_VERSION = '2.2';

    /** @var string  */
    protected $_apiUri;

    /** @var AccessTokenInterface|null */
    private $accessToken;

    /** @var string|null */
    protected $act;

    /** @var BookboonProvider */
    protected $provider;

    /** @var array  */
    protected $requestOptions = [];

    /**
     * ClientCommon constructor.
     * @param string $apiId
     * @param string $apiSecret
     * @param Headers $headers
     * @param array $scopes
     * @param CacheInterface|null $cache
     * @param string|null $redirectUri
     * @param string|null $appUserId
     * @param string|null $authServiceUri
     * @param string|null $apiUri
     * @param LoggerInterface|null $logger
     * @param array $clientOptions
     * @throws UsageException
     */
    public function __construct(
        string $apiId,
        string $apiSecret,
        Headers $headers,
        array $scopes,
        CacheInterface $cache = null,
        ?string $redirectUri = null,
        ?string $appUserId = null,
        ?string $authServiceUri = null,
        ?string $apiUri = null,
        LoggerInterface $logger = null,
        array $clientOptions = []
    ) {
        if (empty($apiId)) {
            throw new UsageException("Client id is required");
        }

        $clientOptions = array_merge(
            $clientOptions,
            [
                'clientId'      => $apiId,
                'clientSecret'  => $apiSecret,
                'scope'         => $scopes,
                'redirectUri'   => $redirectUri,
                'baseUri'       => $authServiceUri,
            ]
        );

        if ($logger !== null) {
            $this->requestOptions = [
                'on_stats' => function (TransferStats $stats) use ($logger) {
                    if ($stats->hasResponse()) {
                        $size = $stats->getHandlerStat('size_download') ?? 0;
                        $statusCode = $stats->getResponse() ? $stats->getResponse()->getStatusCode() : 0;

                        $logger->info(
                            "Api request \"{$stats->getRequest()->getMethod()} {$stats->getRequest()->getRequestTarget()} HTTP/{$stats->getRequest()->getProtocolVersion()}\" {$statusCode} - {$size} - {$stats->getTransferTime()}"
                        );
                    } else {
                        $logger->error(
                            "Api request: No response received with error {$stats->getHandlerErrorData()}"
                        );
                    }
                }
            ];
        }

        $clientOptions['requestOptions'] = $this->requestOptions;
        $this->provider = new BookboonProvider($clientOptions);

        $this->setApiId($apiId);
        $this->setCache($cache);
        $this->setHeaders($headers);
        $this->setAct($appUserId);

        $this->_apiUri = $this->parseUriOrDefault($apiUri);
    }

    /**
     * @param string $uri
     * @param string $type
     * @param array $variables
     * @param string $contentType
     * @return BookboonResponse
     * @throws ApiAccessTokenExpired
     * @throws ApiAuthenticationException
     * @throws \Bookboon\Api\Exception\ApiGeneralException
     * @throws \Bookboon\Api\Exception\ApiNotFoundException
     * @throws \Bookboon\Api\Exception\ApiSyntaxException
     */
    protected function executeQuery(
        string $uri,
        string $type = ClientInterface::HTTP_GET,
        array $variables = [],
        string $contentType = ClientInterface::CONTENT_TYPE_JSON
    ) : BookboonResponse {
        if (!($this->getAccessToken() instanceof AccessTokenInterface)) {
            throw new ApiAuthenticationException("Not authenticated");
        }

        $options = array_merge(
            [
                'allow_redirects' => false,
                'headers' => $this->getHeaders()->getHeadersArray()
            ],
            $this->requestOptions
        );

        $options['headers']['User-Agent'] = $this->getUserAgentString();

        if (count($variables) > 0
            && in_array($type, [ClientInterface::HTTP_POST, ClientInterface::HTTP_DELETE, ClientInterface::HTTP_PUT], true)) {
            $postType = $contentType === ClientInterface::CONTENT_TYPE_JSON ? 'json' : 'form_params';
            $options[$postType] = $variables;
        }

        $accessToken = $this->getAccessToken();

        if ($accessToken === null) {
            throw new ApiAccessTokenExpired("Access token is null");
        }

        try {
            $request = $this->provider->getAuthenticatedRequest(
                $type,
                $uri,
                $accessToken
            );

            if ($accessToken->hasExpired()) {
                throw new ApiAccessTokenExpired("Bookboon API Access Token Has Now Expired");

            }
        } catch (IdentityProviderException $e) {
            throw new ApiAuthenticationException("Identity not found");
        }

        try {
            $response = $this->provider->getHttpClient()->send($request, $options);
        } catch (RequestException $e) {
            if (false === $e->hasResponse()) {
                throw new ApiGeneralException('No response. Curl error code: ' . $e->getCode() );
            }

            $response = $e->getResponse();
        }

        if ($response === null) {
            throw new ApiGeneralException('Response is null');
        }

        $normalizedHeaders = $this->normalizeHeaders($response->getHeaders());
        $body = $response->getBody()->getContents();

        if ($response->getStatusCode() >= 400) {
            $this->handleErrorResponse(
                $body,
                $normalizedHeaders,
                $response->getStatusCode(),
                $uri
            );
        }

        return new BookboonResponse(
            $body,
            $response->getStatusCode(),
            $normalizedHeaders
        );
    }

    /**
     * @param array $options
     * @return string
     */
    public function getAuthorizationUrl(array $options = []) : string
    {
        $provider = $this->provider;

        if (null != $this->act && false === isset($options['act'])) {
            $options['act'] = $this->act;
        }

        return $provider->getAuthorizationUrl($options);
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
        $provider = $this->provider;

        if ($type == OauthGrants::AUTHORIZATION_CODE && !isset($options["code"])) {
            throw new UsageException("This oauth flow requires a code");
        }

        try {
            $this->accessToken = $provider->getAccessToken($type, $options);
        }

        catch (IdentityProviderException $e) {
            //TODO: Parse and send this with exception (string) $e->getResponseBody()->getBody()
            throw new ApiAuthenticationException("Authorization Failed");
        }

        return $this->accessToken;
    }


    /**
     * @param AccessTokenInterface $accessToken
     * @return AccessTokenInterface
     * @throws IdentityProviderException
     */
    public function refreshAccessToken(AccessTokenInterface $accessToken) : AccessTokenInterface
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
     * @param AccessTokenInterface $accessToken
     * @return void
     * @throws ApiAccessTokenExpired
     */
    public function setAccessToken(AccessTokenInterface $accessToken) : void
    {
        if ($accessToken->hasExpired()) {
            throw new ApiAccessTokenExpired("The api access token has expired");
        }

        $this->accessToken = $accessToken;
    }

    /**
     * @param string $stateParameter
     * @param string $stateSession
     * @return boolean
     * @throws ApiInvalidStateException
     */
    public function isCorrectState(string $stateParameter, string $stateSession) : bool
    {
        if (empty($stateParameter) || ($stateParameter !== $stateSession)) {
            throw new ApiInvalidStateException("State is invalid");
        }

        return true;
    }


    /**
     * Return specific header value from string of headers.
     *
     * @param array $headers
     *
     * @return array
     */
    protected function normalizeHeaders(array $headers) : array
    {
        $returnHeaders = [];
        foreach ($headers as $key => $header) {
            $returnHeaders[$key] = $header[0] ?? $header;
        }

        return $returnHeaders;
    }

    public function setAct(?string $act) : void
    {
        $this->act = $act;
    }

    public function getAct() : ?string
    {
        return $this->act;
    }

    public function getAccessToken() : ?AccessTokenInterface
    {
        return $this->accessToken;
    }

    protected function getComponentVersion() : string
    {
        return self::C_VERSION;
    }

    protected function getBaseApiUri() : string
    {
        return $this->_apiUri;
    }
}
