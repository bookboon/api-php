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

    private $headers = [];

    public function __construct()
    {
        $this->set(static::HEADER_XFF, $this->getRemoteAddress() ?? '');
    }

    /**
     * Set or override header.
     *
     * @param string $header
     * @param string $value
     * @return void
     */
    public function set(string $header, string $value) : void
    {
        $this->headers[$header] = $value;
    }

    /**
     * Get specific header.
     *
     * @param string $header
     *
     * @return string|null false if header is not set or string value of header
     */
    public function get(string $header) : ?string
    {
        return $this->headers[$header] ?? null;
    }

    /**
     * Get all headers in CURL format.
     *
     * @return array
     */
    public function getAll() : array
    {
        $headers = [];
        foreach ($this->headers as $h => $v) {
            $headers[] = $h.': '.$v;
        }

        return $headers;
    }

    /**
     * @return array
     */
    public function getHeadersArray() : array
    {
        return $this->headers;
    }

    /**
     * Returns the remote address either directly or if set XFF header value.
     *
     * @return string|null The ip address
     */
    private function getRemoteAddress() : ?string
    {
        $hostname = null;

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $hostname = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);

            if (false === $hostname) {
                $hostname = null;
            }
        }

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();

            if ($headers === false) {
                return $hostname;
            }

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
