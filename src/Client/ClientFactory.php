<?php
/**
 * Created by PhpStorm.
 * User: ross
 * Date: 07/11/16
 * Time: 15:04
 */

namespace Bookboon\Api\Client;


use Bookboon\Api\Exception\InvalidClientException;


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
            case self::BASIC;
                return new BookboonCurlClient($this->appId, $this->appSecret, $this->headers, $this->cache);
            break;
            case self::OAUTH2;
                return new BookboonOauthClient($this->appId, $this->appSecret, $this->headers, $this->cache);
            break;
            default:
                throw new InvalidClientException("Invalid Client");
        }

    }


}