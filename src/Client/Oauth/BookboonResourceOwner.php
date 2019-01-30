<?php

namespace Bookboon\Api\Client\Oauth;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class BookboonResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    /**
     * Raw response
     *
     * @var array
     */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = [])
    {
        $this->response = $response;
    }
    /**
     * Get resource owner id
     *
     * @return string
     */
    public function getId()
    {
        return $this->getValueByKey($this->response, 'application.id');
    }

    /**
     * @return array
     */
    public function getApplication()
    {
        return $this->getValueByKey($this->response, 'application');
    }

    /**
     * Get resource owner name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getValueByKey($this->response, 'user.name');
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->getValueByKey($this->response, 'user.roles');
    }

    /**
     * @return string[]
     */
    public function getObjectAccessApplication()
    {
        $applications = $this->getValueByKey($this->response, 'user.objectAccess.application', []);

        $applicationIds = array_map(
            function ($app) {
                return $app['id'];
            },
            $applications
        );

        $applicationIds[] = $this->getId();

        return $applicationIds;
    }

    /**
     * @return array
     */
    public function getScopes()
    {
        return $this->getValueByKey($this->response, 'grantedScopes');
    }

    /**
     * @return boolean
     */
    public function hasErrored()
    {
        return $this->getValueByKey($this->response, 'status') !== null;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
