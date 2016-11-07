<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Exception\ApiGeneralException;
use Bookboon\Api\Exception\ApiTimeoutException;

class BookboonCurlClient implements Client
{
    use ClientTrait, ResponseTrait, RequestTrait;

    protected static $CURL_REQUESTS;

    public static $CURL_OPTS = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => 'bookboon-php-3',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    );

    /**
     * Makes the actual query call to the remote api.
     *
     * @param string $url The url relative to the address
     * @param string $type Bookboon::HTTP_GET or  Bookboon::HTTP_POST
     * @param array $variables array of post variables (key => value)
     * @oaram string $contentType
     *
     * @param string $contentType
     * @return array
     * @throws ApiGeneralException
     * @throws ApiTimeoutException
     */
    protected function executeQuery($url, $type = self::HTTP_GET, $variables = array(), $contentType = self::CONTENT_TYPE_FORM)
    {
        $http = curl_init();
        $headers = $this->getHeaders()->getAll();

        if ($type == self::HTTP_POST) {
            $encodedVariables = $this->encodeByContentType($variables, $contentType);
            $headers[] = "Content-Type: $contentType";
            $headers[] = 'Content-Length: ' . sizeof($encodedVariables);

            curl_setopt($http, CURLOPT_POST, true);
            curl_setopt($http, CURLOPT_POSTFIELDS, $encodedVariables);
        }

        curl_setopt($http, CURLOPT_URL, "https://$url");
        curl_setopt($http, CURLOPT_USERPWD, $this->apiId . ':' . $this->apiSecret);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);

        foreach (self::$CURL_OPTS as $key => $val) {
            curl_setopt($http, $key, $val);
        }

        $response = curl_exec($http);
        $headersSize = curl_getinfo($http, CURLINFO_HEADER_SIZE);
        $httpStatus = curl_getinfo($http, CURLINFO_HTTP_CODE);

        $this->reportDeveloperInfo(curl_getinfo($http), $variables);

        if (curl_errno($http)) {
            if (curl_errno($http) == 28) {
                throw new ApiTimeoutException();
            }
            throw new ApiGeneralException('Curl error number ' . curl_errno($http));
        }

        curl_close($http);

        return $this->handleResponse(substr($response, $headersSize), substr($response, 0, $headersSize), $httpStatus, $url);
    }

    /**
     * Return specific header value from string of headers.
     *
     * @param string $headers
     * @param string $name
     *
     * @return string result
     */
    protected function getResponseHeader($headers, $name)
    {
        foreach (explode("\n", $headers) as $header) {
            if (strpos($header, $name) === 0) {
                return trim(str_replace("$name: ", '', $header));
            }
        }

        return '';
    }

    protected function reportDeveloperInfo($request, $data)
    {
        self::$CURL_REQUESTS[] = array(
            'curl' => $request,
            'data' => $data,
        );
    }
}