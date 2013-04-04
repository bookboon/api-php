<?php
/*
 *  Copyright 2011 Bookboon.com Ltd.
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
   private $url = "api.bookboon.com";
   private $cache_class_name= "Bookboon_Memcached";
   private $cache = null;
   
   public static $CURL_OPTS = array(
       CURLOPT_CONNECTTIMEOUT => 10,
       CURLOPT_RETURNTRANSFER => true,
       CURLOPT_TIMEOUT => 60,
       CURLOPT_USERAGENT => 'bookboon-php-0.4',
       CURLOPT_SSL_VERIFYPEER => true,
       CURLOPT_SSL_VERIFYHOST => 2
   );

   function __construct($APIKEY = "", $UniqueUserIdentifier = "") {
      if ((!empty($APIKEY)) AND (!empty($UniqueUserIdentifier))) {
         if ($this->validateUserID($UniqueUserIdentifier)) {
            $this->authenticated['apikey'] = $APIKEY;
            $this->authenticated['handle'] = $UniqueUserIdentifier;
         } else
            throw new Exception('Handle is invalid (see documentation)');
      }
      if (!empty($this->cache_class_name)) {
         require_once (strtolower($this->cache_class_name).".php");
         $this->cache = new $this->cache_class_name();
      }
   }

   /**
    * Validate the UserID or Handle
    * Check if the input is RFC 2617 compatible
    * 
    * @param string $UniqueUserIdentifier The unique user id
    * @return boolean
    */
   private function validateUserID($UniqueUserIdentifier) {
      /* handle cannot be longer than 64 chars and cannot contain colon. 
       * See Documentation at http://api.bookboon.com/docs 
       */
      if (strstr($UniqueUserIdentifier, ':') OR strlen($UniqueUserIdentifier) > 64)
         return false;
      else
         return true;
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

      curl_setopt($http, CURLOPT_URL, "http://" . $url);

      if (isset($vars['post'])) {
         curl_setopt($http, CURLOPT_POST, count($vars['post']));
         curl_setopt($http, CURLOPT_POSTFIELDS, http_build_query($vars['post']));
      }
      
      foreach (self::$CURL_OPTS as $key => $val)
         curl_setopt($http, $key, $val);

      curl_setopt($http, CURLOPT_HTTPHEADER, array('X-Forwarded-For: ' . $this->getRemoteAddress()));

      $response = curl_exec($http);
      $http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);
      
      /* Requires Authentication & SSL */
      if ($http_status == 401) {
         if (empty($this->authenticated))
            throw new Exception('Function call requires authenticated class');

         curl_setopt($http, CURLOPT_CAINFO, dirname(__FILE__) . '/bookboon_ca.crt');
         curl_setopt($http, CURLOPT_USERPWD, $this->authenticated['handle'] . ":" . $this->authenticated['apikey']);
         curl_setopt($http, CURLOPT_URL, "https://" . $url);

         $response = curl_exec($http);
         $http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);

         if ($http_status == 401)
            throw new Exception('Incorrect Authentication');
      }
      curl_close($http);

      if ($http_status == 200)
         return json_decode($response, true);
      else
         throw new Exception('Unhandled HTTP (' . $http_status . ') response code');
   }

   /**
    * Useful GUID validator to validate input in scripts
    * 
    * @param string $guid GUID to validate
    * @return boolean true if valid, false if not
    */
   public function isValidGUID($guid) {
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
