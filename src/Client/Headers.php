<?php

namespace Bookboon\Api\Client;


class Headers
{
    const HEADER_BRANDING = 'X-Bookboon-Branding';
    const HEADER_ROTATION = 'X-Bookboon-Rotation';
    const HEADER_PREMIUM = 'X-Bookboon-PremiumLevel';
    const HEADER_CURRENCY = 'X-Bookboon-Currency';
    const HEADER_LANGUAGE = 'Accept-Language';
    const HEADER_XFF = 'X-Forwarded-For';

    private $headers = array();

    public function __construct()
    {
        $this->set(static::HEADER_XFF, $this->getRemoteAddress());
    }

    /**
     * Set or override header.
     *
     * @param string $header
     * @param string $value
     */
    public function set($header, $value)
    {
        $this->headers[$header] = $value;
    }

    /**
     * Get specific header.
     *
     * @param $header
     *
     * @return bool|string false if header is not set or string value of header
     */
    public function get($header)
    {
        return isset($this->headers[$header]) ? $this->headers[$header] : false;
    }

    /**
     * Get all headers in CURL format.
     *
     * @return array
     */
    public function getAll()
    {
        $headers = array();
        foreach ($this->headers as $h => $v) {
            $headers[] = $h.': '.$v;
        }

        return $headers;
    }

    /**
     * @return array
     */
    public function getHeadersArray()
    {
        return $this->headers;
    }

    /**
     * Returns the remote address either directly or if set XFF header value.
     *
     * @return string The ip address
     */
    private function getRemoteAddress()
    {
        $hostname = false;

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $hostname = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
        }

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            foreach ($headers as $k => $v) {
                if (strcasecmp($k, 'x-forwarded-for')) {
                    continue;
                }

                $hostname = explode(',', $v);
                $hostname = trim($hostname[0]);
                break;
            }
        }

        return $hostname;
    }
}