<?php

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\Oauth\OauthGrants;


class Helpers
{
    public static function getApiId()
    {
        return getenv('BOOKBOON_API_ID');
    }

    public static function getApiSecret()
    {
        return getenv('BOOKBOON_API_KEY');
    }

    public static function getBookboon()
    {
        $bookboon = Bookboon::create(self::getApiId(), self::getApiSecret(), ['basic', 'download_book.pdf', 'download_category']);
        $bookboon->getClient()->requestAccessToken([], OauthGrants::CLIENT_CREDENTIALS);

        return $bookboon;
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method
     *
     * @return mixed Method return
     */
    public static function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}