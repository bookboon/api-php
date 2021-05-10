<?php

namespace Bookboon\Api\Client\Oauth;

use Bookboon\Api\Exception\UsageException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class BookboonProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    private $host = 'bookboon.com';
    private $protocol = 'https';

    protected $scope = ['basic'];

    protected $requestOptions = [];

    public function __construct(array $options = [], array $collaborators = [])
    {
        if (isset($options['baseUri']) && $options['baseUri'] !== "") {
            $parts = explode('://', $options['baseUri']);
            $this->protocol = $parts[0];
            $this->host = $parts[1];
        }

        if ($this->protocol !== 'http' && $this->protocol !== 'https') {
            throw new UsageException('Invalid protocol');
        }

        $this->requestOptions = $options['requestOptions'] ?? [];

        parent::__construct($options, $collaborators);
    }


    /**
     * Returns the base URL for authorizing a client.
     *
     * Eg. https://oauth.service.com/authorize
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return "$this->protocol://$this->host/login/authorize";
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * Eg. https://oauth.service.com/token
     *
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return "$this->protocol://$this->host/login/access_token";
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     *
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return "$this->protocol://$this->host/login/userinfo";
    }


    public function generateRandomState()
    {
        return $this->getRandomState();
    }

    /**
     * @param mixed $grant
     * @param array $options
     * @return AccessTokenInterface
     * @throws IdentityProviderException
     */
    public function getAccessToken($grant, array $options = [])
    {
        if (!isset($options['scope'])) {
            $options['scope'] = $this->scope;
        }

        if (is_array($options['scope'])) {
            $options['scope'] = implode($this->getScopeSeparator(), $options['scope']);
        }

        return parent::getAccessToken($grant, $options);
    }

    /**
     * Returns the default scopes used by this provider.
     *
     * This should only be the scopes that are required to request the details
     * of the resource owner, rather than all the available scopes.
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return $this->scope;
    }

    /**
     * Checks a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param ResponseInterface $response
     * @param array|string $data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['errors'])) {
            throw new IdentityProviderException(
                $data['errors'][0]['title'] ?? $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response->getBody()
            );
        }
    }

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     *
     * @param  array $response
     * @param  AccessToken $token
     * @return ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new BookboonResourceOwner($response);
    }

    protected function getScopeSeparator()
    {
        return ' ';
    }

    /**
     * Sends a request instance and returns a response instance.
     *
     * WARNING: This method does not attempt to catch exceptions caused by HTTP
     * errors! It is recommended to wrap this method in a try/catch block.
     *
     * @param  RequestInterface $request
     * @return ResponseInterface
     */
    public function getResponse(RequestInterface $request)
    {
        return $this->getHttpClient()->send($request, $this->requestOptions);
    }

    protected function getDefaultHeaders()
    {
        $headers = parent::getDefaultHeaders();

        foreach ($this->getParentRequestHeaders() as $key => $value) {
            if (stripos($key, 'x-b3-') !== false || stripos($key, 'x-request-id') !== false) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    protected function getParentRequestHeaders(): array
    {
        if (!function_exists('apache_request_headers')) {
            $out = [];

            foreach ($_SERVER as $key => $value) {
                if (strpos($key, "HTTP_") === 0) {
                    $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
                    $out[$key] = $value;
                } else {
                    $out[$key] = $value;
                }
            }
            return $out;
        }

        return apache_request_headers();
    }

    /**
     * Returns the list of options that can be passed to the HttpClient
     *
     * @param array $options An array of options to set on this provider.
     *     Options include `clientId`, `clientSecret`, `redirectUri`, and `state`.
     *     Individual providers may introduce more options, as needed.
     * @return array The options to pass to the HttpClient constructor
     */
    protected function getAllowedClientOptions(array $options)
    {
        $client_options = ['timeout', 'proxy', 'handler'];

        // Only allow turning off ssl verification if it's for a proxy
        if (!empty($options['proxy'])) {
            $client_options[] = 'verify';
        }

        return $client_options;
    }
}
