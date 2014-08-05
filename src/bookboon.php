<?php
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

if (!function_exists('curl_init')) {
   throw new Exception('Bookboon requires the curl PHP extension');
}
if (!function_exists('json_decode')) {
   throw new Exception('Bookboon requires the json PHP extension');
}

class Bookboon {

   private $authenticated = array();
   private $headers = array();
   private $url = "bookboon.com/api";
   private $cache_class_name= "";
   private $cache = null;
   
   public static $CURL_OPTS = array(
       CURLOPT_CONNECTTIMEOUT => 10,
       CURLOPT_RETURNTRANSFER => true,
       CURLOPT_HEADER => true,
       CURLOPT_TIMEOUT => 60,
       CURLOPT_USERAGENT => 'bookboon-php-2.0',
       CURLOPT_SSL_VERIFYPEER => true,
       CURLOPT_SSL_VERIFYHOST => 2
   );

   function __construct($appid, $appkey, $headers = array()) {
      if (empty($appid) || empty($appkey)) {
          throw new Exception('Invalid appid or appkey');
      }
      
      $this->authenticated['appid'] = $appid;
      $this->authenticated['appkey'] = $appkey;
      
      foreach ($headers as $h => $v) {
         $this->headers[] = $h . ': ' . $v;
      }
      $this->headers[] = 'X-Forwarded-For: ' . $this->getRemoteAddress();
      
      if (!empty($this->cache_class_name)) {
         require_once (strtolower($this->cache_class_name).".php");
         $this->cache = new $this->cache_class_name();
      }
   }

   /**
    * Prepares the call to the api and if enabled tries cache provider first for GET calls
    * 
    * @param string $relative_url The url relative to the address. Must begin with '/'
    * @param array $method_vars must contain subarray called either 'post' or 'get' depend on HTTP method
    * @param boolean $cache_query manually disable object cache for query
    * @return array results of call
    */
   public function api($relative_url, $method_vars = array(), $cache_query = true) {
      
      $result = array();
      $queryUrl = $this->url . $relative_url;
      
      if (!substr($relative_url, 1, 1) == '/') {
         throw new Exception('Location must begin with forward slash');
      }

      if (isset($method_vars['get'])) {
         $queryUrl = $this->url . $relative_url . "?" . http_build_query($method_vars['get']);
       
         /* Use cache if provider succesfully initialized and only GET calls */
         if (is_object($this->cache) && count($method_vars) == 1 && $cache_query) {
            $result = $this->cache->get($queryUrl);
            if (!$result) {
               $result = $this->query($queryUrl, $method_vars);
               $this->cache->save($queryUrl, $result);
            }
            return $result;
         }
      }
      
      return $result = $this->query($queryUrl, $method_vars);
   }
   
   /**
    * Makes the actual query call to the remote api.
    * 
    * @param string $relative_url The url relative to the address. Must begin with '/'
    * @param array $vars must contain subarray called either 'post' or 'get' depend on HTTP method
    * @return array results of call
    */
   private function query($url, $vars = array()) {
      
      $http = curl_init();

      curl_setopt($http, CURLOPT_URL, "https://" . $url);
      curl_setopt($http, CURLOPT_USERPWD, $this->authenticated['appid'] . ":" . $this->authenticated['appkey']);
      curl_setopt($http, CURLOPT_HTTPHEADER, $this->headers);
      
      if (isset($vars['post'])) {
         curl_setopt($http, CURLOPT_POST, count($vars['post']));
         curl_setopt($http, CURLOPT_POSTFIELDS, http_build_query($vars['post']));
      }
      
      foreach (self::$CURL_OPTS as $key => $val) {
         curl_setopt($http, $key, $val);
      }
      $response = curl_exec($http);
      
      $header_size = curl_getinfo($http, CURLINFO_HEADER_SIZE);
      $header = substr($response, 0, $header_size);
      $body = json_decode(substr($response, $header_size), true);
      
      $http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);
      
      curl_close($http);

      if ($http_status >= 400) {
         throw new Exception($body['error'] . ': ' . $body['messsage'] . "//: " . $url); 
      }
      
      if ($http_status >= 300 && $http <= 303) {
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
   public static function isValidGUID($guid) {
      if (!preg_match("^(\{{0,1}([0-9a-fA-F]){8}-([0-9a-fA-F]){4}-([0-9a-fA-F]){4}-([0-9a-fA-F]){4}-([0-9a-fA-F]){12}\}{0,1})$^", $guid))
         return false;
      else
         return true;
   }

   /**
    * Returns the remote address either directly or if set XFF header value
    * 
    * @return string The ip address
    */
   private function getRemoteAddress() {
      $hostname = $_SERVER['REMOTE_ADDR'];

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

}

?>
