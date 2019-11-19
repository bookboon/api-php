<?php

namespace Bookboon\Api\Entity;

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\ClientInterface;
use Bookboon\Api\Exception\ApiGeneralException;

class Subscription extends Entity
{

    /**
     * Determines whether api response is valid
     *
     * @param array $array
     * @return bool
     */
    protected function isValid(array $array): bool
    {
        return true;
    }

    /**
     * @param Bookboon $bookboon
     * @param string $email
     * @param bool $hasConsented
     * @param string|null $alias
     * @return boolean
     * @throws \Bookboon\Api\Exception\UsageException
     */
    protected static function add(Bookboon $bookboon, string $email, bool $hasConsented, ?string $alias) : bool
    {
        try {
            $bookboon->rawRequest(
                '/subscriptions',
                [
                    'email' => $email,
                    'alias' => $alias,
                    'hasConsented' => false
                ],
                ClientInterface::HTTP_POST,
                false
            );
        } catch (ApiGeneralException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param Bookboon $bookboon
     * @param string $email
     * @param string|null $alias
     * @return boolean
     * @throws \Bookboon\Api\Exception\UsageException
     */
    protected static function remove(Bookboon $bookboon, string $email, ?string $alias) : bool
    {
        try {
            $bookboon->rawRequest(
                '/subscriptions',
                [
                    'email' => $email,
                    'alias' => $alias
                ],
                ClientInterface::HTTP_DELETE,
                false
            );
        } catch (ApiGeneralException $e) {
            return false;
        }

        return true;
    }

}
