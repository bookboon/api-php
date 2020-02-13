<?php

namespace Bookboon\Api\Client;


use ArrayAccess;

class Headers implements ArrayAccess
{
    const HEADER_BRANDING = 'X-Bookboon-Branding';
    const HEADER_ROTATION = 'X-Bookboon-Rotation';
    const HEADER_PREMIUM = 'X-Bookboon-PremiumLevel';
    const HEADER_CURRENCY = 'X-Bookboon-Currency';
    const HEADER_LANGUAGE = 'Accept-Language';
    const HEADER_XFF = 'X-Forwarded-For';

    private $headers = [];

    public function __construct(array $headers = [])
    {
        $this->headers = $headers;
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

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return bool true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->headers[strtolower($offset)]);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->headers[strtolower($offset)] ?? null;
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->headers[strtolower($offset)] = $value;
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->headers[strtolower($offset)]);
    }
}
