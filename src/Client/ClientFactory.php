<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Exception\UsageException;

class ClientFactory
{
    const BASIC = 'basic';
    const OAUTH2 = 'oauth2';

    protected $appId;
    protected $appSecret;
    protected $headers;
    protected $cache;


    public function __construct($appId, $appSecret, $headers, $cache)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->headers = $headers;
        $this->cache = $cache;

    }

    public function createClient($type)
    {
        switch ($type) {
            case self::BASIC:
                return new BookboonCurlClient($this->appId, $this->appSecret, $this->headers, $this->cache);
            case self::OAUTH2:
                return new BookboonOauthClient($this->appId, $this->appSecret, $this->headers, $this->cache);
            default:
                throw new UsageException("Invalid Client: $type");
        }

    }
}