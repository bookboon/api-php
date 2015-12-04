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

use Bookboon\Api\Entity\Book;
use Bookboon\Api\Entity\Category;
use Bookboon\Api\Entity\Question;
use Bookboon\Api\Entity\Review;
use Exception;

if (!function_exists('curl_init')) {
    throw new Exception('Bookboon requires the curl PHP extension');
}
if (!function_exists('json_decode')) {
    throw new Exception('Bookboon requires the json PHP extension');
}

class Bookboon
{
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';

    const HEADER_BRANDING = 'X-Bookboon-Branding';
    const HEADER_ROTATION = 'X-Bookboon-Rotation';
    const HEADER_PREMIUM = 'X-Bookboon-PremiumLevel';
    const HEADER_CURRENCY = 'X-Bookboon-Currency';
    const HEADER_LANGUAGE = 'Accept-Language';
    const HEADER_XFF = 'X-Forwarded-For';


    private $authenticated = array();
    private $headers = array();
    private $url = "bookboon.com/api";
    private $cache = null;

    public static $CURL_REQUESTS = array();

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
     * Bookboon constructor.
     *
     * @param $appid Bookboon API ID
     * @param $appkey Bookboon API KEY
     * @param array $headers in format array("headername" => "value")
     * @throws Exception
     */
    function __construct($appid, $appkey, $headers = array())
    {
        if (empty($appid) || empty($appkey)) {
            throw new Exception('Empty appid or appkey');
        }

        $this->authenticated['appid'] = $appid;
        $this->authenticated['appkey'] = $appkey;
        $this->headers = array_merge(
            array(self::HEADER_XFF => $this->getRemoteAddress()),
            $headers
        );

    }

    /**
     * Set cache instance
     *
     * @param Cache $cache
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Set or override header
     *
     * @param $header
     * @param $value
     */
    public function setHeader($header, $value)
    {
        $this->headers[$header] = $value;
    }

    /**
     * Get specific header
     *
     * @param $header
     * @return bool|string false if header is not set or string value of header
     */
    public function getHeader($header)
    {
        return isset($this->headers[$header]) ? $this->headers[$header] : false;
    }

    /**
     * Get all headers in CURL format
     *
     * @return array
     */
    private function getHeaders()
    {
        $headers = array();
        foreach ($this->headers as $h => $v) {
            $headers[] = $h . ': ' . $v;
        }

        return $headers;
    }

    /**
     * Hashes url with unique values: app id and headers
     *
     * @param $url
     * @return string the hashed key
     */
    public function hash($url)
    {
        $h = $this->headers;
        unset($h[self::HEADER_XFF]);
        return sha1($this->authenticated['appid'] . serialize($h) . $url);
    }

    /**
     * Get Book object
     *
     * @param $bookId guid for book
     * @return Book|bool
     * @throws ApiSyntaxException
     */
    public function getBook($bookId)
    {
        if (self::isValidGUID($bookId) === false) {
            return false;
        }
        return new Book($this->api("/books/$bookId"));
    }

    public function getBookDownloadUrl($bookId, Array $variables, $format = "pdf")
    {
        $variables["format"] = $format;
        $download = $this->api("/books/$bookId/download", array("post" => $variables));
        return $download["url"];
    }

    /**
     * Get Reviews for specified Book
     *
     * @param $bookId
     * @return array of Review objects
     * @throws ApiSyntaxException
     */
    public function getReviews($bookId)
    {
        if (self::isValidGUID($bookId) === false) {
            return false;
        }

        $reviews = $this->api("/books/$bookId/review");
        return Review::getEntitiesFromArray($reviews);
    }

    /**
     * Get Category
     *
     * @param $categoryId
     * @return Category|bool
     * @throws ApiSyntaxException
     */
    public function getCategory($categoryId)
    {
        if (self::isValidGUID($categoryId) === false) {
            return false;
        }

        return new Category($this->api("/categories/$categoryId"));
    }

    public function getCategoryDownloadUrl($categoryId, Array $variables)
    {
        $download = $this->api("/categories/$categoryId/download", array("post" => $variables));
        return $download["url"];
    }

    /**
     * Search
     *
     * @param $query string to search for
     * @param int $limit results to return per page
     * @param int $offset offset of results
     * @return array
     * @throws ApiSyntaxException
     */
    public function getSearch($query, $limit = 10, $offset = 0)
    {
        $search = $this->api("/search", array("get" => array("q" => $query, "limit" => $limit, "offset" => $offset)));
        if (count($search) === 0) {
            return array();
        }

        return Book::getEntitiesFromArray($search);
    }

    /**
     * Recommendations
     *
     * @param array $bookIds array of book ids to base recommendations on, can be empty
     * @param int $limit
     * @return array
     * @throws ApiSyntaxException
     */
    public function getRecommendations(Array $bookIds = array(), $limit = 5)
    {
        $variables["get"] = array("limit" => $limit);
        if (count($bookIds) > 0) {
            for($i=0; $i<count($bookIds); $i++) {
                $variables["get"]["book[$i]"] = $bookIds[$i];
            }
        }
        $recommendations = $this->api("/recommendations", $variables);
        return Book::getEntitiesFromArray($recommendations);
    }

    /**
     * Questions
     *
     * @param array $answerIds array of answer ids, can be empty
     * @return array
     * @throws ApiSyntaxException
     */
    public function getQuestions(Array $answerIds = array())
    {
        $variables = array();
        if (count($answerIds) > 0) {
            $variables["get"] = array();
            for($i=0; $i<count($answerIds); $i++) {
                $variables["get"]["answer[$i]"] = $answerIds[$i];
            }
        }

        $questions = $this->api("/questions", $variables);
        return Question::getEntitiesFromArray($questions);
    }

    /**
     * Determine whether cache should be attempted
     * 
     * @param $variables
     * @return bool
     */
    protected function isCachable($variables)
    {
        if (isset($variables['get']) || empty($variables)) {
            if (is_object($this->cache) && count($variables) <= 1)
                return true;
        }
        return false;
    }

    /**
     * Prepares the call to the api and if enabled tries cache provider first for GET calls
     *
     * @param string $relativeUrl The url relative to the address. Must begin with '/'
     * @param array $methodVariables must contain subarray called either 'post' or 'get' depend on HTTP method
     * @param boolean $cacheQuery manually disable object cache for query
     * @return array results of call
     * @throws ApiSyntaxException
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

    /**
     * Useful GUID validator to validate input in scripts
     *
     * @param string $guid GUID to validate
     * @return boolean true if valid, false if not
     */
    public static function isValidGUID($guid)
    {
        return preg_match("/^([0-9a-fA-F]){8}-([0-9a-fA-F]){4}-([0-9a-fA-F]){4}-([0-9a-fA-F]){4}-([0-9a-fA-F]){12}$/", $guid) == true;
    }

    /**
     * Returns the remote address either directly or if set XFF header value
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
                if (strcasecmp($k, "x-forwarded-for"))
                    continue;

                $hostname = explode(",", $v);
                $hostname = trim($hostname[0]);
                break;
            }
        }

        return $hostname;
    }

    private function reportDeveloperInfo($request, $data)
    {
        self::$CURL_REQUESTS[] = array(
            "curl" => $request,
            "data" => $data
        );
    }

}
