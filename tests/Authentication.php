<?php

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
}