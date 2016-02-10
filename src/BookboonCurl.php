<?php
namespace Bookboon\Api;

/*
 *  Copyright 2014 Bookboon.com Ltd.
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 */

class BookboonCurl
{
    public static $CURL_OPTS = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => 'bookboon-php-2.1',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2
    );

    /**
     * Prepares the call to the api and if enabled tries cache provider first for GET calls
     *
     * @param string $relativeUrl The url relative to the address. Must begin with '/'
     * @param array $methodVariables must contain subarray called either 'post' or 'get' depend on HTTP method
     * @param boolean $cacheQuery manually disable object cache for query
     * @return array results of call
     * @throws ApiSyntaxException
     * @deprecated
     */
    public function api($relativeUrl, $methodVariables = array(), $cacheQuery = true)
    {
        $queryUrl = $this->url . $relativeUrl;
        $type = Bookboon::HTTP_GET;
        $postVariables = array();

        if (isset($methodVariables['get'])) {
            $queryUrl .= "?" . http_build_query($methodVariables['get']);
        }

        if (isset($methodVariables['post'])) {
            $postVariables = $methodVariables['post'];
            $type = Bookboon::HTTP_POST;
        }

        if (substr($relativeUrl, 0, 1) !== '/') {
            throw new ApiSyntaxException('Location must begin with forward slash');
        }

        if ($this->isCachable($methodVariables) && $cacheQuery) {
            $hash = $this->hash($queryUrl);
            $result = $this->cache->get($hash);

            if ($result === false) {
                $result = $this->query($queryUrl, $type, $postVariables);
                $this->cache->save($hash, $result);
            } else {
                $this->reportDeveloperInfo(array(
                    "total_time" => 0,
                    "http_code" => 'memcache',
                    "size_download" => mb_strlen(json_encode($result)),
                    "url" => "https://" . $queryUrl
                ), array());
            }
            return $result;
        }

        return $this->query($queryUrl, $type, $postVariables);
    }

    /**
     * Makes the actual query call to the remote api.
     *
     * @param string $url The url relative to the address
     * @param string $type  Bookboon::HTTP_GET or  Bookboon::HTTP_POST
     * @param array $variables array of post variables (key => value)
     * @return array results of call, json decoded
     * @throws ApiSyntaxException
     * @throws AuthenticationException
     * @throws GeneralApiException
     * @throws NotFoundException
     */
    private function query($url, $type = Bookboon::HTTP_GET, $variables = array())
    {

        $http = curl_init();

        curl_setopt($http, CURLOPT_URL, "https://" . $url);
        curl_setopt($http, CURLOPT_USERPWD, $this->authenticated['appid'] . ":" . $this->authenticated['appkey']);
        curl_setopt($http, CURLOPT_HTTPHEADER, $this->getHeaders());

        if ($type == Bookboon::HTTP_POST) {
            curl_setopt($http, CURLOPT_POST, count($variables));
            curl_setopt($http, CURLOPT_POSTFIELDS, http_build_query($variables));
        }

        foreach (self::$CURL_OPTS as $key => $val) {
            curl_setopt($http, $key, $val);
        }
        $response = curl_exec($http);

        $headerSize = curl_getinfo($http, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $body = json_decode(substr($response, $headerSize), true);

        $httpStatus = curl_getinfo($http, CURLINFO_HTTP_CODE);

        $this->reportDeveloperInfo(curl_getinfo($http), $variables);

        curl_close($http);

        if ($httpStatus >= 400) {
            switch ($httpStatus) {
                case 400:
                    throw new ApiSyntaxException($body['message']);
                case 401:
                case 403:
                    throw new AuthenticationException("Invalid credentials");
                case 404:
                    throw new NotFoundException($url);
                    break;
                default:
                    throw new GeneralApiException($body['message']);
            }
        }

        if ($httpStatus >= 301 && $httpStatus <= 303) {
            $body['url'] = '';
            foreach (explode("\n", $header) as $h) {
                if (strpos($h, "Location") === 0) {
                    $body['url'] = trim(str_replace("Location: ", "", $h));
                }
            }
        }

        return $body;
    }
}