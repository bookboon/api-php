<?php

namespace Bookboon\Api\Client\Oauth;


use App\Security\BookboonResourceOwner;
use Bookboon\Api\Exception\UsageException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class BookboonProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    private $host = 'bookboon.com';
    private $protocol = 'https';

    protected $scope = ['basic'];

    public function __construct(array $options = [], array $collaborators = [])
    {
        if (isset($options['baseUri']) && $options['baseUri'] != "") {
            $parts = explode('://', $options['baseUri']);
            $this->protocol = $parts[0];
            $this->host = $parts[1];
        }

        if ($this->protocol != 'http' && $this->protocol != 'https') {
            throw new UsageException('Invalid protocol');
        }

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
        return $this->protocol . "://" . $this->host . "/login/authorize";
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
        return $this->protocol . "://" . $this->host . "/login/access_token";
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     *
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->protocol . "://" . $this->host . "/login/userinfo";
    }


    public function generateRandomState()
    {
        return $this->getRandomState();
    }

    /**
     * @param mixed $grant
     * @param array $options
     * @return AccessToken
     */
    public function getAccessToken($grant, array $options = [])
    {
        if (!isset($options['scope'])) {
            $options['scope'] = $this->scope;
        }

        if (is_array($options['scope'])) {
            $options['scope'] = join($this->getScopeSeparator(), $options['scope']);
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
        if (isset($data['error'])) {
            throw new IdentityProviderException(
                $data['error'] ?: $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
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
}
