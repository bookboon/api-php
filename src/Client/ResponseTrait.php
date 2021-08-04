<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Exception\ApiAuthenticationException;
use Bookboon\Api\Exception\ApiGeneralException;
use Bookboon\Api\Exception\ApiNotFoundException;
use Bookboon\Api\Exception\ApiSyntaxException;

trait ResponseTrait
{
    /**
     * @param string $body
     * @param array $headers
     * @param int $status
     * @param string $url
     *
     * @return void
     *
     * @throws ApiSyntaxException
     * @throws ApiAuthenticationException
     * @throws ApiGeneralException
     * @throws ApiNotFoundException
     */
    protected function handleErrorResponse(string $body, array $headers, int $status, string $url): void
    {
        switch ($status) {
            case 400:
            case 405:
                $returnArray = json_decode($body, true);
                throw new ApiSyntaxException($this->getFirstError($returnArray), $status);
            case 401:
            case 403:
                $returnArray = json_decode($body, true);
                throw new ApiAuthenticationException($this->getFirstError($returnArray, 'Invalid credentials'), $status);
            case 410:
            case 404:
                throw new ApiNotFoundException($url, $status);
            default:
                $returnArray = json_decode($body, true);
                throw new ApiGeneralException($this->generalExceptionMessage($returnArray ?? [], $headers), $status);
        }
    }

    protected function getFirstError(?array $errors, string $default = '') : string
    {
        if ($errors !== null && isset($errors['errors']) && count($errors['errors']) > 0) {
            $message = $errors['errors'][0]['title'] ?? '';

            if (isset($errors['errors'][0]['detail'])) {
                $message .= ": {$errors['errors'][0]['detail']}";
            }

            return $message;
        }

        return $default;
    }

    /**
     * @param array $responseArray
     * @param array $headers
     * @return string
     */
    protected function generalExceptionMessage(array $responseArray, array $headers)
    {
        $message = $this->getFirstError($responseArray);

        $xVarnish = $this->getResponseHeader($headers, 'X-Varnish');
        if (!empty($xVarnish)) {
            $message .= "    X-Varnish: $xVarnish";
        }

        $apiVersion = $this->getResponseHeader($headers, 'X-API-Version');
        if (!empty($apiVersion)) {
            $message .= "    X-API-Version: $apiVersion";
        }

        return $message;
    }

    /**
     * Return specific header value from string of headers.
     *
     * @param array $headers
     * @param string $name
     *
     * @return string result
     */
    protected function getResponseHeader(array $headers, string $name) : string
    {
        return $headers[$name] ?? '';
    }
}
