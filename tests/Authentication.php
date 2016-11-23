<?php

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\Oauth\OauthGrants;


class Authentication
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
        $bookboon = Bookboon::create(self::getApiId(), self::getApiSecret(), array('basic', 'download_book.pdf', 'download_category'));
        $bookboon->getClient()->requestAccessToken(null, OauthGrants::CLIENT_CREDENTIALS);

        return $bookboon;
    }
}